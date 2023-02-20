<?php

namespace App\Console\Commands;

use App\Models\Apartment;
use App\Models\AppartmentType;
use Illuminate\Support\Facades\Log;

class ParseApartments extends AbstractGarParserCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gar:parse:apartments 
        {path : Путь до архива с выгрузкой внутри папки "storage/app/gar"}
        {region=all : Регион для парсинга. Базово парсит все}
    ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Парсит данные по квартирам и прочим помещениям';

    /**
     * Шаблон имени файла с данными
     */
    protected $fileNamePattern = 'AS_APARTMENTS_\d+?.+?\.xml';

    protected $parsingClass = Apartment::class;

    protected $types = [];


    protected function parseItem($item)
    {
        $isActual = filter_var($item['ISACTUAL'], FILTER_VALIDATE_BOOL);
        if ( $isActual && !$this->validateType($item['APARTTYPE']) ) {
            $isActual = false;
            Log::channel('parsing')->info(sprintf(
                "Помещение %d (%s) имеет тип %s, который отсутсвует в базе. Запись в базу не добавлена.",
                $item['OBJECTID'],
                $item['OBJECTGUID'],
                $item['APARTTYPE']
            ));
        }

        return [
            'data' => [
                'gar_id' => $item['OBJECTID'],
                'gar_guid' => $item['OBJECTGUID'],

                'type_id' => $item['APARTTYPE'],
                'number' => $item['NUMBER'],

                'is_active' => filter_var($item['ISACTIVE'], FILTER_VALIDATE_BOOL),
            ],
            'is_actual' => $isActual,
        ];
    }

    protected function validateType($type)
    {
        if (count($this->types) == 0) {
            $types = AppartmentType::select('gar_id')->get()->toArray();
            foreach ($types as $t) {
                $this->types[] = $t['gar_id'];
            }
        }

        return in_array($type, $this->types);
    }
}
