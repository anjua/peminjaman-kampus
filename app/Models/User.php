<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'pengguna';

    protected $fillable = ['name', 'email', 'password', 'peran_id', 'nim_nip', 'telepon', 'aktif'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'aktif' => 'boolean',
        ];
    }

    public function peran()
    {
        return $this->belongsTo(Peran::class, 'peran_id');
    }

    public function peminjaman()
    {
        return $this->hasMany(Peminjaman::class, 'pengguna_id');
    }

    public function punyaHakAkses(string $kode): bool
    {
        return $this->loadMissing('peran.hakAkses')->peran?->hakAkses->contains('kode', $kode) ?? false;
    }

    public function getInisialAttribute(): string
    {
        return collect(explode(' ', $this->name))->take(2)
            ->map(fn($kata) => mb_strtoupper(mb_substr($kata, 0, 1)))->join('');
    }
}
