<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePlayerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'team_id' => 'required|exists:teams,id',
            'name' => 'required|string|max:255',
            'height' => 'required|integer|min:100|max:250',
            'weight' => 'required|integer|min:40|max:150',
            'position' => 'required|in:penyerang,gelandang,bertahan,penjaga_gawang',
            'jersey_number' => [
                'required',
                'integer',
                'min:1',
                'max:99',
                Rule::unique('players')->where(function ($query) {
                    return $query->where('team_id', $this->team_id);
                })
            ]
        ];
    }

    public function messages(): array
    {
        return [
            'team_id.required' => 'Tim wajib dipilih',
            'team_id.exists' => 'Tim tidak ditemukan',
            'name.required' => 'Nama pemain wajib diisi',
            'height.required' => 'Tinggi badan wajib diisi',
            'height.min' => 'Tinggi badan minimal 100 cm',
            'weight.required' => 'Berat badan wajib diisi',
            'position.required' => 'Posisi pemain wajib dipilih',
            'jersey_number.required' => 'Nomor punggung wajib diisi',
            'jersey_number.unique' => 'Nomor punggung sudah digunakan dalam tim ini'
        ];
    }
}
