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


    protected function parseItem(SimpleXMLElement $item)
    {
        $attributes = $item->attributes();
        return [
            'gar_id' => $attributes['ID'],
            'change_id' => $attributes['CHANGEID'],
            
            'object_id' => $attributes['OBJECTID'],
            'parent_object_id' => $attributes['PARENTOBJID'],

            'oktmo' => $attributes['OKTMO'],

            'prev_id' => $attributes['PREVID'] ?? 0,
            'next_id' => $attributes['NEXTID'] ?? 0,
            
            'path' => $attributes['PATH'],
            'is_active' => filter_var($attributes['ISACTIVE'], FILTER_VALIDATE_BOOL),
        ];
    }
}
