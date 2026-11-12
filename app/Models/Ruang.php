<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ruang extends Model
{
    use HasFactory;

    protected $table = 'ruang';

    protected $fillable = ['kode', 'nama', 'lokasi', 'kapasitas', 'fasilitas', 'foto', 'aktif'];

    protected $casts = ['aktif' => 'boolean', 'kapasitas' => 'integer'];

    protected $appends = ['foto_url'];

    public function peminjaman()
    {
        return $this->hasMany(Peminjaman::class, 'ruang_id');
    }

    public function getFotoUrlAttribute(): ?string
    {
        return $this->foto ? asset('storage/' . $this->foto) : null;
    }
}
