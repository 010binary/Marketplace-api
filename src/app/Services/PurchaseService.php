<?php

namespace App\Services;

use App\Models\DownloadLog;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class PurchaseService
{
    /**
     * Process checkout for a product
     */
    public function checkout(User $user, Product $product): Purchase
    {
        // Verify product is active
        if (!$product->is_active) {
            throw new \DomainException(
                'This product is not available for purchase.'
            );
        }

        // Check if user already purchased this product
        $existingPurchase = Purchase::where('user_id', $user->id)
            ->where('product_id', $product->id)
            ->where('status', Purchase::STATUS_COMPLETED)
            ->first();

        if ($existingPurchase) {
            throw new \DomainException(
                'You have already purchased this product.'
            );
        }

        // Simulate payment processing
        $reference = $this->generateReference();

        // In a real application, this would integrate with a payment gateway
        // For now, we simulate successful payment
        $purchase = Purchase::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'reference' => $reference,
            'status' => Purchase::STATUS_COMPLETED, // Simulated success
        ]);

        // Load relationships
        return $purchase->load(['product.creator', 'product.category', 'product.image', 'product.file']);
    }

    /**
     * Get user's library (purchased products)
     */
    public function getUserLibrary(User $user, int $perPage = 15): LengthAwarePaginator
    {
        return Purchase::where('user_id', $user->id)
            ->where('status', Purchase::STATUS_COMPLETED)
            ->with([
                'product.creator',
                'product.category',
                'product.image',
                'product.file',
            ])
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Get a specific purchase for the user
     */
    public function getUserPurchase(User $user, int $purchaseId): Purchase
    {
        $purchase = Purchase::with([
            'product.creator',
            'product.category',
            'product.image',
            'product.file',
            'downloadLogs',
        ])->findOrFail($purchaseId);

        // Verify ownership
        if ($purchase->user_id !== $user->id) {
            throw new \Symfony\Component\HttpKernel\Exception\HttpException(
                403,
                'You do not have access to this purchase.'
            );
        }

        return $purchase;
    }

    /**
     * Verify user can download a product
     */
    public function verifyDownloadAccess(User $user, int $productId): Purchase
    {
        $purchase = Purchase::where('user_id', $user->id)
            ->where('product_id', $productId)
            ->where('status', Purchase::STATUS_COMPLETED)
            ->with(['product.file'])
            ->first();

        if (!$purchase) {
            throw new \Symfony\Component\HttpKernel\Exception\HttpException(
                403,
                'You have not purchased this product.'
            );
        }

        if (!$purchase->product || !$purchase->product->file) {
            throw new \DomainException(
                'Product file is not available.'
            );
        }

        return $purchase;
    }

    /**
     * Log a download
     */
    public function logDownload(Purchase $purchase, string $ipAddress): DownloadLog
    {
        return DownloadLog::create([
            'purchase_id' => $purchase->id,
            'ip_address' => $ipAddress,
            'downloaded_at' => now(),
        ]);
    }

    /**
     * Get download statistics for a purchase
     */
    public function getDownloadStats(Purchase $purchase): array
    {
        $logs = $purchase->downloadLogs()
            ->orderBy('downloaded_at', 'desc')
            ->get();

        return [
            'total_downloads' => $logs->count(),
            'first_download' => $logs->last()?->downloaded_at,
            'last_download' => $logs->first()?->downloaded_at,
            'recent_downloads' => $logs->take(5)->map(function ($log) {
                return [
                    'downloaded_at' => $log->downloaded_at,
                    'ip_address' => $log->ip_address,
                ];
            }),
        ];
    }

    /**
     * Check if product was purchased by user
     */
    public function hasPurchased(User $user, Product $product): bool
    {
        return Purchase::where('user_id', $user->id)
            ->where('product_id', $product->id)
            ->where('status', Purchase::STATUS_COMPLETED)
            ->exists();
    }

    /**
     * Get purchase by reference
     */
    public function findByReference(string $reference): ?Purchase
    {
        return Purchase::where('reference', $reference)
            ->with(['product', 'user'])
            ->first();
    }

    /**
     * Get creator's sales (products purchased from this creator)
     */
    public function getCreatorSales(User $creator, int $perPage = 15): LengthAwarePaginator
    {
        return Purchase::whereHas('product', function ($query) use ($creator) {
            $query->where('creator_id', $creator->id);
        })
            ->where('status', Purchase::STATUS_COMPLETED)
            ->with(['product', 'user'])
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Get creator's revenue statistics
     */
    public function getCreatorRevenue(User $creator): array
    {
        $sales = Purchase::whereHas('product', function ($query) use ($creator) {
            $query->where('creator_id', $creator->id);
        })
            ->where('status', Purchase::STATUS_COMPLETED)
            ->with('product')
            ->get();

        $totalRevenue = $sales->sum(function ($purchase) {
            return $purchase->product->price ?? 0;
        });

        $totalSales = $sales->count();

        return [
            'total_sales' => $totalSales,
            'total_revenue' => number_format($totalRevenue, 2, '.', ''),
            'average_order_value' => $totalSales > 0
                ? number_format($totalRevenue / $totalSales, 2, '.', '')
                : '0.00',
        ];
    }

    /**
     * Generate unique purchase reference
     */
    private function generateReference(): string
    {
        do {
            $reference = 'PUR-' . strtoupper(Str::random(12));
        } while (Purchase::where('reference', $reference)->exists());

        return $reference;
    }
}
