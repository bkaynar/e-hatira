<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventPhoto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Inertia\Inertia;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use ZipArchive;
use App\Jobs\ProcessEventPhoto;

class EventPhotoController extends Controller
{
    use AuthorizesRequests;

    public function store(Request $request, Event $event)
    {
        $request->validate([
            'photos' => 'required|array|max:10',
            'photos.*' => 'image|mimes:jpeg,png,jpg,gif,webp,heic,heif|max:5120', // 5MB (HEIC/WEBP dahil)
            'uploader_name' => 'nullable|string|max:255',
            'uploader_email' => 'nullable|email|max:255',
        ]);

        $uploadedPhotos = [];

        foreach ($request->file('photos') as $index => $photo) {
            $path = $this->storeWithCustomName(
                $photo,
                'event-photos/' . $event->id,
                $request->uploader_email
            );

            $finalPath = $path;
            if (preg_match('/\.(heic|heif)$/i', $path)) {
                $converted = $this->convertHeicToJpeg($path);
                if ($converted) {
                    $finalPath = $converted;
                }
            }

            $eventPhoto = EventPhoto::create([
                'event_id' => $event->id,
                'photo_path' => $finalPath,
                'original_name' => $photo->getClientOriginalName(),
                'file_size' => $photo->getSize(),
                'mime_type' => $photo->getMimeType(),
                'order' => $index,
                'uploader_name' => $request->uploader_name,
                'uploader_email' => $request->uploader_email,
                'uploader_ip' => $this->getRealIpAddress($request),
                'status' => 'pending',
            ]);

            $uploadedPhotos[] = $eventPhoto;
        }

        return response()->json([
            'message' => count($uploadedPhotos) . ' fotoğraf başarıyla yüklendi!',
            'photos' => $uploadedPhotos
        ]);
    }

    public function approve(Event $event, $eventPhotoId)
    {
        $eventPhoto = EventPhoto::where('id', $eventPhotoId)
            ->where('event_id', $event->id)
            ->firstOrFail();
            
        $this->authorize('update', $event);

        $eventPhoto->update(['status' => 'approved']);

        return back()->with('success', 'Fotoğraf onaylandı!');
    }

    public function reject(Event $event, $eventPhotoId)
    {
        $eventPhoto = EventPhoto::where('id', $eventPhotoId)
            ->where('event_id', $event->id)
            ->firstOrFail();
            
        $this->authorize('update', $event);

        $eventPhoto->update(['status' => 'rejected']);

        return back()->with('success', 'Fotoğraf reddedildi!');
    }

    public function destroy(Event $event, $eventPhotoId)
    {
        $eventPhoto = EventPhoto::where('id', $eventPhotoId)
            ->where('event_id', $event->id)
            ->firstOrFail();
            
        $this->authorize('update', $event);

        // Storage'dan dosyayı sil
        if (Storage::disk('public')->exists($eventPhoto->photo_path)) {
            Storage::disk('public')->delete($eventPhoto->photo_path);
        }

        $eventPhoto->delete();

        return back()->with('success', 'Fotoğraf silindi!');
    }

    public function bulkDestroy(Request $request, Event $event)
    {
        \Log::info('Bulk destroy called', ['event_id' => $event->id, 'user_id' => auth()->id()]);
        
        $this->authorize('update', $event);

        $request->validate([
            'photo_ids' => 'required|array|min:1',
            'photo_ids.*' => 'required|integer|exists:event_photos,id',
        ]);

        $photos = EventPhoto::whereIn('id', $request->photo_ids)
            ->where('event_id', $event->id)
            ->get();

        if ($photos->isEmpty()) {
            return back()->withErrors(['photos' => 'Silinecek fotoğraf bulunamadı.']);
        }

        $deletedCount = 0;
        foreach ($photos as $photo) {
            // Storage'dan dosyayı sil
            if (Storage::disk('public')->exists($photo->photo_path)) {
                Storage::disk('public')->delete($photo->photo_path);
            }
            
            $photo->delete();
            $deletedCount++;
        }

        return back()->with('success', $deletedCount . ' fotoğraf başarıyla silindi!');
    }

