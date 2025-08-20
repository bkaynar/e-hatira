<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventPhoto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Inertia\Inertia;

class EventPhotoController extends Controller
{
    use AuthorizesRequests;

    public function store(Request $request, Event $event)
    {
        $request->validate([
            'photos' => 'required|array|max:10',
            'photos.*' => 'image|mimes:jpeg,png,jpg,gif|max:5120', // 5MB
            'uploader_name' => 'nullable|string|max:255',
            'uploader_email' => 'nullable|email|max:255',
        ]);

        $uploadedPhotos = [];

        foreach ($request->file('photos') as $index => $photo) {
            $path = $photo->store('event-photos/' . $event->id, 'public');
            
            $eventPhoto = EventPhoto::create([
                'event_id' => $event->id,
                'photo_path' => $path,
                'original_name' => $photo->getClientOriginalName(),
                'file_size' => $photo->getSize(),
                'mime_type' => $photo->getMimeType(),
                'order' => $index,
                'uploader_name' => $request->uploader_name,
                'uploader_email' => $request->uploader_email,
                'uploader_ip' => $request->ip(),
                'status' => 'pending',
            ]);

            $uploadedPhotos[] = $eventPhoto;
        }

        return response()->json([
            'message' => count($uploadedPhotos) . ' fotoğraf başarıyla yüklendi!',
            'photos' => $uploadedPhotos
        ]);
    }

    public function approve(EventPhoto $eventPhoto)
    {
        $this->authorize('update', $eventPhoto->event);
        
        $eventPhoto->update(['status' => 'approved']);

        return back()->with('success', 'Fotoğraf onaylandı!');
    }

    public function reject(EventPhoto $eventPhoto)
    {
        $this->authorize('update', $eventPhoto->event);
        
        $eventPhoto->update(['status' => 'rejected']);

        return back()->with('success', 'Fotoğraf reddedildi!');
    }

    public function destroy(EventPhoto $eventPhoto)
    {
        $this->authorize('update', $eventPhoto->event);
        
        // Storage'dan dosyayı sil
        if (Storage::disk('public')->exists($eventPhoto->photo_path)) {
            Storage::disk('public')->delete($eventPhoto->photo_path);
        }

        $eventPhoto->delete();

        return back()->with('success', 'Fotoğraf silindi!');
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

    public function setCover(EventPhoto $eventPhoto)
    {
        $this->authorize('update', $eventPhoto->event);

        // Önce diğer kapak fotoğraflarını kaldır
        EventPhoto::where('event_id', $eventPhoto->event_id)
            ->update(['is_cover' => false]);

        // Bu fotoğrafı kapak yap
        $eventPhoto->update(['is_cover' => true]);

        return back()->with('success', 'Kapak fotoğrafı güncellendi!');
    }

    public function publicUploadPage($eventSlug)
    {
        $event = Event::with(['package', 'photos' => function($query) {
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
                'event_date' => $event->event_date->format('M d, Y'),
                'image' => $event->image,
                'package_name' => $event->package?->name,
                'photos_count' => $event->photos->count(),
            ]
        ]);
    }

    public function publicUpload(Request $request, $eventSlug)
    {
        $event = Event::where('slug', $eventSlug)
            ->where('status', 'published')
            ->firstOrFail();

        $request->validate([
            'files' => 'required|array|max:5', // Reduce to 5 files at once
            'files.*' => 'file|mimes:jpeg,png,jpg,gif,mp4,mov,avi,wmv|max:2048', // 2MB max per file
            'uploader_name' => 'required|string|max:255',
            'uploader_email' => 'required|email|max:255',
        ]);

        $uploadedFiles = [];
        $maxOrder = EventPhoto::where('event_id', $event->id)->max('order') ?? 0;

        foreach ($request->file('files') as $index => $file) {
            // Determine storage path based on file type
            $folder = str_starts_with($file->getMimeType(), 'video/') ? 'event-videos' : 'event-photos';
            $path = $file->store($folder . '/' . $event->id, 'public');
            
            $eventPhoto = EventPhoto::create([
                'event_id' => $event->id,
                'photo_path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'file_size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'order' => $maxOrder + $index + 1,
                'uploader_name' => $request->uploader_name,
                'uploader_email' => $request->uploader_email,
                'uploader_ip' => $request->ip(),
                'status' => 'pending',
            ]);

            $uploadedFiles[] = $eventPhoto;
        }

        $fileCount = count($uploadedFiles);
        $fileType = str_contains($uploadedFiles[0]->mime_type, 'video') ? 'dosya' : 'fotoğraf';
        
        return back()->with('success', $fileCount . ' ' . $fileType . ' başarıyla yüklendi! Onay bekliyor.');
    }
}
