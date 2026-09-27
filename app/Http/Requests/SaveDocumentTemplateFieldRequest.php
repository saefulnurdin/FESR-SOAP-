<?php

namespace App\Http\Requests;

use App\Enums\DocumentFieldType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SaveDocumentTemplateFieldRequest extends FormRequest
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
        $type = $this->resolvedType();

        return [
            'document_template_section_id' => [
                'required',
                Rule::exists('document_template_sections', 'id')
                    ->where('document_template_id', $this->route('template')->getKey()),
            ],
            'key' => [
                'required',
                'string',
                'max:60',
                'regex:/^[a-z0-9_]+$/',
                Rule::unique('document_template_fields', 'key')
                    ->where('document_template_section_id', $this->input('document_template_section_id'))
                    ->ignore($this->route('field'), 'id'),
            ],
            'label' => ['required', 'string', 'max:120'],
            'type' => ['required', Rule::enum(DocumentFieldType::class)],
            'options' => [
                Rule::requiredIf($type === DocumentFieldType::Select),
                'nullable',
                'array',
                'min:1',
            ],
            'options.*' => ['string', 'max:100'],
            'unit' => ['nullable', 'string', 'max:30'],
            'is_required' => ['required', 'boolean'],
        ];
    }

    /**
     * Ubah daftar opsi dari satu baris per pilihan menjadi array, dan buang
     * opsi pada field yang tidak memakai pilihan.
     */
    protected function prepareForValidation(): void
    {
        $type = $this->resolvedType();

        $options = $type === DocumentFieldType::Select
            ? collect(preg_split('/\R/', (string) $this->input('options', '')))
                ->map(fn (string $option): string => trim($option))
                ->filter()
                ->unique()
                ->values()
                ->all()
            : null;

        $label = (string) $this->string('label');
        $key = $this->filled('key') ? (string) $this->string('key') : $label;

        $this->merge([
            'key' => Str::of($key)->trim()->replaceMatches('/[\s-]+/', '_')->lower()->value(),
            'options' => $options,
        ]);
    }

    /**
     * Tipe yang sedang disunting, dengan nilai bawaan bila tidak valid.
     */
    private function resolvedType(): ?DocumentFieldType
    {
        return DocumentFieldType::tryFrom((string) $this->input('type', ''));
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'key.regex' => 'Key isian hanya boleh memakai huruf kecil, angka, dan garis bawah.',
            'key.unique' => 'Key ini sudah dipakai isian lain pada bagian yang sama.',
            'document_template_section_id.exists' => 'Bagian tersebut tidak ada pada template ini.',
            'options.required' => 'Isian pilihan wajib menyertakan minimal satu pilihan.',
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
            'document_template_section_id' => 'bagian',
            'key' => 'key isian',
            'label' => 'nama isian',
            'type' => 'tipe isian',
            'options' => 'daftar pilihan',
            'options.*' => 'pilihan',
            'unit' => 'satuan',
        ];
    }
}
