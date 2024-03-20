<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MunHierarchy extends Model
{
    use HasFactory;

    protected $guarded = [
        'id'
    ];


    public function addrObj()
    {
        return $this->belongsTo(AddrObj::class, 'gar_id', 'gar_id');
    }

    public function house()
    {
        return $this->belongsTo(House::class, 'gar_id', 'gar_id');
    }

    public function apartments()
    {
        return $this->belongsTo(Apartment::class, 'gar_id', 'gar_id');
    }
}
