<?php

namespace App\Console\Commands;

use App\Models\AppartmentType;
use Illuminate\Console\Command;
use SimpleXMLElement;

class ParseAppartmentTypes extends AbstractGarParserCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gar:parse:appart-types  {path : Путь до архива с выгрузкой внутри папки "storage/app/gar"}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Парсит типы помещений';

    /**
     * Шаблон имени файла с данными
     */
    protected $fileNamePattern = 'AS_APARTMENT_TYPES_.+?\.xml';

    protected $isSpecificForRegion = false;

    protected $parsingClass = AppartmentType::class;


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
