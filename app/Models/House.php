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


    public function getSelfAddressShort()
    {
        return $this->buildAddress([
            'name' => $this->type ? $this->type->short : null,
            'addTypeFirst' => $this->addTypeFirst ? $this->addTypeFirst->short : null,
            'addTypeSecond' => $this->addTypeSecond ? $this->addTypeSecond->short : null,
        ]);
    }

    public function getSelfAddressFull()
    {
        return $this->buildAddress([
            'name' => $this->type ? $this->type->name : null,
            'addTypeFirst' => $this->addTypeFirst ? $this->addTypeFirst->name : null,
            'addTypeSecond' => $this->addTypeSecond ? $this->addTypeSecond->name : null,
        ]);
    }

    protected function buildAddress(array $parts)
    {
        $res = '';

        if ($parts['name']) {
            $res .= $parts['name'] . ' ' . $this->num;
        }

        if ($parts['addTypeFirst']) {
            $res .= ' ' . $parts['addTypeFirst'] . ' ' . $this->num_1;
        }

        if ($parts['addTypeSecond']) {
            $res .= ' ' . $parts['addTypeSecond'] . ' ' . $this->num_2;
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
