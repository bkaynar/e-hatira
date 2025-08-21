<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Package;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PackageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $packages = Package::latest()->paginate(10);
        return Inertia::render('Admin/Packages/Index', [
            'packages' => $packages
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Admin/Packages/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|unique:packages,slug',
            'price' => 'required|numeric|min:0',
            'currency' => 'required|string|size:3',
            'max_uploads' => 'nullable|integer|min:1',
            'storage_days' => 'required|integer|min:1',
            'upload_days' => 'required|integer|min:1',
            'quality' => 'required|in:normal,high',
            'advanced_customization' => 'boolean',
            'features' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        Package::create($validated);

        return redirect()->route('admin.packages.index')
            ->with('success', 'Paket başarıyla oluşturuldu.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Package $package)
    {
        return Inertia::render('Admin/Packages/Show', [
            'package' => $package
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Package $package)
    {
        return Inertia::render('Admin/Packages/Edit', [
            'package' => $package
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Package $package)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|unique:packages,slug,' . $package->id,
            'price' => 'required|numeric|min:0',
            'currency' => 'required|string|size:3',
            'max_uploads' => 'nullable|integer|min:1',
            'storage_days' => 'required|integer|min:1',
            'upload_days' => 'required|integer|min:1',
            'quality' => 'required|in:normal,high',
            'advanced_customization' => 'boolean',
            'features' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        $package->update($validated);

        return redirect()->route('admin.packages.index')
            ->with('success', 'Paket başarıyla güncellendi.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Package $package)
    {
        $package->delete();

        return redirect()->route('admin.packages.index')
            ->with('success', 'Paket başarıyla silindi.');
    }
}
