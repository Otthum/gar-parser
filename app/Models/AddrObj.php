<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AddrObj extends Model
{
    use HasFactory;

    protected $guarded = [
        'id'
    ];


    public function getSelfAddress()
    {
        return ($this->type->short ?? $this->type->name) . ' ' . $this->num;
    }


    public function munHierarchy()
    {
        return $this->hasOne(MunHierarchy::class, 'gar_id', 'gar_id');
    }

    public function type()
    {
        return $this->belongsTo(AddrObjType::class, 'type_id', 'gar_id');
    }
}
