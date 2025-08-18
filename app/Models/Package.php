<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'price',
        'currency',
        'max_uploads',
        'storage_days',
        'upload_days',
        'quality',
        'advanced_customization',
        'features',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'advanced_customization' => 'boolean',
        'is_active' => 'boolean',
        'features' => 'array',
    ];

    public function isUnlimitedUploads(): bool
    {
        return is_null($this->max_uploads);
    }

    public function getFormattedPriceAttribute(): string
    {
        return number_format($this->price, 2) . ' ' . $this->currency;
    }
}
