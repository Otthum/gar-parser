<?php

namespace App\Console\Commands;

use App\Models\AddrObj;
use SimpleXMLElement;

class ParseAddrObjects extends AbstractGarParserCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gar:parse:add-objects  {path : Путь до архива с выгрузкой внутри папки "storage/app/gar"} {region=all : Регион для парсинга. Базово парсит все}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Парсит данные адресных объектов';

    /**
     * Шаблон имени файла с данными
     */
    protected $fileNamePattern = 'AS_ADDR_OBJ_\d+?.+?\.xml';

    protected $parsingClass = AddrObj::class;


    protected function parseItem($item)
    {
        return [
            'data' => [
                'gar_id' => $item['OBJECTID'],
                'gar_guid' => $item['OBJECTGUID'],

                'name' => $item['NAME'],
                'short' => $item['TYPENAME'],

                'level' => $item['LEVEL'],
            ],
            'is_actual' => filter_var($item['ISACTUAL'], FILTER_VALIDATE_BOOL),
            'is_active' => filter_var($item['ISACTIVE'], FILTER_VALIDATE_BOOL),
        ];
    }
}
