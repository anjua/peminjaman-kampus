<?php

namespace App\Http\Requests;

use App\Models\Pengaturan;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AlatRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'kode' => ['required', 'max:20', Rule::unique('alat', 'kode')->ignore($this->route('alat'))],
            'nama' => ['required', 'max:100'],
            'stok' => ['required', 'integer', 'min:0'],
            'kondisi' => ['required', Rule::in(array_keys(Pengaturan::ambil('pengembalian.pilihan_kondisi')))],
            'denda_per_jam' => ['nullable', 'integer', 'min:0'],
            'aktif' => ['boolean'],
        ];
    }
}
