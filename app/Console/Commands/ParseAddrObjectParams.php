<?php

namespace App\Console\Commands;

use App\Models\Param;

class ParseAddrObjectParams extends AbstractGarParserCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gar:parse:addr-obj-params  {path : Путь до архива с выгрузкой внутри папки "storage/app/gar"} {region=all : Регион для парсинга. Базово парсит все}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Парсит параметры адресных объектов за указанную дату';

    /**
     * Шаблон имени файла с данными
     */
    protected $fileNamePattern = 'AS_ADDR_OBJ_PARAMS_.+?\.xml';

    protected $uniqueFields = ['gar_id', 'type_id'];

    protected $parsingClass = Param::class;


    protected function parseItem($item)
    {
        $isActual = false;

        if (isset($item['CHANGEIDEND']) && $item['CHANGEIDEND'] == 0) {
            $isActual = true;
        }
        
        return [
            'data' => [
                'gar_id' => $item['OBJECTID'],
                'type_id' => $item['TYPEID'],
                'value' => $item['VALUE'],
            ],
            
            'is_actual' => $isActual
        ];
    }
}
