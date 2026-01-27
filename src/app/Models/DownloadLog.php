<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DownloadLog extends Model
{
    public $timestamps = false;

    protected $fillable = ["purchase_id", "ip_address", "downloaded_at"];

    protected $casts = [
        "downloaded_at" => "datetime",
    ];

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }
}
