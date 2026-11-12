<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TemplateNotifikasi extends Model
{
    protected $table = 'template_notifikasi';

    protected $fillable = ['kode', 'nama', 'judul', 'isi', 'ikon', 'warna', 'kanal', 'penerima', 'aktif'];

    protected $casts = ['kanal' => 'array', 'aktif' => 'boolean'];
}
