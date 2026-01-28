<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Purchase extends Model
{
    protected $fillable = ["user_id", "product_id", "reference", "status"];

    protected $casts = [
        "id" => "integer",
        "user_id" => "integer",
        "product_id" => "integer",
    ];

    // Status constants
    public const STATUS_PENDING = "pending";
    public const STATUS_COMPLETED = "completed";
    public const STATUS_FAILED = "failed";

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function downloadLogs(): HasMany
    {
        return $this->hasMany(DownloadLog::class);
    }

    /**
     * Check if purchase is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Check if user can download the product
     */
    public function canDownload(): bool
    {
        return $this->isCompleted() && $this->product && $this->product->file;
    }

    /**
     * Get total download count for this purchase
     */
    public function getDownloadCount(): int
    {
        return $this->downloadLogs()->count();
    }
}
