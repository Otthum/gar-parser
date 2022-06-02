<?php

namespace App\Console\Commands;

use App\Models\MunHierarchy;
use SimpleXMLElement;

class ParseMunHierarchy extends AbstractGarParserCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gar:parse:mun-hierarchy  {path : Путь до архива с выгрузкой внутри папки "storage/app/gar"} {region=all : Регион для парсинга. Базово парсит все}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Парсит данные муниципальной иерархии';

    /**
     * Шаблон имени файла с данными
     */
    protected $fileNamePattern = 'AS_MUN_HIERARCHY_.+?\.xml';

    protected $parsingClass = MunHierarchy::class;


    protected function parseItem($item)
    {
        return [
            'gar_id' => $item['ID'],
            'change_id' => $item['CHANGEID'],
            
            'object_id' => $item['OBJECTID'],
            'parent_object_id' => $item['PARENTOBJID'],

            'oktmo' => $item['OKTMO'],

            'prev_id' => $item['PREVID'] ?? 0,
            'next_id' => $item['NEXTID'] ?? 0,
            
            'path' => $item['PATH'],
            'is_active' => filter_var($item['ISACTIVE'], FILTER_VALIDATE_BOOL),
        ];
    }
}
