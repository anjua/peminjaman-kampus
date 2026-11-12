<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JamOperasional extends Model
{
    protected $table = 'jam_operasional';

    public $timestamps = false;

    protected $fillable = ['hari', 'nama_hari', 'buka', 'tutup', 'aktif'];

    protected $casts = ['aktif' => 'boolean', 'hari' => 'integer'];
}
