<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SaveDocumentTemplateSectionRequest extends FormRequest
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
            'key' => [
                'required',
                'string',
                'max:60',
                'regex:/^[a-z0-9_]+$/',
                Rule::unique('document_template_sections', 'key')
                    ->where('document_template_id', $this->route('template')->getKey()),
            ],
            'title' => ['required', 'string', 'max:120'],
            'hint' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Turunkan key dari judul bila pengguna tidak mengisinya, lalu samakan
     * formatnya dengan snake_case.
     */
    protected function prepareForValidation(): void
    {
        $source = $this->filled('key') ? (string) $this->string('key') : (string) $this->string('title');

        $this->merge([
            'key' => Str::of($source)->trim()->replaceMatches('/[\s-]+/', '_')->lower()->value(),
        ]);
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'key.regex' => 'Key bagian hanya boleh memakai huruf kecil, angka, dan garis bawah.',
            'key.unique' => 'Key ini sudah dipakai bagian lain pada template yang sama.',
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
            'key' => 'key bagian',
            'title' => 'judul bagian',
            'hint' => 'petunjuk pengisian',
        ];
    }
}
