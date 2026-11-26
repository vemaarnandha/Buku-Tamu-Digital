<?php

namespace App\Http\Requests\Guest;

use Illuminate\Foundation\Http\FormRequest;

class StoreKunjunganRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'keperluan' => strip_tags(trim((string) $this->input('keperluan'))),
            'nama' => strip_tags(trim((string) $this->input('nama'))),
            'instansi' => strip_tags(trim((string) $this->input('instansi'))),
            'alamat' => strip_tags(trim((string) $this->input('alamat'))),
        ]);
    }

    public function rules(): array
    {
        return [
            'no_hp' => ['required', 'string', 'max:20'],
            'nama' => ['required', 'string', 'max:255'],
            'kategori' => ['required', 'in:perorangan,instansi'],
            'instansi' => ['nullable', 'string', 'max:255'],
            'alamat' => ['nullable', 'string', 'max:1000'],
            'keperluan' => ['required', 'string', 'max:2000'],
        ];
    }
}
