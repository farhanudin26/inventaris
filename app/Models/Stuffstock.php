<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Stuffstock extends Model
{
    use SoftDeletes;
    protected $fillable = ["stuff_id", "total_available", "total_defec"];

    //model FK : belongsTo
    //panggil namaModelPK::classP
    public function stuff()
    {
        return $this->belongsTo(Stuff::class);
    }

}
