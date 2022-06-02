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
    protected $signature = 'gar:parse:object-levels  {path : Путь до архива с выгрузкой внутри папки "storage/app/gar"}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Парсит уровни адресных объектов';

    /**
     * Шаблон имени файла с данными
     */
    protected $fileNamePattern = 'AS_OBJECT_LEVELS_.+?\.xml';

    protected $isSpecificForRegion = false;

    protected $parsingClass = ObjectLevel::class;


    protected function parseItem($item)
    {
        return [
            'gar_id' => $item['LEVEL'],
            'name' => $item['NAME'],
            'short' => $item['SHORTNAME'],
            'update_date' => $item['UPDATEDATE'],
            'start_date' => $item['STARTDATE'],
            'end_date' => $item['ENDDATE'],
            'is_active' => filter_var($item['ISACTIVE'], FILTER_VALIDATE_BOOL),
        ];
    }
}
