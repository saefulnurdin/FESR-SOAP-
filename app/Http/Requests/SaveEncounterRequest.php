<?php

namespace App\Http\Requests;

use App\Enums\EncounterStatus;
use App\Enums\VisitType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Aturan yang sama berlaku untuk membuat maupun menyunting kunjungan, sehingga
 * cukup satu form request dipakai keduanya.
 */
class SaveEncounterRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'occurred_at' => ['required', 'date'],
            'visit_type' => ['required', Rule::enum(VisitType::class)],
            'status' => ['required', Rule::enum(EncounterStatus::class)],
            'chief_complaint' => ['nullable', 'string', 'max:1000'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Get the custom validation messages in Indonesian.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'occurred_at.required' => 'Waktu kunjungan wajib diisi.',
            'occurred_at.date' => 'Waktu kunjungan tidak valid.',
            'visit_type.required' => 'Jenis kunjungan wajib dipilih.',
            'visit_type.enum' => 'Jenis kunjungan tidak valid.',
            'status.required' => 'Status kunjungan wajib dipilih.',
            'status.enum' => 'Status kunjungan tidak valid.',
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
            'occurred_at' => 'waktu kunjungan',
            'visit_type' => 'jenis kunjungan',
            'status' => 'status kunjungan',
            'chief_complaint' => 'keluhan utama',
            'notes' => 'catatan',
        ];
    }
}
