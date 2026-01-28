<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\PurchaseService;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;

#[OA\Tag(name: "Purchases", description: "Purchase and library management endpoints")]
class PurchaseController extends Controller
{
    public function __construct(
        private readonly PurchaseService $purchaseService,
    ) {}

    #[
        OA\Post(
            path: "/api/v1/checkout/{product}",
            summary: "Checkout a product",
            description: "Purchase a product (simulated payment). Customer only. Cannot purchase same product twice.",
            security: [["sanctum" => []]],
            tags: ["Purchases"],
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
                    response: 201,
                    description: "Product purchased successfully",
                    content: new OA\JsonContent(
                        ref: "#/components/schemas/CheckoutResponse",
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
                    description: "Forbidden - Customer role required",
                    content: new OA\JsonContent(
                        ref: "#/components/schemas/Forbidden",
                    ),
                ),
                new OA\Response(
                    response: 400,
                    description: "Business logic error",
                    content: new OA\JsonContent(
                        properties: [
                            new OA\Property(
                                property: "message",
                                type: "string",
                                example: "You have already purchased this product.",
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
    public function checkout(Request $request, Product $product)
    {
        try {
            $purchase = $this->purchaseService->checkout(
                $request->user(),
                $product
            );

            return response()->json(
                [
                    'message' => 'Product purchased successfully',
                    'purchase' => $purchase,
                ],
                Response::HTTP_CREATED
            );
        } catch (\DomainException $e) {
            return response()->json(
                ['message' => $e->getMessage()],
                Response::HTTP_BAD_REQUEST
            );
        }
    }

    #[
        OA\Get(
            path: "/api/v1/library",
            summary: "Get user's library",
            description: "Retrieve a paginated list of purchased products (library). Customer only.",
            security: [["sanctum" => []]],
            tags: ["Purchases"],
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
                        ref: "#/components/schemas/PurchasePaginated",
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
                    description: "Forbidden - Customer role required",
                    content: new OA\JsonContent(
                        ref: "#/components/schemas/Forbidden",
                    ),
                ),
            ],
        ),
    ]
    public function library(Request $request)
    {
        $perPage = (int) $request->query("per_page", 15);

        return response()->json(
            $this->purchaseService->getUserLibrary($request->user(), $perPage)
        );
    }

    #[
        OA\Get(
            path: "/api/v1/library/{purchase}",
            summary: "Get specific purchase details",
            description: "Retrieve details of a specific purchase including download statistics. Customer only.",
            security: [["sanctum" => []]],
            tags: ["Purchases"],
            parameters: [
                new OA\Parameter(
                    name: "purchase",
                    in: "path",
                    description: "Purchase ID",
                    required: true,
                    schema: new OA\Schema(type: "integer", example: 1),
                ),
            ],
            responses: [
                new OA\Response(
                    response: 200,
                    description: "Successful operation",
                    content: new OA\JsonContent(
                        ref: "#/components/schemas/Purchase",
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
                    description: "Forbidden - Not your purchase or Customer role required",
                    content: new OA\JsonContent(
                        properties: [
                            new OA\Property(
                                property: "message",
                                type: "string",
                                example: "You do not have access to this purchase.",
                            ),
                        ],
                    ),
                ),
                new OA\Response(
                    response: 404,
                    description: "Purchase not found",
                    content: new OA\JsonContent(
                        ref: "#/components/schemas/NotFound",
                    ),
                ),
            ],
        ),
    ]
    public function show(Request $request, int $purchaseId)
    {
        try {
            $purchase = $this->purchaseService->getUserPurchase(
                $request->user(),
                $purchaseId
            );

            return response()->json($purchase);
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            return response()->json(
                ['message' => $e->getMessage()],
                $e->getStatusCode()
            );
        }
    }

    #[
        OA\Get(
            path: "/api/v1/sales",
            summary: "Get creator's sales",
            description: "Retrieve a paginated list of sales for products created by the authenticated creator. Creator only.",
            security: [["sanctum" => []]],
            tags: ["Purchases"],
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
                        ref: "#/components/schemas/PurchasePaginated",
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
    public function sales(Request $request)
    {
        $perPage = (int) $request->query("per_page", 15);

        return response()->json(
            $this->purchaseService->getCreatorSales($request->user(), $perPage)
        );
    }

    #[
        OA\Get(
            path: "/api/v1/revenue",
            summary: "Get creator's revenue statistics",
            description: "Retrieve revenue statistics for products created by the authenticated creator. Creator only.",
            security: [["sanctum" => []]],
            tags: ["Purchases"],
            responses: [
                new OA\Response(
                    response: 200,
                    description: "Successful operation",
                    content: new OA\JsonContent(
                        ref: "#/components/schemas/CreatorRevenue",
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
    public function revenue(Request $request)
    {
        return response()->json(
            $this->purchaseService->getCreatorRevenue($request->user())
        );
    }
}
