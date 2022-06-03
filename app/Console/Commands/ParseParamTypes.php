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
            'data' => [
                'gar_id' => $item['ID'],
                'name' => $item['NAME'],
                'code' => $item['DESC'] ?? null,
                'desc' => $item['CODE'],
            ],
            'is_active' => filter_var($item['ISACTIVE'], FILTER_VALIDATE_BOOL),
        ];
    }
}
