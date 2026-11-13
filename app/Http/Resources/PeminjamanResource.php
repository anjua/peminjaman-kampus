<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PeminjamanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'ruang' => $this->ruang?->only('id', 'kode', 'nama'),
            'alat' => $this->detail->map(fn($d) => ['id' => $d->alat_id, 'nama' => $d->alat->nama, 'jumlah' => $d->jumlah]),
            'waktu_mulai' => $this->waktu_mulai->toIso8601String(),
            'waktu_selesai' => $this->waktu_selesai->toIso8601String(),
            'jumlah_peserta' => $this->jumlah_peserta,
            'keperluan' => $this->keperluan,
            'status' => ['kode' => $this->status, 'label' => $this->statusRef->label],
            'catatan_admin' => $this->catatan_admin,
            'denda' => $this->denda,
        ];
    }
}
