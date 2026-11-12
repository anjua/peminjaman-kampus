<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Peran extends Model
{
    protected $table = 'peran';

    protected $fillable = ['kode', 'nama', 'keterangan'];

    public function hakAkses()
    {
        return $this->belongsToMany(HakAkses::class, 'peran_hak_akses', 'peran_id', 'hak_akses_id');
    }

    public function pengguna()
    {
        return $this->hasMany(User::class, 'peran_id');
    }
}
