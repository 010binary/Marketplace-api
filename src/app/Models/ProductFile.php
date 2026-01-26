<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductFile extends Model
{
    protected $fillable = [
        'product_id',
        'disk',
        'path',
        'original_filename',
        'mime_type',
        'size',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
