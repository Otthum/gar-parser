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
    protected $signature = 'gar:parse:house-params {date : Дата выгрузки} {region=all : Регион для парсинга. Базово парсит все}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Парсит параметры домов за указанную дату';

    /**
     * Шаблон имени файла с данными
     */
    protected $fileNamePattern = '~AS_HOUSES_PARAMS_.+?\.xml~i';


    protected function parseItem(SimpleXMLElement $item)
    {
        $attributes = $item->attributes();
        $param = Param::updateOrCreate(
            [
                'gar_id' => $attributes['ID'],
            ],
            [
                'gar_id' => $attributes['ID'],
                'object_id' => $attributes['OBJECTID'],
                'change_id' => $attributes['CHANGEID'],
                'change_end_id' => $attributes['CHANGEENDID'],
                'type_id' => $attributes['TYPEID'],
                'value' => $attributes['VALUE'],
                'start_date' => $attributes['STARTDATE'],
                'end_date' => $attributes['ENDDATE'],
            ]
        );
    }
}
