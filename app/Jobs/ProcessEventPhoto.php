<?php

namespace App\Jobs;

use App\Models\EventPhoto;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ProcessEventPhoto implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 300; // 5 minutes
    public $tries = 3;

    protected $eventPhoto;

    public function __construct(EventPhoto $eventPhoto)
    {
        $this->eventPhoto = $eventPhoto;
    }

    public function handle(): void
    {
        try {
            Log::info('Processing photo', ['photo_id' => $this->eventPhoto->id]);

            // HEIC dönüştürme işlemi
            if (preg_match('/\.(heic|heif)$/i', $this->eventPhoto->photo_path)) {
                $convertedPath = $this->convertHeicToJpeg($this->eventPhoto->photo_path);
                if ($convertedPath) {
                    // Eski HEIC dosyasını sil
                    if (Storage::disk('public')->exists($this->eventPhoto->photo_path)) {
                        Storage::disk('public')->delete($this->eventPhoto->photo_path);
                    }
                    
                    // Yeni path'i güncelle
                    $this->eventPhoto->update([
                        'photo_path' => $convertedPath,
                        'mime_type' => 'image/jpeg',
                        'status' => 'processed'
                    ]);
                    
                    Log::info('HEIC converted successfully', [
                        'photo_id' => $this->eventPhoto->id,
                        'new_path' => $convertedPath
                    ]);
                } else {
                    // Dönüştürme başarısız
                    $this->eventPhoto->update(['status' => 'failed']);
                    Log::error('HEIC conversion failed', ['photo_id' => $this->eventPhoto->id]);
                }
            } else {
                // HEIC değilse direkt processed yap
                $this->eventPhoto->update(['status' => 'processed']);
                Log::info('Photo marked as processed', ['photo_id' => $this->eventPhoto->id]);
            }

        } catch (\Exception $e) {
            Log::error('Photo processing failed', [
                'photo_id' => $this->eventPhoto->id,
                'error' => $e->getMessage()
            ]);
            
            $this->eventPhoto->update(['status' => 'failed']);
            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('Photo processing job failed permanently', [
            'photo_id' => $this->eventPhoto->id,
            'error' => $exception->getMessage()
        ]);
        
        $this->eventPhoto->update(['status' => 'failed']);
    }

    protected function convertHeicToJpeg(string $originalPath): ?string
    {
        try {
            $disk = Storage::disk('public');
            if (!$disk->exists($originalPath)) {
                return null;
            }
            
            $data = $disk->get($originalPath);
            $jpegPath = preg_replace('/\.(heic|heif)$/i', '.jpg', $originalPath);
            
            if (class_exists('Imagick')) {
                $img = new \Imagick();
                $img->readImageBlob($data);
                $img->setImageFormat('jpeg');
                $img->setImageCompressionQuality(90);
                $disk->put($jpegPath, $img->getImageBlob());
                $img->clear();
                $img->destroy();
                return $jpegPath;
            }
            
            return null;
        } catch (\Throwable $e) {
            Log::warning('HEIC convert failed in job: ' . $e->getMessage());
            return null;
        }
    }
}