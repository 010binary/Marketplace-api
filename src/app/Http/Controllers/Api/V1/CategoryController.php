<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Services\CategoryService;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;

#[OA\Tag(name: "Categories", description: "Category management endpoints")]
class CategoryController extends Controller
{
    public function __construct(
        private readonly CategoryService $categoryService,
    ) {}

    #[
        OA\Get(
            path: "/api/categories",
            summary: "Get paginated list of categories",
            description: "Retrieve a paginated list of all categories",
            security: [["sanctum" => []]],
            tags: ["Categories"],
            parameters: [
                new OA\Parameter(
                    name: "per_page",
                    in: "query",
                    description: "Number of items per page",
                    required: false,
                    schema: new OA\Schema(
                        type: "integer",
                        default: 15,
                        example: 15,
                    ),
                ),
                new OA\Parameter(
                    name: "page",
                    in: "query",
                    description: "Page number",
                    required: false,
                    schema: new OA\Schema(
                        type: "integer",
                        default: 1,
                        example: 1,
                    ),
                ),
            ],
            responses: [
                new OA\Response(
                    response: 200,
                    description: "Successful operation",
                    content: new OA\JsonContent(
                        ref: "#/components/schemas/CategoryPaginated",
                    ),
                ),
                new OA\Response(
                    response: 401,
                    description: "Unauthenticated",
                    content: new OA\JsonContent(
                        ref: "#/components/schemas/Unauthenticated",
                    ),
                ),
            ],
        ),
    ]
    public function index(Request $request)
    {
        $perPage = (int) $request->query("per_page", 15);

        return response()->json($this->categoryService->paginate($perPage));
    }

    #[
        OA\Get(
            path: "/api/categories/{id}",
            summary: "Get a specific category",
            description: "Retrieve details of a specific category by ID",
            security: [["sanctum" => []]],
            tags: ["Categories"],
            parameters: [
                new OA\Parameter(
                    name: "id",
                    in: "path",
                    description: "Category ID",
                    required: true,
                    schema: new OA\Schema(type: "integer", example: 1),
                ),
            ],
            responses: [
                new OA\Response(
                    response: 200,
                    description: "Successful operation",
                    content: new OA\JsonContent(
                        ref: "#/components/schemas/Category",
                    ),
                ),
                new OA\Response(
                    response: 401,
                    description: "Unauthenticated",
                    content: new OA\JsonContent(
                        ref: "#/components/schemas/Unauthenticated",
                    ),
                ),
                new OA\Response(
                    response: 404,
                    description: "Category not found",
                    content: new OA\JsonContent(
                        ref: "#/components/schemas/NotFound",
                    ),
                ),
            ],
        ),
    ]
    public function show(int $id)
    {
        return response()->json($this->categoryService->findById($id));
    }

    #[
        OA\Post(
            path: "/api/categories",
            summary: "Create a new category",
            description: "Create a new category (Creator only)",
            security: [["sanctum" => []]],
            tags: ["Categories"],
            requestBody: new OA\RequestBody(
                required: true,
                content: new OA\JsonContent(
                    required: ["name"],
                    properties: [
                        new OA\Property(
                            property: "name",
                            type: "string",
                            maxLength: 255,
                            example: "E-Books",
                        ),
                    ],
                ),
            ),
            responses: [
                new OA\Response(
                    response: 201,
                    description: "Category created successfully",
                    content: new OA\JsonContent(
                        ref: "#/components/schemas/Category",
                    ),
                ),
                new OA\Response(
                    response: 401,
                    description: "Unauthenticated",
                    content: new OA\JsonContent(
                        ref: "#/components/schemas/Unauthenticated",
                    ),
                ),
                new OA\Response(
                    response: 403,
                    description: "Forbidden - Creator role required",
                    content: new OA\JsonContent(
                        ref: "#/components/schemas/Forbidden",
                    ),
                ),
                new OA\Response(
                    response: 422,
                    description: "Validation error",
                    content: new OA\JsonContent(
                        ref: "#/components/schemas/ValidationError",
                    ),
                ),
            ],
        ),
    ]
    public function store(Request $request)
    {
        $validated = $request->validate([
            "name" => ["required", "string", "max:255"],
        ]);

        $category = $this->categoryService->create($validated["name"]);

        return response()->json($category, Response::HTTP_CREATED);
    }

    #[
        OA\Put(
            path: "/api/categories/{category}",
            summary: "Update a category",
            description: "Update an existing category (Creator only)",
            security: [["sanctum" => []]],
            tags: ["Categories"],
            parameters: [
                new OA\Parameter(
                    name: "category",
                    in: "path",
                    description: "Category ID",
                    required: true,
                    schema: new OA\Schema(type: "integer", example: 1),
                ),
            ],
            requestBody: new OA\RequestBody(
                required: true,
                content: new OA\JsonContent(
                    required: ["name"],
                    properties: [
                        new OA\Property(
                            property: "name",
                            type: "string",
                            maxLength: 255,
                            example: "Updated E-Books",
                        ),
                    ],
                ),
            ),
            responses: [
                new OA\Response(
                    response: 200,
                    description: "Category updated successfully",
                    content: new OA\JsonContent(
                        ref: "#/components/schemas/Category",
                    ),
                ),
                new OA\Response(
                    response: 401,
                    description: "Unauthenticated",
                    content: new OA\JsonContent(
                        ref: "#/components/schemas/Unauthenticated",
                    ),
                ),
                new OA\Response(
                    response: 403,
                    description: "Forbidden - Creator role required",
                    content: new OA\JsonContent(
                        ref: "#/components/schemas/Forbidden",
                    ),
                ),
                new OA\Response(
                    response: 404,
                    description: "Category not found",
                    content: new OA\JsonContent(
                        ref: "#/components/schemas/NotFound",
                    ),
                ),
                new OA\Response(
                    response: 422,
                    description: "Validation error",
                    content: new OA\JsonContent(
                        ref: "#/components/schemas/ValidationError",
                    ),
                ),
            ],
        ),
    ]
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

    #[
        OA\Delete(
            path: "/api/categories/{category}",
            summary: "Delete a category",
            description: "Delete a category (Creator only). Category must not have any products.",
            security: [["sanctum" => []]],
            tags: ["Categories"],
            parameters: [
                new OA\Parameter(
                    name: "category",
                    in: "path",
                    description: "Category ID",
                    required: true,
                    schema: new OA\Schema(type: "integer", example: 1),
                ),
            ],
            responses: [
                new OA\Response(
                    response: 204,
                    description: "Category deleted successfully",
                ),
                new OA\Response(
                    response: 401,
                    description: "Unauthenticated",
                    content: new OA\JsonContent(
                        ref: "#/components/schemas/Unauthenticated",
                    ),
                ),
                new OA\Response(
                    response: 403,
                    description: "Forbidden - Creator role required",
                    content: new OA\JsonContent(
                        ref: "#/components/schemas/Forbidden",
                    ),
                ),
                new OA\Response(
                    response: 404,
                    description: "Category not found",
                    content: new OA\JsonContent(
                        ref: "#/components/schemas/NotFound",
                    ),
                ),
                new OA\Response(
                    response: 422,
                    description: "Category has products attached",
                    content: new OA\JsonContent(
                        properties: [
                            new OA\Property(
                                property: "message",
                                type: "string",
                                example: "Category cannot be deleted because it has products attached.",
                            ),
                        ],
                    ),
                ),
            ],
        ),
    ]
    public function destroy(Category $category)
    {
        $this->categoryService->delete($category);

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
