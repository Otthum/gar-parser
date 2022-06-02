<?php

namespace App\Console\Commands;

use App\Models\OperationType;
use Illuminate\Console\Command;
use SimpleXMLElement;

class ParseOperationTypes extends AbstractGarParserCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gar:parse:operation-types  {path : Путь до архива с выгрузкой внутри папки "storage/app/gar"}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Парсит типы операций';

    /**
     * Шаблон имени файла с данными
     */
    protected $fileNamePattern = 'AS_OPERATION_TYPES_.+?\.xml';

    protected $isSpecificForRegion = false;

    protected $parsingClass = OperationType::class;


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
