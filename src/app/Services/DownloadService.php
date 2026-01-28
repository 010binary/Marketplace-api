<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DownloadService
{
    public function __construct(
        private readonly PurchaseService $purchaseService
    ) {}

    /**
     * Generate a temporary signed URL for product download
     *
     * @param User $user
     * @param int $productId
     * @param int $expirationMinutes Default: 60 minutes
     * @return string Signed URL
     */
    public function generateDownloadUrl(
        User $user,
        int $productId,
        int $expirationMinutes = 60
    ): string {
        // Verify user has purchased the product
        $this->purchaseService->verifyDownloadAccess($user, $productId);

        // Generate signed URL that expires
        return URL::temporarySignedRoute(
            'download.file',
            now()->addMinutes($expirationMinutes),
            [
                'productId' => $productId,
                'userId' => $user->id,
            ]
        );
    }

    /**
     * Download a product file
     *
     * @param User $user
     * @param int $productId
     * @param string $ipAddress
     * @return StreamedResponse
     */
    public function downloadFile(User $user, int $productId, string $ipAddress): StreamedResponse
    {
        // Verify download access and get purchase
        $purchase = $this->purchaseService->verifyDownloadAccess($user, $productId);

        $productFile = $purchase->product->file;

        if (!$productFile) {
            throw new \DomainException('Product file not found.');
        }

        // Verify file exists in storage
        if (!Storage::disk($productFile->disk)->exists($productFile->path)) {
            throw new \DomainException('Product file is no longer available.');
        }

        // Log the download
        $this->purchaseService->logDownload($purchase, $ipAddress);

        // Stream the file download
        return Storage::disk($productFile->disk)->download(
            $productFile->path,
            $productFile->original_filename,
            [
                'Content-Type' => $productFile->mime_type,
                'Content-Disposition' => 'attachment; filename="' . $productFile->original_filename . '"',
            ]
        );
    }

    /**
     * Get download information without actually downloading
     *
     * @param User $user
     * @param int $productId
     * @return array
     */
    public function getDownloadInfo(User $user, int $productId): array
    {
        $purchase = $this->purchaseService->verifyDownloadAccess($user, $productId);
        $productFile = $purchase->product->file;

        if (!$productFile) {
            throw new \DomainException('Product file not found.');
        }

        $downloadStats = $this->purchaseService->getDownloadStats($purchase);

        return [
            'product_id' => $purchase->product_id,
            'product_title' => $purchase->product->title,
            'file_name' => $productFile->original_filename,
            'file_size' => $productFile->size,
            'file_size_formatted' => $this->formatBytes($productFile->size),
            'mime_type' => $productFile->mime_type,
            'purchased_at' => $purchase->created_at,
            'download_stats' => $downloadStats,
        ];
    }

    /**
     * Validate if user can generate download URL
     *
     * @param User $user
     * @param int $productId
     * @return bool
     */
    public function canGenerateDownloadUrl(User $user, int $productId): bool
    {
        try {
            $this->purchaseService->verifyDownloadAccess($user, $productId);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get product for download with verification
     *
     * @param User $user
     * @param int $productId
     * @return Product
     */
    public function getProductForDownload(User $user, int $productId): Product
    {
        $purchase = $this->purchaseService->verifyDownloadAccess($user, $productId);
        return $purchase->product;
    }

    /**
     * Format bytes to human readable format
     *
     * @param int $bytes
     * @param int $precision
     * @return string
     */
    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }

    /**
     * Generate download URL with custom expiration
     *
     * @param User $user
     * @param int $productId
     * @param \DateTime $expiresAt
     * @return string
     */
    public function generateDownloadUrlWithExpiration(
        User $user,
        int $productId,
        \DateTime $expiresAt
    ): string {
        // Verify user has purchased the product
        $this->purchaseService->verifyDownloadAccess($user, $productId);

        // Generate signed URL with specific expiration
        return URL::temporarySignedRoute(
            'download.file',
            $expiresAt,
            [
                'productId' => $productId,
                'userId' => $user->id,
            ]
        );
    }

    /**
     * Validate download access without throwing exceptions
     *
     * @param User $user
     * @param int $productId
     * @return array ['can_download' => bool, 'reason' => string|null]
     */
    public function validateDownloadAccess(User $user, int $productId): array
    {
        try {
            $purchase = $this->purchaseService->verifyDownloadAccess($user, $productId);

            if (!$purchase->product->file) {
                return [
                    'can_download' => false,
                    'reason' => 'Product file is not available.',
                ];
            }

            return [
                'can_download' => true,
                'reason' => null,
            ];
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            return [
                'can_download' => false,
                'reason' => $e->getMessage(),
            ];
        } catch (\DomainException $e) {
            return [
                'can_download' => false,
                'reason' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get file stream for download
     *
     * @param User $user
     * @param int $productId
     * @return resource
     */
    public function getFileStream(User $user, int $productId)
    {
        $purchase = $this->purchaseService->verifyDownloadAccess($user, $productId);
        $productFile = $purchase->product->file;

        if (!$productFile) {
            throw new \DomainException('Product file not found.');
        }

        if (!Storage::disk($productFile->disk)->exists($productFile->path)) {
            throw new \DomainException('Product file is no longer available.');
        }

        return Storage::disk($productFile->disk)->readStream($productFile->path);
    }
}
