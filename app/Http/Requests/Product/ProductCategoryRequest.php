<?php

declare(strict_types=1);

namespace App\Http\Requests\Product;

use App\Models\ProductCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductCategoryRequest extends FormRequest
{
    /**
     * Authorization is enforced via the policy in the controller
     * ($this->authorize()), not here — this keeps the "can this
     * role touch categories at all" check in one place (the policy)
     * rather than duplicated across every Form Request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var ProductCategory|null $category */
        $category = $this->route('category');

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('product_categories', 'name')->ignore($category?->id),
            ],
            'description' => ['nullable', 'string', 'max:1000'],
            // 'sometimes' is deliberate: omitted on create means the
            // column's DB-level default(true) applies; omitted on
            // update means the existing value is left untouched by
            // the repository's partial fill(). Explicitly sending
            // false is still fully honored either way.
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
