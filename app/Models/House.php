<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class House extends Model
{
    use HasFactory;

    protected $guarded = [
        'id'
    ];


    public function getSelfAddress()
    {
        $mainPart = $this->type->name . ' ' . $this->num;

        if ($this->addTypeFirst) {
            $mainPart .= ' ' . $this->addTypeFirst->name . ' ' . $this->num_1;
        }

        if ($this->addTypeSecond) {
            $mainPart .= ' ' . $this->addTypeSecond->name . ' ' . $this->num_2;
        }

        return $mainPart;
    }


    public function munHierarchy()
    {
        return $this->hasOne(MunHierarchy::class, 'gar_id', 'gar_id');
    }

    public function type()
    {
        return $this->belongsTo(HouseType::class, 'type_id', 'gar_id');
    }

    public function addTypeFirst()
    {
        return $this->belongsTo(AddHouseType::class, 'type_id', 'add_type_1');
    }
    
    public function addTypeSecond()
    {
        return $this->belongsTo(AddHouseType::class, 'type_id', 'add_type_2');
    }
}
