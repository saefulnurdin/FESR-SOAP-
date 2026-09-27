<?php

namespace App\Http\Requests;

use App\Enums\BloodType;
use App\Enums\Gender;
use App\Models\Patient;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePatientRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nik' => ['nullable', 'string', 'size:16', 'regex:/^[0-9]{16}$/', Rule::unique(Patient::class)],
            'name' => ['required', 'string', 'max:255'],
            'gender' => ['required', Rule::enum(Gender::class)],
            'birth_date' => ['nullable', 'date', 'before_or_equal:today', 'after:1900-01-01'],
            'birth_place' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30', 'regex:/^[0-9+\-\s()]{6,30}$/'],
            'blood_type' => ['nullable', Rule::enum(BloodType::class)],
            'address' => ['nullable', 'string', 'max:1000'],
            'allergies' => ['nullable', 'string', 'max:1000'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * NIK dan telepon dikosongkan bila kolomnya tidak diisi, bukan terkirim
     * sebagai string kosong yang akan menggagalkan aturan unique.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'nik' => $this->filled('nik') ? (string) $this->string('nik') : null,
            'phone' => $this->filled('phone') ? (string) $this->string('phone') : null,
        ]);
    }

    /**
     * Get the custom validation messages in Indonesian.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nik.size' => 'NIK harus terdiri dari 16 digit.',
            'nik.regex' => 'NIK hanya boleh berisi angka.',
            'nik.unique' => 'NIK tersebut sudah terdaftar.',
            'name.required' => 'Nama pasien wajib diisi.',
            'gender.required' => 'Jenis kelamin wajib dipilih.',
            'gender.enum' => 'Jenis kelamin tidak valid.',
            'birth_date.date' => 'Tanggal lahir tidak valid.',
            'birth_date.before_or_equal' => 'Tanggal lahir tidak boleh di masa depan.',
            'birth_date.after' => 'Tanggal lahir tidak valid.',
            'phone.regex' => 'Nomor telepon hanya boleh berisi angka, spasi, dan tanda hubung.',
            'blood_type.enum' => 'Golongan darah tidak valid.',
        ];
    }

    /**
     * Get custom attributes for validator errors in Indonesian.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'nik' => 'NIK',
            'name' => 'nama',
            'gender' => 'jenis kelamin',
            'birth_date' => 'tanggal lahir',
            'birth_place' => 'tempat lahir',
            'phone' => 'nomor telepon',
            'blood_type' => 'golongan darah',
            'address' => 'alamat',
            'allergies' => 'riwayat alergi',
            'notes' => 'catatan',
        ];
    }
}
