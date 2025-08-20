<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Event;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class UserController extends Controller
{
    public function dashboard()
    {
        $user = Auth::user();
        
        // Get user's recent events
        $recentEvents = Event::with(['package', 'photos'])
            ->where('user_id', Auth::id())
            ->latest()
            ->limit(5)
            ->get()
            ->map(function ($event) {
                return [
                    'id' => $event->id,
                    'name' => $event->name,
                    'location' => $event->location,
                    'event_date' => $event->event_date->format('Y-m-d'),
                    'formatted_date' => $event->event_date->format('M d, Y'),
                    'status' => $event->status,
                    'photos_count' => $event->photos->count(),
                    'approved_photos_count' => $event->photos->where('status', 'approved')->count(),
                    'pending_photos_count' => $event->photos->where('status', 'pending')->count(),
                    'package_name' => $event->package?->name,
                    'image' => $event->image,
                ];
            });

        // Calculate stats
        $totalEvents = Event::where('user_id', Auth::id())->count();
        $totalPhotos = Event::where('user_id', Auth::id())
            ->withCount('photos')
            ->get()
            ->sum('photos_count');
        $totalApprovedPhotos = Event::where('user_id', Auth::id())
            ->with('photos')
            ->get()
            ->sum(function($event) {
                return $event->photos->where('status', 'approved')->count();
            });
        
        return Inertia::render('User/Dashboard', [
            'showFeatureDiscovery' => $user && !$user->onboarding_completed,
            'recentEvents' => $recentEvents,
            'stats' => [
                'total_events' => $totalEvents,
                'total_photos' => $totalPhotos,
                'approved_photos' => $totalApprovedPhotos,
                'pending_photos' => $totalPhotos - $totalApprovedPhotos,
            ]
        ]);
    }

    public function completeOnboarding()
    {
        /** @var User|null $user */
        $user = Auth::user();
        
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $user->update([
            'onboarding_completed' => true,
            'onboarding_completed_at' => now(),
        ]);

        return response()->json(['success' => true]);
    }
}
