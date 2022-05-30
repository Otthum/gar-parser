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
    protected $signature = 'gar:parse:room-types {date : Дата выгрузки}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Парсит типы комнат';

    /**
     * Шаблон имени файла с данными
     */
    protected $fileNamePattern = '~AS_ROOM_TYPES_.+?\.xml~i';

    protected $isSpecificForRegion = false;

    protected $parsingClass = RoomType::class;


    protected function parseItem(SimpleXMLElement $item)
    {
        $attributes = $item->attributes();
        return [
            'gar_id' => $attributes['ID'],
            'name' => $attributes['NAME'],
            'short' => $attributes['SHORTNAME'],
            'desc' => $attributes['DESC'],
            'update_date' => $attributes['UPDATEDATE'],
            'start_date' => $attributes['STARTDATE'],
            'end_date' => $attributes['ENDDATE'],
            'is_active' => filter_var($attributes['ISACTIVE'], FILTER_VALIDATE_BOOL),
        ];
    }
}
