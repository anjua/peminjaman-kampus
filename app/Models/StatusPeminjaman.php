<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StatusPeminjaman extends Model
{
    protected $table = 'status_peminjaman';

    protected $primaryKey = 'kode';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = ['kode', 'label', 'warna', 'urutan'];
}
