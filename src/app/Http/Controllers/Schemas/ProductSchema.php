<?php

namespace App\Http\Controllers\Schemas;

use OpenApi\Attributes as OA;

#[
    OA\Schema(
        schema: "Product",
        title: "Product",
        description: "Product model",
        required: ["id", "creator_id", "category_id", "title", "price", "is_active"],
        properties: [
            new OA\Property(property: "id", type: "integer", example: 1),
            new OA\Property(property: "creator_id", type: "integer", example: 1),
            new OA\Property(property: "category_id", type: "integer", example: 1),
            new OA\Property(
                property: "title",
                type: "string",
                example: "Advanced Laravel Course"
            ),
            new OA\Property(
                property: "description",
                type: "string",
                nullable: true,
                example: "Learn advanced Laravel concepts and best practices"
            ),
            new OA\Property(
                property: "price",
                type: "number",
                format: "decimal",
                example: 49.99
            ),
            new OA\Property(
                property: "is_active",
                type: "boolean",
                example: true
            ),
            new OA\Property(
                property: "created_at",
                type: "string",
                format: "date-time",
                example: "2026-01-27T03:16:30.000000Z"
            ),
            new OA\Property(
                property: "updated_at",
                type: "string",
                format: "date-time",
                example: "2026-01-27T03:16:30.000000Z"
            ),
            new OA\Property(
                property: "creator",
                ref: "#/components/schemas/User",
                type: "object"
            ),
            new OA\Property(
                property: "category",
                ref: "#/components/schemas/Category",
                type: "object"
            ),
            new OA\Property(
                property: "image",
                ref: "#/components/schemas/ProductImage",
                type: "object",
                nullable: true
            ),
            new OA\Property(
                property: "file",
                ref: "#/components/schemas/ProductFile",
                type: "object",
                nullable: true
            ),
        ],
        type: "object"
    ),
]
#[
    OA\Schema(
        schema: "ProductFile",
        title: "ProductFile",
        description: "Product file model",
        properties: [
            new OA\Property(property: "id", type: "integer", example: 1),
            new OA\Property(property: "product_id", type: "integer", example: 1),
            new OA\Property(property: "disk", type: "string", example: "private"),
            new OA\Property(
                property: "path",
                type: "string",
                example: "products/1/file/abc123.zip"
            ),
            new OA\Property(
                property: "original_filename",
                type: "string",
                example: "course-materials.zip"
            ),
            new OA\Property(
                property: "mime_type",
                type: "string",
                example: "application/zip"
            ),
            new OA\Property(property: "size", type: "integer", example: 1048576),
            new OA\Property(
                property: "created_at",
                type: "string",
                format: "date-time",
                example: "2026-01-27T03:16:30.000000Z"
            ),
            new OA\Property(
                property: "updated_at",
                type: "string",
                format: "date-time",
                example: "2026-01-27T03:16:30.000000Z"
            ),
        ],
        type: "object"
    ),
]
#[
    OA\Schema(
        schema: "ProductImage",
        title: "ProductImage",
        description: "Product image model",
        properties: [
            new OA\Property(property: "id", type: "integer", example: 1),
            new OA\Property(property: "product_id", type: "integer", example: 1),
            new OA\Property(property: "disk", type: "string", example: "private"),
            new OA\Property(
                property: "path",
                type: "string",
                example: "products/1/image/abc123.jpg"
            ),
            new OA\Property(
                property: "mime_type",
                type: "string",
                example: "image/jpeg"
            ),
            new OA\Property(property: "size", type: "integer", example: 204800),
            new OA\Property(
                property: "created_at",
                type: "string",
                format: "date-time",
                example: "2026-01-27T03:16:30.000000Z"
            ),
            new OA\Property(
                property: "updated_at",
                type: "string",
                format: "date-time",
                example: "2026-01-27T03:16:30.000000Z"
            ),
        ],
        type: "object"
    ),
]
#[
    OA\Schema(
        schema: "ProductPaginated",
        title: "Paginated Products",
        description: "Paginated list of products",
        properties: [
            new OA\Property(
                property: "data",
                type: "array",
                items: new OA\Items(ref: "#/components/schemas/Product")
            ),
            new OA\Property(
                property: "current_page",
                type: "integer",
                example: 1
            ),
            new OA\Property(
                property: "first_page_url",
                type: "string",
                example: "http://localhost:8000/api/v1/products?page=1"
            ),
            new OA\Property(property: "from", type: "integer", example: 1),
            new OA\Property(property: "last_page", type: "integer", example: 5),
            new OA\Property(
                property: "last_page_url",
                type: "string",
                example: "http://localhost:8000/api/v1/products?page=5"
            ),
            new OA\Property(
                property: "links",
                type: "array",
                items: new OA\Items(
                    properties: [
                        new OA\Property(
                            property: "url",
                            type: "string",
                            nullable: true
                        ),
                        new OA\Property(property: "label", type: "string"),
                        new OA\Property(property: "active", type: "boolean"),
                    ],
                    type: "object"
                )
            ),
            new OA\Property(
                property: "next_page_url",
                type: "string",
                nullable: true,
                example: "http://localhost:8000/api/v1/products?page=2"
            ),
            new OA\Property(
                property: "path",
                type: "string",
                example: "http://localhost:8000/api/v1/products"
            ),
            new OA\Property(property: "per_page", type: "integer", example: 15),
            new OA\Property(
                property: "prev_page_url",
                type: "string",
                nullable: true
            ),
            new OA\Property(property: "to", type: "integer", example: 15),
            new OA\Property(property: "total", type: "integer", example: 50),
        ],
        type: "object"
    ),
]
class ProductSchema
{
    //
}
