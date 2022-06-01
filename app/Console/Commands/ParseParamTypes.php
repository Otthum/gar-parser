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


    protected function parseItem(SimpleXMLElement $item)
    {
        $attributes = $item->attributes();
        return [
            'gar_id' => $attributes['ID'],
            'name' => $attributes['NAME'],
            'code' => $attributes['DESC'],
            'desc' => $attributes['CODE'],
            'update_date' => $attributes['UPDATEDATE'],
            'start_date' => $attributes['STARTDATE'],
            'end_date' => $attributes['ENDDATE'],
            'is_active' => filter_var($attributes['ISACTIVE'], FILTER_VALIDATE_BOOL),
        ];
    }
}
