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


    protected function parseItem(SimpleXMLElement $item)
    {
        $attributes = $item->attributes();
        return [
            'gar_id' => $attributes['ID'],
            'object_id' => $attributes['OBJECTID'],
            'object_guid' => $attributes['OBJECTGUID'],
            'change_id' => $attributes['CHANGEID'],

            'name' => $attributes['NAME'],
            'short' => $attributes['TYPENAME'],

            'level' => $attributes['LEVEL'],

            'operation_id' => $attributes['OPERTYPEID'],

            'prev_id' => $attributes['PREVID'] ?? 0,
            'next_id' => $attributes['NEXTID'] ?? 0,

            'update_date' => $attributes['UPDATEDATE'],
            'start_date' => $attributes['STARTDATE'],
            'end_date' => $attributes['ENDDATE'],

            'is_actual' => filter_var($attributes['ISACTUAL'], FILTER_VALIDATE_BOOL),
            'is_active' => filter_var($attributes['ISACTIVE'], FILTER_VALIDATE_BOOL),
        ];
    }
}
