<?php

namespace App\Console\Commands;

use App\Models\ParamType;
use Illuminate\Console\Command;
use SimpleXMLElement;

class ParseParamTypes extends AbstractGarParserCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gar:parse:param-types {path : Путь до архива с выгрузкой внутри папки "storage/app/gar"}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Парсит типы параметров';

    /**
     * Шаблон имени файла с данными
     */
    protected $fileNamePattern = 'AS_PARAM_TYPES_.+?\.xml';

    protected $isSpecificForRegion = false;

    protected $parsingClass = ParamType::class;


    protected function parseItem($item)
    {
        return [
            'gar_id' => $item['ID'],
            'name' => $item['NAME'],
            'code' => $item['DESC'],
            'desc' => $item['CODE'],
            'update_date' => $item['UPDATEDATE'],
            'start_date' => $item['STARTDATE'],
            'end_date' => $item['ENDDATE'],
            'is_active' => filter_var($item['ISACTIVE'], FILTER_VALIDATE_BOOL),
        ];
    }
}
