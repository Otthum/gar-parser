<?php

namespace App\Console\Commands;

use App\Models\DocKind;
use Illuminate\Console\Command;
use SimpleXMLElement;

class ParseDocKinds extends AbstractGarParserCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gar:parse:doc-kinds {date : Дата выгрузки}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Парсит виды документов';

    /**
     * Шаблон имени файла с данными
     */
    protected $fileNamePattern = '~AS_NORMATIVE_DOCS_KINDS_.+?\.xml~i';

    protected $isSpecificForRegion = false;

    protected $parsingClass = DocKind::class;


    protected function parseItem(SimpleXMLElement $item)
    {
        $attributes = $item->attributes();
        return [
            'gar_id' => $attributes['ID'],
            'name' => $attributes['NAME'],
        ];
    }
}
