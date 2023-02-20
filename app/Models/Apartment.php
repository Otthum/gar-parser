<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Apartment extends Model
{
    use HasFactory;


    protected $guarded = [
        'id'
    ];


    public function type()
    {
        return $this->hasOne(AppartmentType::class, 'type_id', 'gar_id');
    }
}