    public function updateOrder(Request $request, Event $event)
    {
        $this->authorize('update', $event);

        $request->validate([
            'photos' => 'required|array',
            'photos.*.id' => 'required|exists:event_photos,id',
            'photos.*.order' => 'required|integer|min:0',
        ]);

        foreach ($request->photos as $photoData) {
            EventPhoto::where('id', $photoData['id'])
                ->where('event_id', $event->id)
                ->update(['order' => $photoData['order']]);
        }

        return response()->json(['message' => 'Fotoğraf sıralaması güncellendi!']);
    }

    public function setCover(Event $event, $eventPhotoId)
    {
        $eventPhoto = EventPhoto::where('id', $eventPhotoId)
            ->where('event_id', $event->id)
            ->firstOrFail();
            
        $this->authorize('update', $event);

        // Önce diğer kapak fotoğraflarını kaldır
        EventPhoto::where('event_id', $event->id)
            ->update(['is_cover' => false]);

        // Bu fotoğrafı kapak yap
        $eventPhoto->update(['is_cover' => true]);

        return back()->with('success', 'Kapak fotoğrafı güncellendi!');
    }

    public function downloadAll(Event $event)
    {
        $this->authorize('view', $event);

        $photos = EventPhoto::where('event_id', $event->id)
            ->where('status', '!=', 'deleted')
            ->get();

        if ($photos->isEmpty()) {
            return back()->with('error', 'Bu etkinlikte indirilebilecek fotoğraf bulunamadı.');
        }

        $zipFileName = $event->name . '_photos_' . now()->format('Y-m-d_H-i-s') . '.zip';
        $zipFilePath = storage_path('app/temp/' . $zipFileName);

        // Temp klasörü yoksa oluştur
        if (!file_exists(storage_path('app/temp'))) {
            mkdir(storage_path('app/temp'), 0755, true);
        }

        $zip = new ZipArchive;
        if ($zip->open($zipFilePath, ZipArchive::CREATE) !== TRUE) {
            return back()->with('error', 'ZIP dosyası oluşturulamadı.');
        }

        $addedFiles = 0;
        foreach ($photos as $photo) {
            $filePath = storage_path('app/public/' . $photo->photo_path);
            
            // Debug log ekle
            Log::info('Processing photo for zip', [
                'photo_id' => $photo->id,
                'photo_path' => $photo->photo_path,
                'full_path' => $filePath,
                'file_exists' => file_exists($filePath),
                'original_name' => $photo->original_name
            ]);
            
            if (file_exists($filePath)) {
                // Dosya adını düzenle (orijinal ad + ID)
                $extension = pathinfo($photo->original_name, PATHINFO_EXTENSION);
                $nameWithoutExt = pathinfo($photo->original_name, PATHINFO_FILENAME);
                
                $fileName = $nameWithoutExt . '_' . $photo->id . '.' . $extension;
                
                $zip->addFile($filePath, $fileName);
                $addedFiles++;
                Log::info('Added file to zip', ['file_name' => $fileName]);
            } else {
                Log::warning('File not found for photo', [
                    'photo_id' => $photo->id,
                    'expected_path' => $filePath
                ]);
            }
        }
        
        Log::info('Zip creation summary', [
            'total_photos' => $photos->count(),
            'added_files' => $addedFiles
        ]);

        $zip->close();

        if ($addedFiles === 0) {
            unlink($zipFilePath);
            return back()->with('error', 'İndirilebilecek dosya bulunamadı.');
        }

        return response()->download($zipFilePath)->deleteFileAfterSend(true);
    }

    public function publicUploadPage($eventSlug)
    {
        $event = Event::with(['package', 'photos' => function ($query) {
            $query->approved()->orderedByPosition();
        }])
            ->where('slug', $eventSlug)
            ->where('status', 'published')
            ->firstOrFail();

        return Inertia::render('Public/EventUpload', [
            'event' => [
                'id' => $event->id,
                'name' => $event->name,
                'slug' => $event->slug,
                'description' => $event->description,
                'location' => $event->location,
                'event_date' => $event->event_date->locale('tr')->isoFormat('D MMMM YYYY'),
                'image' => $event->image,
                'package_name' => $event->package?->name,
                'photos_count' => $event->photos->count(),
            ]
        ]);
    }

