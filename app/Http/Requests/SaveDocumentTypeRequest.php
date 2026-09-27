<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SaveDocumentTypeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('manage-document-templates') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'code' => [
                'required',
                'string',
                'max:50',
                'regex:/^[a-z0-9_]+$/',
                /*
                 * Saat menyunting, kode yang sedang dipakai baris ini sendiri
                 * tidak boleh dianggap sebagai duplikat.
                 */
                Rule::unique('document_types', 'code')->ignore($this->route('documentType')),
            ],
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    /**
     * Normalize the code so it stays easy to type in the address bar.
     */
    protected function prepareForValidation(): void
    {
        if ($this->filled('code')) {
            $this->merge([
                'code' => Str::of($this->string('code'))->trim()->lower()->value(),
            ]);
        }
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'code.regex' => 'Kode jenis dokumen hanya boleh memakai huruf kecil, angka, dan garis bawah.',
            'code.unique' => 'Kode jenis dokumen sudah digunakan.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'code' => 'kode jenis dokumen',
            'name' => 'nama jenis dokumen',
            'description' => 'keterangan',
        ];
    }
}
