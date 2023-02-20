<?php

namespace App\Console\Commands;

use App\Models\Param;

class ParseApartmentsParams extends AbstractGarParserCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gar:parse:apartments-params
        {path : Путь до архива с выгрузкой внутри папки "storage/app/gar"}
    ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Парсит данные по параметрам квартир и прочим помещениям';

    /**
     * Шаблон имени файла с данными
     */
    protected $fileNamePattern = 'AS_APARTMENTS_PARAMS_\d+?.+?\.xml';

    protected $uniqueFields = ['gar_id', 'type_id'];

    protected $parsingClass = Param::class;
    
    protected $isSpecificForRegion = false;
    

    protected function parseItem($item)
    {
        $isActual = false;

        if (isset($item['CHANGEIDEND']) && $item['CHANGEIDEND'] == 0) {
            $isActual = true;
        }
        
        return [
            'data' => [
                'gar_id' => $item['OBJECTID'],
                'type_id' => $item['TYPEID'],
                'value' => $item['VALUE'],
            ],
            
            'is_actual' => $isActual
        ];
    }
}
