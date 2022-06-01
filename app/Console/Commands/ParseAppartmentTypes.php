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


    protected function parseItem(SimpleXMLElement $item)
    {
        $attributes = $item->attributes();
        return [
            'gar_id' => $attributes['ID'],
            'name' => $attributes['NAME'],
            'short' => $attributes['SHORTNAME'],
            'desc' => $attributes['DESC'],
            'update_date' => $attributes['UPDATEDATE'],
            'start_date' => $attributes['STARTDATE'],
            'end_date' => $attributes['ENDDATE'],
            'is_active' => filter_var($attributes['ISACTIVE'], FILTER_VALIDATE_BOOL),
        ];
    }
}
