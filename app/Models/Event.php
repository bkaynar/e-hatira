<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Event extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'location',
        'event_date',
        'event_time',
        'image',
        'status',
        'user_id',
        'package_id',
        'is_active',
    ];

    protected $casts = [
        'event_date' => 'date',
        'event_time' => 'datetime:H:i',
        'is_active' => 'boolean',
    ];

    protected $appends = ['event_date_formatted'];

    public function getEventDateFormattedAttribute()
    {
        return $this->event_date ? $this->event_date->format('Y-m-d') : null;
    }

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($event) {
            if (empty($event->slug)) {
                // Generate unique random slug with date prefix
                do {
                    $datePrefix = now()->format('ymd');
                    $randomSuffix = Str::random(8);
                    $event->slug = $datePrefix . '-' . $randomSuffix;
                } while (self::where('slug', $event->slug)->exists());
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public function photos(): HasMany
    {
        return $this->hasMany(EventPhoto::class);
    }

    public function approvedPhotos(): HasMany
    {
        return $this->hasMany(EventPhoto::class)->where('status', 'approved');
    }

    public function pendingPhotos(): HasMany
    {
        return $this->hasMany(EventPhoto::class)->where('status', 'pending');
    }

    // Upload URL getter
    public function getUploadUrlAttribute(): string
    {
        return route('events.public.upload.page', $this->slug);
    }

    // QR Code getter
    public function getQrCodeAttribute(): string
    {
        return \SimpleSoftwareIO\QrCode\Facades\QrCode::size(200)
            ->format('svg')
            ->generate($this->upload_url);
    }
}
