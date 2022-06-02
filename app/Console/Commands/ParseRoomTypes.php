<?php

namespace App\Console\Commands;

use App\Models\RoomType;
use Illuminate\Console\Command;
use SimpleXMLElement;

class ParseRoomTypes extends AbstractGarParserCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gar:parse:room-types {path : Путь до архива с выгрузкой внутри папки "storage/app/gar"}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Парсит типы комнат';

    /**
     * Шаблон имени файла с данными
     */
    protected $fileNamePattern = 'AS_ROOM_TYPES_.+?\.xml';

    protected $isSpecificForRegion = false;

    protected $parsingClass = RoomType::class;


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
