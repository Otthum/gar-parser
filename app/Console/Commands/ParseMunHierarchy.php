<?php

namespace App\Console\Commands;

use App\Models\MunHierarchy;

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
        $isActual = true;
        if (isset($item['NEXTID']) && $item['NEXTID'] != 0) {
            $isActual = false;
        }

        return [
            'data' => [
                'gar_id' => $item['OBJECTID'],
                'parent_gar_id' => $item['PARENTOBJID'] ?? null,
                'oktmo' => $item['OKTMO'] ?? null,
                'path' => $item['PATH'],
                'is_active' => filter_var($item['ISACTIVE'], FILTER_VALIDATE_BOOL),
            ],

            'is_actual' => $isActual
        ];
    }
}
