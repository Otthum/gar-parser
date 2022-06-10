<?php

namespace App\Console\Commands;

use App\Models\AddrObj;
use App\Models\House;
use App\Models\MunHierarchy;
use Illuminate\Console\Command;
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
    protected $limit = 500;

    /**
     * Обработанные данные, которые будут занесены в базу
     */
    protected array $toCommit = [
        'objects' => [],
        'hierarchies' => []
    ];

    protected int $total = 0;


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
            House::class => [
                'munHierarchy',
                'type',
                'addTypeFirst',
                'addTypeSecond',
            ],
            AddrObj::class => [
                'munHierarchy',
                'type',
            ],
        ];

        foreach ($classes as $class => $relations) {

            $q = $class::where('is_active', true)
                ->where('updated_at', '<=', $dateTo->format('Y-m-d H:i:s'))
                ->limit($this->limit)
                ->with($relations)
                ->orderBy('id', 'asc');

            if (!$this->option('all')) {
                $q->where('updated_at', '>=', $dateFrom->format('Y-m-d H:i:s'));
            }

            while (true) {
                $singleIterationTime = microtime(true);

                $objects = $q->get();

                if ($objects->count() == 0) {
                    break;
                }

                $parents = $this->collectParents($objects);

                foreach ($objects as $object) {
                    $this->toCommit['objects'][] = $object->id;
                    $currentParentIds = $this->explodeObjectPath($object);

                    if ( !$currentParentIds ) {
                        continue;
                    }

                    $str = '';
                    foreach ($currentParentIds as $id) {
                        if ($id == $object->gar_id) {
                            continue;
                        }
                        $str .= $parents[$id]->getSelfAddress() . ', ';
                    }
                    $str .= $object->getSelfAddress();

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
                    "\rОбновлено %d адресов за %f секунд (итерация завершена за %f, обновлено %d иерархий и %d объектов, уникальных родителей в итерации - %d)",
                    $this->total,
                    microtime(true) - $start,
                    microtime(true) - $singleIterationTime,
                    count($this->toCommit['hierarchies']),
                    count($this->toCommit['objects']),
                    count($parents)
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
    protected function collectParents($objects)
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

        /**
         * Получаем всех родителей и записываем их к соответствующему ID в массиве
         */
        $parentModels = AddrObj::whereIn('gar_id', array_keys($parentByIds))->get();
        foreach ($parentModels as $parentModel) {
            $parentByIds[$parentModel->gar_id] = $parentModel;
        }

        return $parentByIds;
    }
}
