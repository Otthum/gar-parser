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
        $res = '';

        if ($this->type) {
            $res .= ' ' . ($this->type->short ?? $this->type->name) . ' ' . $this->num;
        }

        if ($this->addTypeFirst) {
            $res .= ' ' . ($this->addTypeFirst->short ?? $this->addTypeFirst->name) . ' ' . $this->num_1;
        }

        if ($this->addTypeSecond) {
            $res .= ' ' . ($this->addTypeSecond->short ?? $this->addTypeSecond->name) . ' ' . $this->num_2;
        }

        return $res == '' ? null : $res;
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
        return $this->belongsTo(AddHouseType::class, 'add_type_id_1', 'gar_id');
    }

    public function addTypeSecond()
    {
        return $this->belongsTo(AddHouseType::class, 'add_type_id_2', 'gar_id');
    }
}
