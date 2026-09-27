<?php

namespace App\Http\Requests;

use App\Models\Patient;
use Illuminate\Validation\Rule;

class UpdatePatientRequest extends StorePatientRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * Aturannya sama dengan saat membuat pasien, kecuali keunikan NIK yang
     * sudah dipakai pasien ini sendiri harus tetap dianggap valid.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'nik' => [
                'nullable',
                'string',
                'size:16',
                'regex:/^[0-9]{16}$/',
                Rule::unique(Patient::class)->ignore($this->route('patient')),
            ],
        ]);
    }
}
