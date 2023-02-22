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
                    $currentParentIds = $this->explodeObjectPath($object);

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
                                        "У объекта %d (%s) отсутсвует часть адреса (gar id - %d).",
                                        $object->gar_id,
                                        $class,
                                        $id
                                    )
                                );
                            }
                            $str .= $parents[$id]->getSelfAddressFull() . ', ';
                        }
                    } catch (Exception $e) {
                        echo $e->getMessage() . " Пропускаем этот объект\n";
                        continue;
                    }
                    
                    $str .= $object->getSelfAddressFull();

                    $munHierarchy = $object->munHierarchy->toArray();
                    unset(
                        $munHierarchy['created_at'],
                        $munHierarchy['updated_at'],
                    );

                    $munHierarchy['address_str'] = $str;
                    $this->total++;
                    $this->toCommit['hierarchies'][] = $munHierarchy;
                }

                MunHierarchy::upsert($this->toCommit['hierarchies'], ['gar_id']);

                $class::whereIn('id', $this->toCommit['objects'])->update(['updated_at' => (new \DateTime())->format('Y-m-d H:i:s')]);

                echo sprintf(
                    "\rОбновлено %d/пропущено %d адресов за %f секунд (итерация завершена за %f, тайминг объектов - %f, тайминг родителей - %f)",
                    $this->total,
                    $this->skipped,
                    microtime(true) - $start,
                    microtime(true) - $singleIterationTime,
                    $objectsFetched - $singleIterationTime,
                    $parentsFetched - $objectsFetched,
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
    protected function explodeObjectPath($object)
    {
        try {
            $ids = explode('.', $object->munHierarchy->path);
        } catch (\Throwable $e) {
            echo sprintf(
                "Объект класса %s (garId - %d) не имеет записи в иерархии\n",
                get_class($object),
                $object->gar_id
            );
            return false;
        }

        return $ids;
    }

    /**
     * Возвращает массив уникальных родителей со всех переданных объектов
     */
    protected function collectParents(Collection $objects, string $targetClass)
    {
        /**
         * Пройдем по всем объектам и собёрм ID всех возможных родителей
         */
        $parentByIds = [];
        foreach ($objects as $object) {
            if ($currentParentIds = $this->explodeObjectPath($object)) {
                foreach ($currentParentIds as $id) {
                    if ($object->gar_id != $id) {
                        $parentByIds[$id] = null;
                    }
                }
            }
        }


        $parentModels = [
            AddrObj::class,
        ];

        if ($targetClass == Apartment::class) {
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


/**
 * type - this will be a foreign key to param_types table
 * value - this is a plane value, like house number
 * dictionary_type - 
 * dictionary_value 
 */