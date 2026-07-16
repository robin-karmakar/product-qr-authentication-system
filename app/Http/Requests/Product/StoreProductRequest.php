<?php

declare(strict_types=1);

namespace App\Http\Requests\Product;

use App\Enums\ProductStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductRequest extends FormRequest
{
    /**
     * Authorization is enforced via the Policy in the controller
     * ($this->authorize()), not here — consistent with
     * ProductCategoryRequest's approach.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * SKU is normalized to uppercase before validation so the
     * uniqueness and format checks below operate on the final stored
     * form. ProductService::createProduct() also uppercases
     * defensively, so this stays correct even if the service is ever
     * called with unnormalized input from somewhere other than this
     * request.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'sku' => strtoupper((string) $this->input('sku')),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'category_uuid' => [
                'required',
                'uuid',
                Rule::exists('product_categories', 'uuid')->where('is_active', true),
            ],
            'name' => ['required', 'string', 'max:255'],
            'sku' => [
                'required',
                'string',
                'max:100',
                'regex:/^[A-Z0-9-]+$/',
                Rule::unique('products', 'sku'),
            ],
            'description' => ['nullable', 'string', 'max:5000'],
            'status' => ['required', 'string', Rule::in(ProductStatus::values())],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'category_uuid.exists' => 'The selected category does not exist or is not active.',
            'sku.regex' => 'SKU may only contain uppercase letters, numbers, and hyphens.',
        ];
    }
}
