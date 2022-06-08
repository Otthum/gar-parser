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
    protected $signature = 'gar:address-str {date=today : Дата обновления адресных объектов, от которой пересчитать строку адреса}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Обновляет address_str колонку в иерархии';

    /**
     * Сколько записей берём за раз
     */
    protected $limit = 500;

    /**
     * Текущий оффсет
     */
    protected $offset = 0;

    protected $toCommit = [];


    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $date = new \DateTime($this->argument('date'));

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

        $count = 0;
        $start = microtime(true);
        foreach ($classes as $class => $relations) {

            $this->offset = 0;
            while (true) {
                $objects = $class::where('updated_at', '>=', $date->format('Y-m-d H:i:s'))
                    ->where('is_active', true)
                    ->with($relations)
                    ->limit($this->limit)
                    ->offset($this->offset)
                    ->get();

                if ($objects->count() == 0) {
                    break;
                }

                $this->offset += $this->limit;

                foreach ($objects as $object) {

                    try {
                        $parentIds = explode('.', $object->munHierarchy->path);
                    } catch (\Throwable $e) {
                        echo sprintf(
                            "\nОбъект класса %s (garId - %d) не имеет записи в иерархии",
                            $class,
                            $object->gar_id
                        );
                        continue;
                    }

                    $parentModels = AddrObj::whereIn('gar_id', $parentIds)
                        ->where('gar_id', '!=', $object->gar_id)
                        ->with('type')
                        ->get();

                    /**
                     * Создаём ассоциативный массив id => model
                     * для того, чтобы в дальнейшем гарантировать
                     * правильный порядок элементов
                     */
                    $parents = [];
                    foreach ($parentModels as $parent) {
                        $parents[$parent->gar_id] = $parent;
                    }

                    /**
                     * Так как $parentIds изначально идут в том
                     * порядке, который нам нужен, мы можем пройти
                     * по нему и брать соответствующую модель
                     * из заранее подготовленного массива
                     */
                    $str = '';
                    foreach ($parentIds as $id) {
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
                    $this->toCommit[] = $munHierarchy;
                    
                    $count++;
                }

                $this->commit($class);
                echo sprintf("\rОбновлено %d записей (последняя - %d, времени прошло %fс)", $count, $object->gar_id, microtime(true) - $start);
            }
        }
    }

    
    protected function commit(string $class)
    {
        MunHierarchy::upsert($this->toCommit, ['gar_id']);

        $ids = array_column($this->toCommit, 'gar_id');
        $class::whereIn('gar_id', $ids)->update(['updated_at' => new \DateTime()]);
        
        $this->toCommit = [];
    }
}