    public function publicUpload(Request $request, $eventSlug)
    {
        // Memory limit artır
        ini_set('memory_limit', '512M');
        
        $event = Event::where('slug', $eventSlug)
            ->where('status', 'published')
            ->firstOrFail();

        // Etkinlik başına toplam dosya limiti kontrolü
        $currentCount = EventPhoto::where('event_id', $event->id)->count();
        $newFileCount = count($request->file('files', []));
        
        if ($currentCount + $newFileCount > 1000) {
            return back()->withErrors([
                'files' => 'Bu etkinliğe daha fazla dosya yüklenemez. (Maksimum: 1000 dosya)'
            ]);
        }

        // FRONTEND Limitleri ile uyum:
        // Görsel max ~15MB, Video max ~250MB
        // Laravel 'max' KB cinsinden: 250MB ~= 256000KB
        $request->validate([
            'files' => 'required|array|max:5',
            // Geniş mime listesi + yüksek üst sınır (video sınırına göre). Detaylı ayrımı aşağıda manuel yapıyoruz.
            'files.*' => 'file|mimetypes:image/jpeg,image/png,image/jpg,image/gif,image/webp,image/heic,image/heif,video/mp4,video/quicktime,video/mov,video/avi,video/x-msvideo,video/wmv,video/x-ms-wmv|max:256000',
            'uploader_name' => 'required|string|max:255',
            'uploader_email' => 'required|email|max:255',
        ]);

        // İnce taneli boyut kontrolü (görsel 15MB, video 250MB) ve özel mesajlar
        $imageMaxBytes = 15 * 1024 * 1024;  // 15MB
        $videoMaxBytes = 250 * 1024 * 1024; // 250MB
        $perFileErrors = [];

        foreach ($request->file('files') as $idx => $file) {
            $isImage = str_starts_with($file->getMimeType(), 'image/');
            $isVideo = str_starts_with($file->getMimeType(), 'video/');
            if ($isImage && $file->getSize() > $imageMaxBytes) {
                $perFileErrors["files.$idx"] = 'Görsel 15MB sınırını aşıyor.';
            } elseif ($isVideo && $file->getSize() > $videoMaxBytes) {
                $perFileErrors["files.$idx"] = 'Video 250MB sınırını aşıyor.';
            }
        }

        if ($perFileErrors) {
            return back()->withErrors($perFileErrors)->withInput();
        }

        $uploadedFiles = [];
        $maxOrder = EventPhoto::where('event_id', $event->id)->max('order') ?? 0;

        foreach ($request->file('files') as $index => $file) {
            $folder = str_starts_with($file->getMimeType(), 'video/') ? 'event-videos' : 'event-photos';
            $path = $this->storeWithCustomName(
                $file,
                $folder . '/' . $event->id,
                $request->uploader_email,
                $index
            );

            // HEIC dönüştürmeyi job'a bırak, şimdilik orijinal path'i kaydet
            $finalPath = $path;

            $eventPhoto = EventPhoto::create([
                'event_id' => $event->id,
                'photo_path' => $finalPath,
                'original_name' => $file->getClientOriginalName(),
                'file_size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'order' => $maxOrder + $index + 1,
                'uploader_name' => $request->uploader_name,
                'uploader_email' => $request->uploader_email,
                'uploader_ip' => $this->getRealIpAddress($request),
                'status' => 'processing',
            ]);

            // Background job'a gönder
            ProcessEventPhoto::dispatch($eventPhoto);

            $uploadedFiles[] = $eventPhoto;
        }

        $fileCount = count($uploadedFiles);
        $fileType = str_contains($uploadedFiles[0]->mime_type, 'video') ? 'dosya' : 'fotoğraf';

        return back()->with('success', $fileCount . ' ' . $fileType . ' başarıyla yüklendi! Onay bekliyor.');
    }

