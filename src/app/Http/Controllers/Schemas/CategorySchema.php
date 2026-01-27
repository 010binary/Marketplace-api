<?php

namespace App\Http\Controllers\Schemas;

use OpenApi\Attributes as OA;

#[
    OA\Schema(
        schema: "Category",
        title: "Category",
        description: "Category model",
        required: ["id", "name", "slug"],
        properties: [
            new OA\Property(property: "id", type: "integer", example: 1),
            new OA\Property(
                property: "name",
                type: "string",
                example: "E-Books",
            ),
            new OA\Property(
                property: "slug",
                type: "string",
                example: "e_books",
            ),
            new OA\Property(
                property: "created_at",
                type: "string",
                format: "date-time",
                example: "2026-01-27T03:16:30.000000Z",
            ),
            new OA\Property(
                property: "updated_at",
                type: "string",
                format: "date-time",
                example: "2026-01-27T03:16:30.000000Z",
            ),
        ],
        type: "object",
    ),
]
#[
    OA\Schema(
        schema: "CategoryPaginated",
        title: "Paginated Categories",
        description: "Paginated list of categories",
        properties: [
            new OA\Property(
                property: "data",
                type: "array",
                items: new OA\Items(ref: "#/components/schemas/Category"),
            ),
            new OA\Property(
                property: "current_page",
                type: "integer",
                example: 1,
            ),
            new OA\Property(
                property: "first_page_url",
                type: "string",
                example: "http://localhost:8000/api/categories?page=1",
            ),
            new OA\Property(property: "from", type: "integer", example: 1),
            new OA\Property(property: "last_page", type: "integer", example: 5),
            new OA\Property(
                property: "last_page_url",
                type: "string",
                example: "http://localhost:8000/api/categories?page=5",
            ),
            new OA\Property(
                property: "links",
                type: "array",
                items: new OA\Items(
                    properties: [
                        new OA\Property(
                            property: "url",
                            type: "string",
                            nullable: true,
                        ),
                        new OA\Property(property: "label", type: "string"),
                        new OA\Property(property: "active", type: "boolean"),
                    ],
                    type: "object",
                ),
            ),
            new OA\Property(
                property: "next_page_url",
                type: "string",
                nullable: true,
                example: "http://localhost:8000/api/categories?page=2",
            ),
            new OA\Property(
                property: "path",
                type: "string",
                example: "http://localhost:8000/api/categories",
            ),
            new OA\Property(property: "per_page", type: "integer", example: 15),
            new OA\Property(
                property: "prev_page_url",
                type: "string",
                nullable: true,
            ),
            new OA\Property(property: "to", type: "integer", example: 15),
            new OA\Property(property: "total", type: "integer", example: 50),
        ],
        type: "object",
    ),
]
class CategorySchema
{
    //
}
