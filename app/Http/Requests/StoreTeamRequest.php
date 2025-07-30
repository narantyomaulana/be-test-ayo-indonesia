<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTeamRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:teams,name',
            'logo' => 'nullable|string|url',
            'founded_year' => 'required|integer|min:1800|max:' . date('Y'),
            'headquarters_address' => 'required|string|max:500',
            'headquarters_city' => 'required|string|max:255'
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama tim wajib diisi',
            'name.unique' => 'Nama tim sudah ada',
            'founded_year.required' => 'Tahun berdiri wajib diisi',
            'founded_year.min' => 'Tahun berdiri tidak valid',
            'headquarters_address.required' => 'Alamat markas wajib diisi',
            'headquarters_city.required' => 'Kota markas wajib diisi'
        ];
    }
}
