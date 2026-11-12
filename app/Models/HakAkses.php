<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HakAkses extends Model
{
    protected $table = 'hak_akses';

    protected $fillable = ['kode', 'nama', 'grup'];

    public function peran()
    {
        return $this->belongsToMany(Peran::class, 'peran_hak_akses', 'hak_akses_id', 'peran_id');
    }
}
