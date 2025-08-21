<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Log;

class EventPhoto extends Model
{
    protected $fillable = [
        'event_id',
        'photo_path',
        'original_name',
        'file_size',
        'mime_type',
        'order',
        'is_cover',
        'uploader_name',
        'uploader_email',
        'uploader_ip',
        'status',
    ];

    protected $casts = [
        'is_cover' => 'boolean',
        'file_size' => 'integer',
        'order' => 'integer',
    ];

    protected $appends = ['photo_url'];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function getPhotoUrlAttribute(): string
    {
        if (empty($this->photo_path)) {
            Log::warning("Empty photo_path for photo id: {$this->id}");
            return "";
        }

        // public/storage yolunu tam olarak oluşturur
        $url = asset('storage/' . $this->photo_path);

        // Hata ayıklama amaçlı
        Log::info("Photo URL generated for ID {$this->id}: {$url}");

        return $url;
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeOrderedByPosition($query)
    {
        return $query->orderBy('order', 'asc');
    }
}
