<?php

namespace App\Console\Commands;

use App\Models\AddrObjType;
use Illuminate\Console\Command;
use SimpleXMLElement;

class ParseAddrObjTypes extends AbstractGarParserCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gar:parse:addr-obj-types  {path : Путь до архива с выгрузкой внутри папки "storage/app/gar"}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Парсит типы адресных объектов';

    /**
     * Шаблон имени файла с данными
     */
    protected $fileNamePattern = 'AS_ADDR_OBJ_TYPES_.+?\.xml';

    protected $isSpecificForRegion = false;

    protected $parsingClass = AddrObjType::class;


    protected function parseItem($item)
    {
        return [
            'gar_id' => $item['ID'],
            'level' => $item['LEVEL'],
            'name' => $item['NAME'],
            'short' => $item['SHORTNAME'],
            'desc' => $item['DESC'],
            'update_date' => $item['UPDATEDATE'],
            'start_date' => $item['STARTDATE'],
            'end_date' => $item['ENDDATE'],
            'is_active' => filter_var($item['ISACTIVE'], FILTER_VALIDATE_BOOL),
        ];
    }
}
