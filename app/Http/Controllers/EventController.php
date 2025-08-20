<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Package;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Inertia\Inertia;

class EventController extends Controller
{
    use AuthorizesRequests;
    public function index()
    {
        $events = Event::with(['user', 'package', 'photos'])
            ->where('user_id', Auth::id())
            ->latest()
            ->paginate(10);

        return Inertia::render('User/Events/Index', [
            'events' => $events
        ]);
    }

    public function create()
    {
        $packages = Package::where('is_active', true)->get();
        
        return Inertia::render('User/Events/Create', [
            'packages' => $packages
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'location' => 'required|string|max:255',
            'event_date' => 'required|date|after:today',
            'event_time' => 'nullable|date_format:H:i',
            'package_id' => 'required|exists:packages,id',
            'image' => 'nullable|image|max:2048',
        ]);

        $validated['user_id'] = Auth::id();
        $validated['status'] = 'draft';

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('events', 'public');
        }

        $event = Event::create($validated);

        return redirect()->route('events.show', $event)->with('success', 'Event başarıyla oluşturuldu!');
    }

    public function show(Event $event)
    {
        $this->authorize('view', $event);
        
        $event->load(['user', 'package', 'photos' => function ($query) {
            $query->approved()->orderedByPosition();
        }]);

        return Inertia::render('User/Events/Show', [
            'event' => $event
        ]);
    }

    public function edit(Event $event)
    {
        $this->authorize('update', $event);
        
        $packages = Package::where('is_active', true)->get();

        return Inertia::render('User/Events/Edit', [
            'event' => $event,
            'packages' => $packages
        ]);
    }

    public function update(Request $request, Event $event)
    {
        $this->authorize('update', $event);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'location' => 'required|string|max:255',
            'event_date' => 'required|date',
            'event_time' => 'nullable|date_format:H:i',
            'package_id' => 'required|exists:packages,id',
            'image' => 'nullable|image|max:2048',
            'status' => 'required|in:draft,published,cancelled',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('events', 'public');
        }

        $event->update($validated);

        return redirect()->route('events.show', $event)->with('success', 'Event başarıyla güncellendi!');
    }

    public function destroy(Event $event)
    {
        $this->authorize('delete', $event);
        
        $event->delete();

        return redirect()->route('events.index')->with('success', 'Event başarıyla silindi!');
    }
}
