<?php

namespace App\Http\Controllers\Schemas;

use OpenApi\Attributes as OA;

#[
    OA\Schema(
        schema: "User",
        title: "User",
        description: "User model",
        required: ["id", "name", "email", "role"],
        properties: [
            new OA\Property(property: "id", type: "integer", example: 1),
            new OA\Property(
                property: "name",
                type: "string",
                example: "John Doe",
            ),
            new OA\Property(
                property: "email",
                type: "string",
                format: "email",
                example: "john@example.com",
            ),
            new OA\Property(
                property: "role",
                type: "string",
                enum: ["creator", "customer"],
                example: "creator",
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
class UserSchema
{
    //
}
