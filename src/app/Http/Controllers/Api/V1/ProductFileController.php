<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\ProductFileService;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;

#[
    OA\Tag(
        name: "Product Files",
        description: "Product file and image upload endpoints",
    ),
]
class ProductFileController extends Controller
{
    public function __construct(
        private readonly ProductFileService $fileService,
    ) {}

    #[
        OA\Post(
            path: "/api/v1/products/{product}/upload-file",
            summary: "Upload product file",
            description: "Upload or replace the downloadable file for a product. Only the creator who owns the product can upload files. Maximum file size: ~500MB",
            security: [["sanctum" => []]],
            tags: ["Product Files"],
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
                content: new OA\MediaType(
                    mediaType: "multipart/form-data",
                    schema: new OA\Schema(
                        required: ["file"],
                        properties: [
                            new OA\Property(
                                property: "file",
                                type: "string",
                                format: "binary",
                                description: "Product file (max: 500MB)",
                            ),
                        ],
                    ),
                ),
            ),
            responses: [
                new OA\Response(
                    response: 201,
                    description: "File uploaded successfully",
                    content: new OA\JsonContent(
                        ref: "#/components/schemas/ProductFile",
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
                                example: "Unauthorized",
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
    public function uploadFile(Request $request, Product $product)
    {
        $this->authorizeOwnership($product, $request);

        $request->validate([
            "file" => ["required", "file", "max:512000"], // ~500MB
        ]);

        return response()->json(
            $this->fileService->uploadProductFile(
                $product,
                $request->file("file"),
            ),
            Response::HTTP_CREATED,
        );
    }

    #[
        OA\Post(
            path: "/api/v1/products/{product}/upload-image",
            summary: "Upload product display image",
            description: "Upload or replace the display image for a product. Only the creator who owns the product can upload images. Maximum file size: 5MB",
            security: [["sanctum" => []]],
            tags: ["Product Files"],
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
                content: new OA\MediaType(
                    mediaType: "multipart/form-data",
                    schema: new OA\Schema(
                        required: ["image"],
                        properties: [
                            new OA\Property(
                                property: "image",
                                type: "string",
                                format: "binary",
                                description: "Product image (max: 5MB)",
                            ),
                        ],
                    ),
                ),
            ),
            responses: [
                new OA\Response(
                    response: 201,
                    description: "Image uploaded successfully",
                    content: new OA\JsonContent(
                        ref: "#/components/schemas/ProductImage",
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
                                example: "Unauthorized",
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
    public function uploadImage(Request $request, Product $product)
    {
        $this->authorizeOwnership($product, $request);

        $request->validate([
            "image" => ["required", "image", "max:5120"], // 5MB
        ]);

        return response()->json(
            $this->fileService->uploadDisplayImage(
                $product,
                $request->file("image"),
            ),
            Response::HTTP_CREATED,
        );
    }

    private function authorizeOwnership(
        Product $product,
        Request $request,
    ): void {
        if ($product->creator_id !== $request->user()->id) {
            abort(403, "Unauthorized");
        }
    }
}