    /**
     * Dosyayı email + tarih formatıyla kaydeder.
     * Örnek: user_example_com_20250821_153045_0.jpg
     */
    protected function storeWithCustomName(UploadedFile $file, string $directory, ?string $email = null, int $index = 0): string
    {
        $emailBase = $email ? preg_replace('/[^a-z0-9]+/i', '_', strtolower($email)) : 'anon';
        $timestamp = now()->format('Ymd_His');
        $ext = strtolower($file->getClientOriginalExtension());
        if (!$ext) {
            // MIME'dan tahmin (özellikle HEIC)
            $mime = $file->getMimeType();
            if (str_contains($mime, 'heic')) {
                $ext = 'heic';
            } elseif (str_contains($mime, 'heif')) {
                $ext = 'heif';
            } elseif (str_contains($mime, 'jpeg')) {
                $ext = 'jpg';
            } elseif (str_contains($mime, 'png')) {
                $ext = 'png';
            } elseif (str_contains($mime, 'gif')) {
                $ext = 'gif';
            } elseif (str_contains($mime, 'webp')) {
                $ext = 'webp';
            } elseif (str_contains($mime, 'mp4')) {
                $ext = 'mp4';
            } elseif (str_contains($mime, 'quicktime') || str_contains($mime, 'mov')) {
                $ext = 'mov';
            } elseif (str_contains($mime, 'avi')) {
                $ext = 'avi';
            } elseif (str_contains($mime, 'wmv')) {
                $ext = 'wmv';
            } else {
                $ext = 'dat';
            }
        }

        $base = $emailBase . '_' . $timestamp . '_' . $index;
        $candidate = $base . '.' . $ext;
        $i = 1;
        while (Storage::disk('public')->exists(trim($directory, '/') . '/' . $candidate)) {
            $candidate = $base . '_' . $i++ . '.' . $ext;
        }

        Storage::disk('public')->makeDirectory($directory);
        Storage::disk('public')->putFileAs($directory, $file, $candidate);
        return $directory . '/' . $candidate;
    }
    /**
     * HEIC/HEIF -> JPEG dönüşümü. Başarılıysa yeni yol döner.
     */
    protected function convertHeicToJpeg(string $originalPath): ?string
    {
        try {
            $disk = Storage::disk('public');
            if (!$disk->exists($originalPath)) {
                return null;
            }
            $data = $disk->get($originalPath);
            $jpegPath = preg_replace('/\.(heic|heif)$/i', '.jpg', $originalPath);
            /** @noinspection PhpUndefinedClassInspection */
            if (class_exists('Imagick')) {
                $class = 'Imagick';
                $img = new $class();
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
            Log::warning('HEIC convert failed: ' . $e->getMessage());
            return null;
        }
    }

    protected function getRealIpAddress(Request $request): string
    {
        // Proxy başlıklarını kontrol et
        $ipKeys = [
            'HTTP_CF_CONNECTING_IP',     // Cloudflare
            'HTTP_CLIENT_IP',            // Shared internet
            'HTTP_X_FORWARDED_FOR',      // Load balancer/proxy
            'HTTP_X_FORWARDED',          // Proxy
            'HTTP_X_CLUSTER_CLIENT_IP',  // Cluster balancer
            'HTTP_FORWARDED_FOR',        // Proxy
            'HTTP_FORWARDED',            // Proxy
            'REMOTE_ADDR'                // Standard
        ];

        foreach ($ipKeys as $key) {
            if (array_key_exists($key, $_SERVER) && !empty($_SERVER[$key])) {
                $ips = explode(',', $_SERVER[$key]);
                $ip = trim($ips[0]);
                
                // Geçerli IP adresi kontrolü
                if (filter_var($ip, FILTER_VALIDATE_IP, 
                    FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }

        // Fallback olarak Laravel'in ip() metodunu kullan
        return $request->ip();
    }
}
