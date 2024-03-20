<?php

namespace App\Console\Commands;

use App\Models\AddrObj;
use App\Models\Apartment;
use App\Models\House;
use App\Models\MunHierarchy;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class UpdateAddressStr extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gar:address-str {date=today : Дата обновления адресных объектов, от которой пересчитать строку адреса} {--all}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Обновляет address_str колонку в иерархии';

    /**
     * Сколько записей брать за раз
     */
    protected $limit = 5000;

    /**
     * Обработанные данные, которые будут занесены в базу
     */
    protected array $toCommit = [
        'objects' => [],
        'hierarchies' => []
    ];

    protected int $total = 0;
    protected int $skipped = 0;


    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $start = microtime(true);
        DB::select(DB::raw("SET in_predicate_conversion_threshold=0"));
        DB::select(DB::raw("RESET QUERY CACHE"));

        DB::connection()->unsetEventDispatcher();
        $dateFrom = new \DateTime($this->argument('date'));
        $dateTo = new \DateTime();

        $classes = [
            AddrObj::class => [
                'munHierarchy',
                'type',
            ],
            House::class => [
                'munHierarchy',
                'type',
                'addTypeFirst',
                'addTypeSecond',
            ],
            Apartment::class => [
                'munHierarchy',
                'type',
            ],
        ];

        foreach ($classes as $class => $relations) {

            $q = $class::where('is_active', true)
                ->where('updated_at', '<=', $dateTo->format('Y-m-d H:i:s'))
                ->limit($this->limit)
                ->with($relations)
                ->orderBy('updated_at', 'asc');

            if (!$this->option('all')) {
                $q->where('updated_at', '>=', $dateFrom->format('Y-m-d H:i:s'));
            }

            while (true) {
                $singleIterationTime = microtime(true);

                $objects = $q->get();
                $objectsFetched = microtime(true);

                if ($objects->count() == 0) {
                    break;
                }

                $parents = $this->collectParents($objects, $class);

                $parentsFetched = microtime(true);

                foreach ($objects as $object) {
                    $this->toCommit['objects'][] = $object->id;

                    foreach ($object->munHierarchy as $munHierarchy) {
                        $currentParentIds = $this->explodeObjectPath($munHierarchy);
        
                        if ( !$currentParentIds ) {
                            $this->skipped++;
                            continue;
                        }
        
                        $str = '';
        
                        try {
                            foreach ($currentParentIds as $id) {
                                if ($id == $object->gar_id) {
                                    continue;
                                }
            
                                if ($parents[$id] == null) {
                                    throw new Exception(
                                        sprintf(
                                            "У иерархии %d объекта %s (%s) отсутствует часть адреса (gar id - %d).",
                                            $munHierarchy->id,
                                            $object->gar_id,
                                            $class,
                                            $id
                                        )
                                    );
                                }
                                $str .= $parents[$id]->getSelfAddressFull() . ', ';
                            }
                        } catch (Exception $e) {
                            $this->skipped++;
                            echo $e->getMessage() . " Пропускаем эту иерархию\n";
                            continue;
                        }
        
                        $str .= $object->getSelfAddressFull();
        
                        $hierarchyData = $munHierarchy->toArray();
                        unset(
                            $hierarchyData['created_at'],
                            $hierarchyData['updated_at'],
                        );
        
                        $hierarchyData['address_str'] = $str;
                        $this->total++;
                        $this->toCommit['hierarchies'][] = $hierarchyData;
                    }
                }

                $operationsFinished = microtime(true);

                MunHierarchy::upsert($this->toCommit['hierarchies'], ['id']);

                $hierarchiesUpdated = microtime(true);

                $class::whereIn('id', $this->toCommit['objects'])->update(['updated_at' => (new \DateTime())->format('Y-m-d H:i:s')]);

                $objectsUpdated = microtime(true);

                echo sprintf(
                    "\rОбновлено %d/пропущено %d адресов за %f секунд (итерация завершена за %f, объекты (s) - %f, родители (s) - %f, логика - %f, иерархии (u) - %f, объекты (u) - %f",
                    $this->total,
                    $this->skipped,
                    microtime(true) - $start,
                    microtime(true) - $singleIterationTime,
                    $objectsFetched - $singleIterationTime,
                    $parentsFetched - $objectsFetched,
                    $operationsFinished - $parentsFetched,
                    $hierarchiesUpdated - $operationsFinished,
                    $objectsUpdated - $hierarchiesUpdated,
                );
                $this->toCommit = [
                    'objects' => [],
                    'hierarchies' => []
                ];
            }

            echo sprintf("\nКласс %s - done.\n", $class);
        }
    }

    /**
     * Разбивает иерархиую объекта на массив из соответсвующих ID
     */
    protected function explodeObjectPath(?MunHierarchy $munHierarchy)
    {
        try {
            $ids = explode('.', $munHierarchy->path);
        } catch (\Throwable $e) {
            /* echo sprintf(
                "Объект класса %s (garId - %d) не имеет записи в иерархии\n",
                get_class($object),
                $object->gar_id
            ); */
            return false;
        }

        return $ids;
    }

    /**
     * Возвращает массив уникальных родителей со всех переданных объектов
     */
    protected function collectParents(Collection $objects, string $class)
    {
        /**
         * Пройдем по всем объектам и собёрм ID всех возможных родителей
         */
        $parentByIds = [];
        foreach ($objects as $object) {

            /**
             * Так как объекты могут иметь несколько записей в иерархии - обработаем каждую отдельно
             */
            foreach ($object->munHierarchy as $munHierarchy) {
                if ($currentParentIds = $this->explodeObjectPath($munHierarchy)) {
                    foreach ($currentParentIds as $id) {
                        if ($object->gar_id != $id) {
                            $parentByIds[$id] = null;
                        }
                    }
                }
            }
            
        }


        $parentModels = [
            AddrObj::class,
        ];

        if ($class == Apartment::class) {
            $parentModels[] = House::class;
        }

        /**
         * Получаем всех родителей и записываем их к соответствующему ID в массиве
         */
        foreach ($parentModels as $class) {
            $models = $class::whereIn('gar_id', array_keys($parentByIds))->get();
            foreach ($models as $model) {
                $parentByIds[$model->gar_id] = $model;
            }
        }
        

        return $parentByIds;
    }
}
