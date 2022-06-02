<?php

namespace App\Console\Commands;

use App\Models\Param;
use SimpleXMLElement;

class ParseHouseParams extends AbstractGarParserCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gar:parse:house-params  {path : Путь до архива с выгрузкой внутри папки "storage/app/gar"} {region=all : Регион для парсинга. Базово парсит все}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Парсит параметры домов за указанную дату';

    /**
     * Шаблон имени файла с данными
     */
    protected $fileNamePattern = 'AS_HOUSES_PARAMS_.+?\.xml';

    protected $parsingClass = Param::class;


    protected function parseItem($item)
    {
        return [
            'gar_id' => $item['ID'],
            'object_id' => $item['OBJECTID'],
            'change_id' => $item['CHANGEID'],
            'change_end_id' => $item['CHANGEIDEND'],
            'type_id' => $item['TYPEID'],
            'value' => $item['VALUE'],
            'update_date' => $item['UPDATEDATE'],
            'start_date' => $item['STARTDATE'],
            'end_date' => $item['ENDDATE'],
        ];
    }
}
