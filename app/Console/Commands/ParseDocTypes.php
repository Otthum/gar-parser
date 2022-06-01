<?php

namespace App\Console\Commands;

use App\Models\DocType;
use SimpleXMLElement;

class ParseDocTypes extends AbstractGarParserCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gar:parse:doc-types  {path : Путь до архива с выгрузкой внутри папки "storage/app/gar"}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Парсит типы документов';

    /**
     * Шаблон имени файла с данными
     */
    protected $fileNamePattern = 'AS_NORMATIVE_DOCS_TYPES_.+?\.xml';

    protected $isSpecificForRegion = false;

    protected $parsingClass = DocType::class;


    protected function parseItem(SimpleXMLElement $item)
    {
        $attributes = $item->attributes();
        return [
            'gar_id' => $attributes['ID'],
            'name' => $attributes['NAME'],
            'start_date' => $attributes['STARTDATE'],
            'end_date' => $attributes['ENDDATE'],
        ];
    }
}
