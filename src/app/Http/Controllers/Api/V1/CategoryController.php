<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Services\CategoryService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CategoryController extends Controller
{
    public function __construct(
        private readonly CategoryService $categoryService,
    ) {}

    public function index(Request $request)
    {
        $perPage = (int) $request->query("per_page", 15);

        return response()->json($this->categoryService->paginate($perPage));
    }

    public function show(int $id)
    {
        return response()->json($this->categoryService->findById($id));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            "name" => ["required", "string", "max:255"],
        ]);

        $category = $this->categoryService->create($validated["name"]);

        return response()->json($category, Response::HTTP_CREATED);
    }

    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            "name" => ["required", "string", "max:255"],
        ]);

        $updated = $this->categoryService->update(
            $category,
            $validated["name"],
        );

        return response()->json($updated);
    }

    public function destroy(Category $category)
    {
        $this->categoryService->delete($category);

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
