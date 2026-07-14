<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\ProductCategoryRequest;
use App\Http\Responses\ApiResponseTrait;
use App\Models\ProductCategory;
use App\Services\ProductCategoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class ProductCategoryController extends Controller
{
    use ApiResponseTrait;

    public function __construct(private readonly ProductCategoryService $categoryService)
    {
    }

    public function index(): View
    {
        $this->authorize('viewAny', ProductCategory::class);

        return view('admin.categories.index', [
            'categories' => $this->categoryService->listCategories(),
        ]);
    }

    public function store(ProductCategoryRequest $request): JsonResponse
    {
        $this->authorize('create', ProductCategory::class);

        $category = $this->categoryService->createCategory($request->validated());

        return $this->created([
            'uuid' => $category->uuid,
            'name' => $category->name,
            'slug' => $category->slug,
        ], 'Category created successfully.');
    }

    public function update(ProductCategoryRequest $request, ProductCategory $category): JsonResponse
    {
        $this->authorize('update', $category);

        $category = $this->categoryService->updateCategory($category, $request->validated());

        return $this->success([
            'uuid' => $category->uuid,
            'name' => $category->name,
            'slug' => $category->slug,
        ], 'Category updated successfully.');
    }

    public function destroy(ProductCategory $category): JsonResponse
    {
        $this->authorize('delete', $category);

        $this->categoryService->deleteCategory($category);

        return $this->success(null, 'Category deleted successfully.');
    }
}
