<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Stuff extends Model
{
    //jika di migrationnya menggunakan $table->softdeletes
    use SoftDeletes;

    //fillable / guard
    //menentukan column wajib diisi (column yg bisa diisi dari luar)
    protected $fillable = ["name","category"];
    //protected $guarded = ['id']

    //property opsional :
    //kalau primary key bukan id : public $primarykey = 'no'
    //kalau misal gapake timestamps di migration : public $timestamps = FALSE

     // relasi
     //nama function : samain kaya model, kata pertama huruf kecil
     //model yang PK : hasOne/ hanMany
     //panggil namaModelFk::class
    public function stuffStock()
    {
        return $this->hasOne(StuffStock::class);
    }

    //relasi hasMany : nama func jamak
    public function inboundStuffs()
    {
        return $this ->hasMany(Inbounstuff::class);
    }

    public function lendings()
    {
        return $this->hasMany(Lending::class);
    }



}

