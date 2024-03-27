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


    public function getSelfAddressShort()
    {
        return $this->type->short . ' ' . $this->number;
    }

    public function getSelfAddressFull()
    {
        return $this->type->name . ' ' . $this->number;
    }

    public function getLevel()
    {
        return 11;
    }


    public function munHierarchy()
    {
        return $this->hasMany(MunHierarchy::class, 'gar_id', 'gar_id');
    }

    public function type()
    {
        return $this->hasOne(ApartmentType::class, 'gar_id', 'type_id');
    }
}
