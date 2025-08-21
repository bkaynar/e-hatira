<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Package;
use Inertia\Inertia;

class OnboardingController extends Controller
{
    public function welcome()
    {
        $user = Auth::user();
        
        // If onboarding is already completed, redirect to dashboard
        if ($user && $user->onboarding_completed) {
            return redirect()->route('dashboard');
        }

        return Inertia::render('Onboarding/Welcome', [
            'user' => $user
        ]);
    }

    public function features()
    {
        $user = Auth::user();
        
        if ($user && $user->onboarding_completed) {
            return redirect()->route('dashboard');
        }

        return Inertia::render('Onboarding/Features');
    }

    public function packages()
    {
        $user = Auth::user();
        
        if ($user && $user->onboarding_completed) {
            return redirect()->route('dashboard');
        }

        $packages = Package::where('is_active', true)->get();

        return Inertia::render('Onboarding/Packages', [
            'packages' => $packages
        ]);
    }

    public function firstEvent()
    {
        $user = Auth::user();
        
        if ($user && $user->onboarding_completed) {
            return redirect()->route('dashboard');
        }

        $packages = Package::where('is_active', true)->get();

        return Inertia::render('Onboarding/FirstEvent', [
            'packages' => $packages
        ]);
    }

    public function complete(Request $request)
    {
        $user = Auth::user();
        
        if (!$user) {
            return redirect()->route('login');
        }

        $user->update([
            'onboarding_completed' => true,
            'onboarding_completed_at' => now(),
            'onboarding_steps' => [
                'welcome' => true,
                'features' => true,
                'packages' => true,
                'first_event' => $request->boolean('created_event', false),
            ]
        ]);

        return redirect()->route('dashboard')->with('success', 'EventPhoto\'ya hoş geldiniz! Kurulum tamamlandı.');
    }

    public function skip()
    {
        $user = Auth::user();
        
        if (!$user) {
            return redirect()->route('login');
        }

        $user->update([
            'onboarding_completed' => true,
            'onboarding_completed_at' => now(),
            'onboarding_steps' => [
                'welcome' => true,
                'features' => false,
                'packages' => false,
                'first_event' => false,
                'skipped' => true,
            ]
        ]);

        return redirect()->route('dashboard')->with('info', 'You can always create your first event later!');
    }

    public function updateStep(Request $request)
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $step = $request->input('step');
        $steps = $user->onboarding_steps ?? [];
        $steps[$step] = true;

        $user->update([
            'onboarding_steps' => $steps
        ]);

        return response()->json(['success' => true]);
    }
}
