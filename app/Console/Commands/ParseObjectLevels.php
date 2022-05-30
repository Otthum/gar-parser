<?php

namespace App\Console\Commands;

use App\Models\ObjectLevel;
use SimpleXMLElement;

class ParseObjectLevels extends AbstractGarParserCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gar:parse:object-levels {date : Дата выгрузки}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Парсит уровни адресных объектов';

    /**
     * Шаблон имени файла с данными
     */
    protected $fileNamePattern = '~AS_OBJECT_LEVELS_.+?\.xml~i';

    protected $isSpecificForRegion = false;

    protected $parsingClass = ObjectLevel::class;


    protected function parseItem(SimpleXMLElement $item)
    {
        $attributes = $item->attributes();
        return [
            'gar_id' => $attributes['LEVEL'],
            'name' => $attributes['NAME'],
            'short' => $attributes['SHORTNAME'],
            'update_date' => $attributes['UPDATEDATE'],
            'start_date' => $attributes['STARTDATE'],
            'end_date' => $attributes['ENDDATE'],
            'is_active' => filter_var($attributes['ISACTIVE'], FILTER_VALIDATE_BOOL),
        ];
    }
}
