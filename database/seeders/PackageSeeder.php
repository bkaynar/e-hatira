<?php

namespace Database\Seeders;

use App\Models\Package;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PackageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $packages = [
            [
                'name' => 'Free',
                'slug' => 'free',
                'price' => 0.00,
                'currency' => 'TRY',
                'max_uploads' => 10,
                'storage_days' => 30,
                'upload_days' => 7,
                'quality' => 'normal',
                'advanced_customization' => false,
                'features' => [
                    'Basic photo upload',
                    'Standard quality',
                    '30 days storage',
                ],
                'is_active' => true,
            ],
            [
                'name' => 'Plus',
                'slug' => 'plus',
                'price' => 99.99,
                'currency' => 'TRY',
                'max_uploads' => 100,
                'storage_days' => 90,
                'upload_days' => 30,
                'quality' => 'high',
                'advanced_customization' => false,
                'features' => [
                    'High quality photos',
                    'Extended storage',
                    'Priority support',
                    'Custom branding',
                ],
                'is_active' => true,
            ],
            [
                'name' => 'Pro',
                'slug' => 'pro',
                'price' => 299.99,
                'currency' => 'TRY',
                'max_uploads' => null, // unlimited
                'storage_days' => 365,
                'upload_days' => 90,
                'quality' => 'high',
                'advanced_customization' => true,
                'features' => [
                    'Unlimited uploads',
                    'Premium quality',
                    '1 year storage',
                    'Advanced customization',
                    'API access',
                    'White-label solution',
                    '24/7 support',
                ],
                'is_active' => true,
            ],
        ];

        foreach ($packages as $package) {
            Package::firstOrCreate(
                ['slug' => $package['slug']],
                $package
            );
        }
    }
}
