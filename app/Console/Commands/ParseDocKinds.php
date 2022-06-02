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
    protected $signature = 'gar:parse:doc-kinds  {path : Путь до архива с выгрузкой внутри папки "storage/app/gar"}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Парсит виды документов';

    /**
     * Шаблон имени файла с данными
     */
    protected $fileNamePattern = 'AS_NORMATIVE_DOCS_KINDS_.+?\.xml';

    protected $isSpecificForRegion = false;

    protected $parsingClass = DocKind::class;


    protected function parseItem($item)
    {
        return [
            'gar_id' => $item['ID'],
            'name' => $item['NAME'],
        ];
    }
}
