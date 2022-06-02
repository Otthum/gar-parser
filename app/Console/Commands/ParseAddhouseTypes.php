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
            'gar_id' => $item['ID'],
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
