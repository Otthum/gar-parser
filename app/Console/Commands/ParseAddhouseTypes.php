<?php

namespace App\Console\Commands;

use App\Models\AddHouseType;
use Illuminate\Console\Command;
use SimpleXMLElement;

class ParseAddhouseTypes extends AbstractGarParserCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gar:parse:add-house-types {path : Путь до архива с выгрузкой внутри папки "storage/app/gar"}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Парсит типы дополнительного имени дома';

    /**
     * Шаблон имени файла с данными
     */
    protected $fileNamePattern = 'AS_ADDHOUSE_TYPES_.+?\.xml';

    protected $isSpecificForRegion = false;

    protected $parsingClass = AddHouseType::class;

    
    protected function parseItem($item)
    {
        return [
            'data' => [
                'gar_id' => $item['ID'],
                'name' => $item['NAME'],
                'short' => $item['SHORTNAME'] ?? null,
                'desc' => $item['DESC'] ?? null,
            ],
            'is_active' => filter_var($item['ISACTIVE'], FILTER_VALIDATE_BOOL),
        ];
    }
}
