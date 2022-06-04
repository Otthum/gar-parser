<?php

namespace App\Console\Commands;

use App\Models\House;
use SimpleXMLElement;

class ParseHouses extends AbstractGarParserCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gar:parse:houses  {path : Путь до архива с выгрузкой внутри папки "storage/app/gar"} {region=all : Регион для парсинга. Базово парсит все}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Парсит данные домов';

    /**
     * Шаблон имени файла с данными
     */
    protected $fileNamePattern = 'AS_HOUSES_\d+?.+?\.xml';

    protected $parsingClass = House::class;


    protected function parseItem($item)
    {
        return [
            'data' => [
                'gar_id' => $item['OBJECTID'],
                'gar_guid' => $item['OBJECTGUID'],

                'num' => $item['HOUSENUM'] ?? null,
                'num_1' => $item['ADDNUM1'] ?? null,
                'num_2' => $item['ADDNUM2'] ?? null,

                'type_id' => $item['HOUSETYPE'] ?? null,
                'add_type_id_1' => $item['ADDTYPE1'] ?? null,
                'add_type_id_2' => $item['ADDTYPE2'] ?? null,
            ],

            'is_actual' => filter_var($item['ISACTUAL'], FILTER_VALIDATE_BOOL),
            'is_active' => filter_var($item['ISACTIVE'], FILTER_VALIDATE_BOOL),
        ];
    }
}
