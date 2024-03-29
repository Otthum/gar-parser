<?php

namespace App\Console\Commands;

use App\Models\HouseType;
use Illuminate\Console\Command;
use SimpleXMLElement;

class ParseHouseTypes extends AbstractGarParserCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gar:parse:house-types  {path : Путь до архива с выгрузкой внутри папки "storage/app/gar"}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Парсит типы домов';

    /**
     * Шаблон имени файла с данными
     */
    protected $fileNamePattern = 'AS_HOUSE_TYPES_.+?\.xml';

    protected $isSpecificForRegion = false;

    protected $parsingClass = HouseType::class;


    protected function parseItem($item)
    {
        return [
            'data' => [
                'gar_id' => $item['ID'],
                'name' => $item['NAME'],
                'short' => $item['SHORTNAME'] ?? null,
                'desc' => $item['DESC'] ?? null,
                'is_active' => filter_var($item['ISACTIVE'], FILTER_VALIDATE_BOOL),
            ],
        ];
    }
}
