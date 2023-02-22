<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AddrObjType extends Model
{
    use HasFactory;


    /**
     * Массив полных наименований в формате ['short' => ['level' => 'full name']] 
     */
    protected static $shortsMap = [

    ];

    protected $guarded = [
        'id'
    ];


    public static function shortToFull(string $short, int $level)
    {
        if (!isset(self::$shortsMap[$short][$level])) {
            $type = self::where('short', $short)->where('level', $level)->first();

            self::$shortsMap[$short][$level] = $type->name;
        }

        return self::$shortsMap[$short][$level];
    }
}
