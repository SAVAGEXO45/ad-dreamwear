<?php

namespace App\Http\Requests;

use App\Services\CatalogueMedia;
use Closure;
use Illuminate\Foundation\Http\FormRequest;

class SaveProduct extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->is_admin;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'description' => ['sometimes', 'nullable', 'string', 'max:5000'],
            'sizes' => ['bail', 'sometimes', 'nullable', 'string', 'max:320', function (string $attribute, string $value, Closure $fail): void {
                $sizes = $this->parseSizes($value);

                if (count($sizes) > 12) {
                    $fail('Enter up to 12 sizes.');
                } elseif (collect($sizes)->contains(fn (string $size) => mb_strlen($size) > 24)) {
                    $fail('Each size must be at most 24 characters.');
                }
            }],
            'price' => ['required', 'regex:/^\d{1,8}(?:\.\d{1,2})?$/', 'numeric', 'min:0.01'],
            'sort_order' => ['required', 'integer', 'between:0,999999'],
            'main_image' => [$this->route('product') ? 'nullable' : 'required', ...CatalogueMedia::rules()],
            'photos' => ['nullable', 'array', 'max:12'],
            'photos.*' => CatalogueMedia::rules(),
            'remove_photos' => ['nullable', 'array', 'max:12'],
            'remove_photos.*' => ['integer', 'distinct'],
            'available' => ['sometimes', 'boolean'],
            'published' => ['sometimes', 'boolean'],
            'new_arrival' => ['sometimes', 'boolean'],
            'featured' => ['sometimes', 'boolean'],
        ];
    }

    public function sizes(): ?array
    {
        return $this->parseSizes($this->validated('sizes')) ?: null;
    }

    private function parseSizes(?string $input): array
    {
        return collect(explode(',', $input ?? ''))
            ->map(fn (string $size) => trim($size))
            ->filter(fn (string $size) => $size !== '')
            ->unique(fn (string $size) => mb_strtolower($size))
            ->values()->all();
    }

    public function attributes(): array
    {
        return ['category_id' => 'collection', 'photos.*' => 'additional photo', 'sort_order' => 'display position'];
    }
}
