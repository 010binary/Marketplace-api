<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;

#[OA\Tag(name: "Products", description: "Product management endpoints")]
class ProductController extends Controller
{
    public function __construct(
        private readonly ProductService $productService,
    ) {}

    #[
        OA\Get(
            path: "/api/v1/products",
            summary: "Get paginated list of products",
            description: "Retrieve a paginated list of products with optional filtering, searching, and sorting",
            security: [["sanctum" => []]],
            tags: ["Products"],
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
                new OA\Parameter(
                    name: "category_id",
                    in: "query",
                    description: "Filter by category ID",
                    required: false,
                    schema: new OA\Schema(
                        type: "integer",
                        example: 1,
                    ),
                ),
                new OA\Parameter(
                    name: "search",
                    in: "query",
                    description: "Search by product title, description, or date (YYYY-MM-DD)",
                    required: false,
                    schema: new OA\Schema(
                        type: "string",
                        example: "Laravel",
                    ),
                ),
                new OA\Parameter(
                    name: "sort_by",
                    in: "query",
                    description: "Sort by field (price or created_at)",
                    required: false,
                    schema: new OA\Schema(
                        type: "string",
                        enum: ["price", "created_at"],
                        example: "price",
                    ),
                ),
                new OA\Parameter(
                    name: "sort_order",
                    in: "query",
                    description: "Sort order (asc or desc)",
                    required: false,
                    schema: new OA\Schema(
                        type: "string",
                        enum: ["asc", "desc"],
                        default: "desc",
                        example: "asc",
                    ),
                ),
            ],
            responses: [
                new OA\Response(
                    response: 200,
                    description: "Successful operation",
                    content: new OA\JsonContent(
                        ref: "#/components/schemas/ProductPaginated",
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
        $categoryId = $request->query("category_id");
        $search = $request->query("search");
        $sortBy = $request->query("sort_by");
        $sortOrder = $request->query("sort_order", "desc");

        return response()->json(
            $this->productService->paginate(
                $perPage,
                $categoryId ? (int) $categoryId : null,
                $search,
                $sortBy,
                $sortOrder
            )
        );
    }

    #[
        OA\Get(
            path: "/api/v1/products/{id}",
            summary: "Get a specific product",
            description: "Retrieve details of a specific product by ID",
            security: [["sanctum" => []]],
            tags: ["Products"],
            parameters: [
                new OA\Parameter(
                    name: "id",
                    in: "path",
                    description: "Product ID",
                    required: true,
                    schema: new OA\Schema(type: "integer", example: 1),
                ),
            ],
            responses: [
                new OA\Response(
                    response: 200,
                    description: "Successful operation",
                    content: new OA\JsonContent(
                        ref: "#/components/schemas/Product",
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
                    description: "Product not found",
                    content: new OA\JsonContent(
                        ref: "#/components/schemas/NotFound",
                    ),
                ),
            ],
        ),
    ]
    public function show(int $id)
    {
        return response()->json($this->productService->findById($id));
    }

    #[
        OA\Post(
            path: "/api/v1/products",
            summary: "Create a new product",
            description: "Create a new product (Creator only). Product will be created as unpublished by default.",
            security: [["sanctum" => []]],
            tags: ["Products"],
            requestBody: new OA\RequestBody(
                required: true,
                content: new OA\JsonContent(
                    required: ["category_id", "title", "price"],
                    properties: [
                        new OA\Property(
                            property: "category_id",
                            type: "integer",
                            example: 1,
                        ),
                        new OA\Property(
                            property: "title",
                            type: "string",
                            maxLength: 255,
                            example: "Advanced Laravel Course",
                        ),
                        new OA\Property(
                            property: "description",
                            type: "string",
                            nullable: true,
                            example: "Learn advanced Laravel concepts",
                        ),
                        new OA\Property(
                            property: "price",
                            type: "number",
                            format: "decimal",
                            minimum: 0,
                            example: 49.99,
                        ),
                    ],
                ),
            ),
            responses: [
                new OA\Response(
                    response: 201,
                    description: "Product created successfully",
                    content: new OA\JsonContent(
                        ref: "#/components/schemas/Product",
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
                    response: 400,
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
            "category_id" => ["required", "integer", "exists:categories,id"],
            "title" => ["required", "string", "max:255"],
            "description" => ["nullable", "string"],
            "price" => ["required", "numeric", "min:0"],
        ]);

        $product = $this->productService->create(
            $validated,
            $request->user()
        );

        return response()->json($product, Response::HTTP_CREATED);
    }

    #[
        OA\Put(
            path: "/api/v1/products/{product}",
            summary: "Update a product",
            description: "Update an existing product. Only the creator who owns the product can update it.",
            security: [["sanctum" => []]],
            tags: ["Products"],
            parameters: [
                new OA\Parameter(
                    name: "product",
                    in: "path",
                    description: "Product ID",
                    required: true,
                    schema: new OA\Schema(type: "integer", example: 1),
                ),
            ],
            requestBody: new OA\RequestBody(
                required: true,
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: "category_id",
                            type: "integer",
                            example: 1,
                        ),
                        new OA\Property(
                            property: "title",
                            type: "string",
                            maxLength: 255,
                            example: "Updated Laravel Course",
                        ),
                        new OA\Property(
                            property: "description",
                            type: "string",
                            nullable: true,
                            example: "Updated description",
                        ),
                        new OA\Property(
                            property: "price",
                            type: "number",
                            format: "decimal",
                            minimum: 0,
                            example: 59.99,
                        ),
                    ],
                ),
            ),
            responses: [
                new OA\Response(
                    response: 200,
                    description: "Product updated successfully",
                    content: new OA\JsonContent(
                        ref: "#/components/schemas/Product",
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
                    description: "Forbidden - Not the product owner",
                    content: new OA\JsonContent(
                        properties: [
                            new OA\Property(
                                property: "message",
                                type: "string",
                                example: "You are not authorized to perform this action on this product.",
                            ),
                        ],
                    ),
                ),
                new OA\Response(
                    response: 404,
                    description: "Product not found",
                    content: new OA\JsonContent(
                        ref: "#/components/schemas/NotFound",
                    ),
                ),
                new OA\Response(
                    response: 400,
                    description: "Validation error",
                    content: new OA\JsonContent(
                        ref: "#/components/schemas/ValidationError",
                    ),
                ),
            ],
        ),
    ]
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            "category_id" => ["sometimes", "integer", "exists:categories,id"],
            "title" => ["sometimes", "string", "max:255"],
            "description" => ["nullable", "string"],
            "price" => ["sometimes", "numeric", "min:0"],
        ]);

        $updated = $this->productService->update(
            $product,
            $validated,
            $request->user()
        );

        return response()->json($updated);
    }

    #[
        OA\Delete(
            path: "/api/v1/products/{product}",
            summary: "Delete a product",
            description: "Delete a product. Only the creator who owns the product can delete it. Product must not have any purchases.",
            security: [["sanctum" => []]],
            tags: ["Products"],
            parameters: [
                new OA\Parameter(
                    name: "product",
                    in: "path",
                    description: "Product ID",
                    required: true,
                    schema: new OA\Schema(type: "integer", example: 1),
                ),
            ],
            responses: [
                new OA\Response(
                    response: 204,
                    description: "Product deleted successfully",
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
                    description: "Forbidden - Not the product owner",
                    content: new OA\JsonContent(
                        properties: [
                            new OA\Property(
                                property: "message",
                                type: "string",
                                example: "You are not authorized to perform this action on this product.",
                            ),
                        ],
                    ),
                ),
                new OA\Response(
                    response: 404,
                    description: "Product not found",
                    content: new OA\JsonContent(
                        ref: "#/components/schemas/NotFound",
                    ),
                ),
                new OA\Response(
                    response: 400,
                    description: "Product has purchases attached",
                    content: new OA\JsonContent(
                        properties: [
                            new OA\Property(
                                property: "message",
                                type: "string",
                                example: "Product cannot be deleted because it has purchases.",
                            ),
                        ],
                    ),
                ),
            ],
        ),
    ]
    public function destroy(Request $request, Product $product)
    {
        $this->productService->delete($product, $request->user());

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    #[
        OA\Post(
            path: "/api/v1/products/{product}/publish",
            summary: "Publish a product",
            description: "Publish a product to make it active and available for purchase. Only the creator who owns the product can publish it. Product must have both an image and a file.",
            security: [["sanctum" => []]],
            tags: ["Products"],
            parameters: [
                new OA\Parameter(
                    name: "product",
                    in: "path",
                    description: "Product ID",
                    required: true,
                    schema: new OA\Schema(type: "integer", example: 1),
                ),
            ],
            responses: [
                new OA\Response(
                    response: 200,
                    description: "Product published successfully",
                    content: new OA\JsonContent(
                        ref: "#/components/schemas/Product",
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
                    description: "Forbidden - Not the product owner or Creator role required",
                    content: new OA\JsonContent(
                        properties: [
                            new OA\Property(
                                property: "message",
                                type: "string",
                                example: "You are not authorized to perform this action on this product.",
                            ),
                        ],
                    ),
                ),
                new OA\Response(
                    response: 404,
                    description: "Product not found",
                    content: new OA\JsonContent(
                        ref: "#/components/schemas/NotFound",
                    ),
                ),
                new OA\Response(
                    response: 400,
                    description: "Product missing required files",
                    content: new OA\JsonContent(
                        properties: [
                            new OA\Property(
                                property: "message",
                                type: "string",
                                example: "Product must have both a file and an image before publishing.",
                            ),
                        ],
                    ),
                ),
            ],
        ),
    ]
    public function publish(Request $request, Product $product)
    {
        $published = $this->productService->publish(
            $product,
            $request->user()
        );

        return response()->json($published);
    }

    #[
        OA\Post(
            path: "/api/v1/products/{product}/unpublish",
            summary: "Unpublish a product",
            description: "Unpublish a product to make it inactive and unavailable for purchase. Only the creator who owns the product can unpublish it.",
            security: [["sanctum" => []]],
            tags: ["Products"],
            parameters: [
                new OA\Parameter(
                    name: "product",
                    in: "path",
                    description: "Product ID",
                    required: true,
                    schema: new OA\Schema(type: "integer", example: 1),
                ),
            ],
            responses: [
                new OA\Response(
                    response: 200,
                    description: "Product unpublished successfully",
                    content: new OA\JsonContent(
                        ref: "#/components/schemas/Product",
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
                    description: "Forbidden - Not the product owner or Creator role required",
                    content: new OA\JsonContent(
                        properties: [
                            new OA\Property(
                                property: "message",
                                type: "string",
                                example: "You are not authorized to perform this action on this product.",
                            ),
                        ],
                    ),
                ),
                new OA\Response(
                    response: 404,
                    description: "Product not found",
                    content: new OA\JsonContent(
                        ref: "#/components/schemas/NotFound",
                    ),
                ),
            ],
        ),
    ]
    public function unpublish(Request $request, Product $product)
    {
        $unpublished = $this->productService->unpublish(
            $product,
            $request->user()
        );

        return response()->json($unpublished);
    }

    #[
        OA\Get(
            path: "/api/v1/products/my-products",
            summary: "Get creator's own products",
            description: "Retrieve a paginated list of products created by the authenticated user (Creator only)",
            security: [["sanctum" => []]],
            tags: ["Products"],
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
                        ref: "#/components/schemas/ProductPaginated",
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
            ],
        ),
    ]
    public function myProducts(Request $request)
    {
        $perPage = (int) $request->query("per_page", 15);

        return response()->json(
            $this->productService->getCreatorProducts(
                $request->user(),
                $perPage
            )
        );
    }
}
