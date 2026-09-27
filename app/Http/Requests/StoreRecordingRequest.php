<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Aturan yang sama berlaku untuk rekaman dari browser maupun dari perangkat
 * ESP32, karena keduanya mengirim berkas audio ke endpoint yang sama.
 */
class StoreRecordingRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'audio' => [
                'required',
                'file',
                'mimetypes:'.implode(',', (array) config('fesr.audio.allowed_mimes')),
                'max:'.config('fesr.audio.max_size_kb'),
            ],
            'duration_seconds' => ['nullable', 'integer', 'min:1', 'max:'.config('fesr.audio.max_duration_seconds')],
            'captured_at' => ['nullable', 'date'],
            'transcript' => ['nullable', 'string', 'max:10000'],
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
            'audio.required' => 'Rekaman wajib diunggah.',
            'audio.mimetypes' => 'Format rekaman harus salah satu dari: '.implode(', ', (array) config('fesr.audio.allowed_mimes')).'.',
            'audio.max' => 'Ukuran rekaman maksimal '.round((int) config('fesr.audio.max_size_kb') / 1024).' MB.',
            'duration_seconds.max' => 'Durasi rekaman maksimal 10 menit.',
            'captured_at.date' => 'Waktu rekaman tidak valid.',
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
            'audio' => 'rekaman',
            'duration_seconds' => 'durasi rekaman',
            'captured_at' => 'waktu rekaman',
            'transcript' => 'transkrip',
        ];
    }
}
