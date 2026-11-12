<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RuangRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'kode' => ['required', 'max:20', Rule::unique('ruang', 'kode')->ignore($this->route('ruang'))],
            'nama' => ['required', 'max:100'],
            'lokasi' => ['required', 'max:150'],
            'kapasitas' => ['required', 'integer', 'min:1'],
            'fasilitas' => ['nullable', 'string', 'max:500'],
            'foto' => ['nullable', 'image', 'max:2048'],
            'aktif' => ['boolean'],
        ];
    }
}
