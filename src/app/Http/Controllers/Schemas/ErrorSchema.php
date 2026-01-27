<?php

namespace App\Http\Controllers\Schemas;

use OpenApi\Attributes as OA;

#[
    OA\Schema(
        schema: "ValidationError",
        title: "Validation Error",
        description: "Validation error response",
        properties: [
            new OA\Property(
                property: "message",
                type: "string",
                example: "The given data was invalid.",
            ),
            new OA\Property(
                property: "errors",
                type: "object",
                example: ["field" => ["The field is required."]],
            ),
        ],
        type: "object",
    ),
]
#[
    OA\Schema(
        schema: "Unauthenticated",
        title: "Unauthenticated",
        description: "Unauthenticated response",
        properties: [
            new OA\Property(
                property: "message",
                type: "string",
                example: "Unauthenticated.",
            ),
        ],
        type: "object",
    ),
]
#[
    OA\Schema(
        schema: "Forbidden",
        title: "Forbidden",
        description: "Forbidden response",
        properties: [
            new OA\Property(
                property: "message",
                type: "string",
                example: "This action is unauthorized.",
            ),
        ],
        type: "object",
    ),
]
#[
    OA\Schema(
        schema: "NotFound",
        title: "Not Found",
        description: "Resource not found response",
        properties: [
            new OA\Property(
                property: "message",
                type: "string",
                example: "Resource not found",
            ),
        ],
        type: "object",
    ),
]
class ErrorSchema
{
    //
}
