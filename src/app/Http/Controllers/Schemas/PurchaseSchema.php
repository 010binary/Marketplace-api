<?php

namespace App\Http\Controllers\Schemas;

use OpenApi\Attributes as OA;

#[
    OA\Schema(
        schema: "Purchase",
        title: "Purchase",
        description: "Purchase model",
        required: ["id", "user_id", "product_id", "reference", "status"],
        properties: [
            new OA\Property(property: "id", type: "integer", example: 1),
            new OA\Property(property: "user_id", type: "integer", example: 10),
            new OA\Property(property: "product_id", type: "integer", example: 5),
            new OA\Property(
                property: "reference",
                type: "string",
                example: "PUR-ABC123DEF456"
            ),
            new OA\Property(
                property: "status",
                type: "string",
                enum: ["pending", "completed", "failed"],
                example: "completed"
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
                property: "product",
                ref: "#/components/schemas/Product",
                type: "object"
            ),
            new OA\Property(
                property: "user",
                ref: "#/components/schemas/User",
                type: "object",
                nullable: true
            ),
            new OA\Property(
                property: "download_logs",
                type: "array",
                items: new OA\Items(ref: "#/components/schemas/DownloadLog"),
                nullable: true
            ),
        ],
        type: "object"
    ),
]
#[
    OA\Schema(
        schema: "DownloadLog",
        title: "DownloadLog",
        description: "Download log model",
        properties: [
            new OA\Property(property: "id", type: "integer", example: 1),
            new OA\Property(property: "purchase_id", type: "integer", example: 1),
            new OA\Property(
                property: "ip_address",
                type: "string",
                example: "192.168.1.1"
            ),
            new OA\Property(
                property: "downloaded_at",
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
        schema: "PurchasePaginated",
        title: "Paginated Purchases",
        description: "Paginated list of purchases",
        properties: [
            new OA\Property(
                property: "data",
                type: "array",
                items: new OA\Items(ref: "#/components/schemas/Purchase")
            ),
            new OA\Property(
                property: "current_page",
                type: "integer",
                example: 1
            ),
            new OA\Property(
                property: "first_page_url",
                type: "string",
                example: "http://localhost:8000/api/v1/library?page=1"
            ),
            new OA\Property(property: "from", type: "integer", example: 1),
            new OA\Property(property: "last_page", type: "integer", example: 5),
            new OA\Property(
                property: "last_page_url",
                type: "string",
                example: "http://localhost:8000/api/v1/library?page=5"
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
                example: "http://localhost:8000/api/v1/library?page=2"
            ),
            new OA\Property(
                property: "path",
                type: "string",
                example: "http://localhost:8000/api/v1/library"
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
#[
    OA\Schema(
        schema: "CheckoutResponse",
        title: "Checkout Response",
        description: "Response after successful checkout",
        properties: [
            new OA\Property(
                property: "message",
                type: "string",
                example: "Product purchased successfully"
            ),
            new OA\Property(
                property: "purchase",
                ref: "#/components/schemas/Purchase",
                type: "object"
            ),
        ],
        type: "object"
    ),
]
#[
    OA\Schema(
        schema: "DownloadUrlResponse",
        title: "Download URL Response",
        description: "Response containing temporary signed download URL",
        properties: [
            new OA\Property(
                property: "download_url",
                type: "string",
                example: "http://localhost:8000/api/v1/download/5?expires=1706328990&signature=abc123..."
            ),
            new OA\Property(
                property: "expires_at",
                type: "string",
                format: "date-time",
                example: "2026-01-27T04:16:30.000000Z"
            ),
            new OA\Property(
                property: "expires_in_minutes",
                type: "integer",
                example: 60
            ),
        ],
        type: "object"
    ),
]
#[
    OA\Schema(
        schema: "DownloadInfo",
        title: "Download Information",
        description: "Information about a downloadable product",
        properties: [
            new OA\Property(property: "product_id", type: "integer", example: 5),
            new OA\Property(
                property: "product_title",
                type: "string",
                example: "Advanced Laravel Course"
            ),
            new OA\Property(
                property: "file_name",
                type: "string",
                example: "course-materials.zip"
            ),
            new OA\Property(property: "file_size", type: "integer", example: 1048576),
            new OA\Property(
                property: "file_size_formatted",
                type: "string",
                example: "1.0 MB"
            ),
            new OA\Property(
                property: "mime_type",
                type: "string",
                example: "application/zip"
            ),
            new OA\Property(
                property: "purchased_at",
                type: "string",
                format: "date-time",
                example: "2026-01-27T03:16:30.000000Z"
            ),
            new OA\Property(
                property: "download_stats",
                type: "object",
                properties: [
                    new OA\Property(
                        property: "total_downloads",
                        type: "integer",
                        example: 3
                    ),
                    new OA\Property(
                        property: "first_download",
                        type: "string",
                        format: "date-time",
                        nullable: true,
                        example: "2026-01-27T03:20:00.000000Z"
                    ),
                    new OA\Property(
                        property: "last_download",
                        type: "string",
                        format: "date-time",
                        nullable: true,
                        example: "2026-01-28T10:15:00.000000Z"
                    ),
                    new OA\Property(
                        property: "recent_downloads",
                        type: "array",
                        items: new OA\Items(
                            properties: [
                                new OA\Property(
                                    property: "downloaded_at",
                                    type: "string",
                                    format: "date-time"
                                ),
                                new OA\Property(
                                    property: "ip_address",
                                    type: "string"
                                ),
                            ],
                            type: "object"
                        )
                    ),
                ],
            ),
        ],
        type: "object"
    ),
]
#[
    OA\Schema(
        schema: "CreatorRevenue",
        title: "Creator Revenue Statistics",
        description: "Revenue statistics for creator",
        properties: [
            new OA\Property(
                property: "total_sales",
                type: "integer",
                example: 150
            ),
            new OA\Property(
                property: "total_revenue",
                type: "string",
                example: "7499.50"
            ),
            new OA\Property(
                property: "average_order_value",
                type: "string",
                example: "49.99"
            ),
        ],
        type: "object"
    ),
]
class PurchaseSchema
{
    //
}
