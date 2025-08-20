<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class UserController extends Controller
{
    public function dashboard()
    {
        $user = Auth::user();
        
        return Inertia::render('User/Dashboard', [
            'showFeatureDiscovery' => $user && !$user->onboarding_completed
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
