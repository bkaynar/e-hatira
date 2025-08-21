<script setup lang="ts">
import AppHeaderLayout from '@/layouts/app/AppHeaderLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Badge } from '@/components/ui/badge';
import {
    Calendar,
    Clock,
    MapPin,
    Upload,
    Camera,
    Star,
    Check,
    AlertCircle,
    X
} from 'lucide-vue-next';
import { ref, computed } from 'vue';

interface Package {
    id: number;
    name: string;
    slug: string;
    price: number;
    currency: string;
    max_uploads?: number;
    storage_days: number;
    upload_days: number;
    quality: 'normal' | 'high';
    advanced_customization: boolean;
    features?: string[];
    is_active: boolean;
}

interface Props {
    packages: Package[];
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/user/dashboard',
    },
    {
        title: 'Events',
        href: '/user/events',
    },
    {
        title: 'Create Event',
        href: '/user/events/create',
    },
];

const form = useForm({
    name: '',
    description: '',
    location: '',
    event_date: '',
    event_time: '',
    package_id: null as number | null,
    image: null as File | null,
});

const selectedPackage = computed(() => {
    if (!form.package_id) return null;
    return props.packages.find(pkg => pkg.id === form.package_id) || null;
});

const imagePreview = ref<string | null>(null);

const handleImageChange = (event: Event) => {
    const target = event.target as HTMLInputElement;
    const file = target.files?.[0] || null;
    form.image = file;

    if (file) {
        const reader = new FileReader();
        reader.onload = (e) => {
            imagePreview.value = e.target?.result as string;
        };
        reader.readAsDataURL(file);
    } else {
        imagePreview.value = null;
    }
};

const removeImage = () => {
    form.image = null;
    imagePreview.value = null;
    const fileInput = document.getElementById('image') as HTMLInputElement;
    if (fileInput) fileInput.value = '';
};

function submitForm() {
    form.post(route('user.events.store'), {
        onSuccess: () => {
            // Success message will be handled by the controller
        },
        onError: (errors) => {
            console.error('Form errors:', errors);
        }
    });
}
</script>

<template>
    <Head title="Create Event" />

    <AppHeaderLayout :breadcrumbs="breadcrumbs">
        <!-- Header Section -->
        <div class="max-w-4xl my-8 mx-auto">
            <div class="flex items-center gap-3 mb-4">
                <div class="p-3 bg-blue-100 dark:bg-blue-900/20 rounded-full">
                    <Camera class="h-6 w-6 text-blue-600" />
                </div>
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                        Yeni Etkinlik Oluştur
                    </h1>
                    <p class="text-gray-600 dark:text-gray-300 mt-1">
                        Set up your photo event and start collecting memories
                    </p>
                </div>
            </div>
        </div>

        <div class="max-w-4xl mx-auto">
            <div class="grid gap-8 lg:grid-cols-3">
                <!-- Main Form -->
                <div class="lg:col-span-2">
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <Calendar class="h-5 w-5 text-blue-600" />
                                Event Details
                            </CardTitle>
                        </CardHeader>

                        <CardContent>
                            <form @submit.prevent="submitForm" class="space-y-6">
                                <!-- Event Name -->
                                <div class="space-y-2">
                                    <Label for="name" class="text-base font-medium">Event Name *</Label>
                                    <div class="relative">
                                        <Input
                                            id="name"
                                            v-model="form.name"
                                            type="text"
                                            placeholder="e.g. Sarah & John's Wedding"
                                            required
                                            class="pl-10"
                                            :class="{ 'border-red-500': form.errors.name }"
                                        />
                                        <Calendar class="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-gray-400" />
                                    </div>
                                    <div v-if="form.errors.name" class="flex items-center gap-1 text-red-500 text-sm">
                                        <AlertCircle class="h-4 w-4" />
                                        {{ form.errors.name }}
                                    </div>
                                </div>

                                <!-- Description -->
                                <div class="space-y-2">
                                    <Label for="description" class="text-base font-medium">Description</Label>
                                    <textarea
                                        id="description"
                                        v-model="form.description"
                                        rows="4"
                                        placeholder="Tell us about your event... (optional)"
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"
                                        :class="{ 'border-red-500': form.errors.description }"
                                    />
                                    <div v-if="form.errors.description" class="flex items-center gap-1 text-red-500 text-sm">
                                        <AlertCircle class="h-4 w-4" />
                                        {{ form.errors.description }}
                                    </div>
                                </div>

                                <!-- Location -->
                                <div class="space-y-2">
                                    <Label for="location" class="text-base font-medium">Location *</Label>
                                    <div class="relative">
                                        <Input
                                            id="location"
                                            v-model="form.location"
                                            type="text"
                                            placeholder="e.g. Grand Ballroom, Istanbul"
                                            required
                                            class="pl-10"
                                            :class="{ 'border-red-500': form.errors.location }"
                                        />
                                        <MapPin class="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-gray-400" />
                                    </div>
                                    <div v-if="form.errors.location" class="flex items-center gap-1 text-red-500 text-sm">
                                        <AlertCircle class="h-4 w-4" />
                                        {{ form.errors.location }}
                                    </div>
                                </div>

                                <!-- Date and Time -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div class="space-y-2">
                                        <Label for="event_date" class="text-base font-medium">Event Date *</Label>
                                        <div class="relative">
                                            <Input
                                                id="event_date"
                                                v-model="form.event_date"
                                                type="date"
                                                required
                                                class="pl-10"
                                                :class="{ 'border-red-500': form.errors.event_date }"
                                            />
                                            <Calendar class="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-gray-400" />
                                        </div>
                                        <div v-if="form.errors.event_date" class="flex items-center gap-1 text-red-500 text-sm">
                                            <AlertCircle class="h-4 w-4" />
                                            {{ form.errors.event_date }}
                                        </div>
                                    </div>

                                    <div class="space-y-2">
                                        <Label for="event_time" class="text-base font-medium">Event Time</Label>
                                        <div class="relative">
                                            <Input
                                                id="event_time"
                                                v-model="form.event_time"
                                                type="time"
                                                class="pl-10"
                                                :class="{ 'border-red-500': form.errors.event_time }"
                                            />
                                            <Clock class="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-gray-400" />
                                        </div>
                                        <div v-if="form.errors.event_time" class="flex items-center gap-1 text-red-500 text-sm">
                                            <AlertCircle class="h-4 w-4" />
                                            {{ form.errors.event_time }}
                                        </div>
                                    </div>
                                </div>

                                <!-- Cover Image Upload -->
                                <div class="space-y-3">
                                    <Label class="text-base font-medium">Cover Image</Label>
                                    <div class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-6 transition-colors hover:border-blue-400">
                                        <div v-if="!imagePreview" class="text-center">
                                            <Upload class="mx-auto h-12 w-12 text-gray-400" />
                                            <div class="mt-4">
                                                <label for="image" class="cursor-pointer">
                                                    <span class="mt-2 block text-sm font-medium text-gray-900 dark:text-white">
                                                        Upload a cover image
                                                    </span>
                                                    <span class="mt-1 block text-xs text-gray-500">
                                                        PNG, JPG, GIF up to 2MB
                                                    </span>
                                                </label>
                                                <input
                                                    id="image"
                                                    type="file"
                                                    accept="image/*"
                                                    @change="handleImageChange"
                                                    class="sr-only"
                                                />
                                            </div>
                                        </div>
                                        <div v-else class="relative">
                                            <img
                                                :src="imagePreview"
                                                alt="Preview"
                                                class="w-full h-48 object-cover rounded-lg"
                                            />
                                            <Button
                                                type="button"
                                                variant="destructive"
                                                size="sm"
                                                class="absolute top-2 right-2"
                                                @click="removeImage"
                                            >
                                                <X class="h-4 w-4" />
                                            </Button>
                                        </div>
                                    </div>
                                    <div v-if="form.errors.image" class="flex items-center gap-1 text-red-500 text-sm">
                                        <AlertCircle class="h-4 w-4" />
                                        {{ form.errors.image }}
                                    </div>
                                </div>

                                <!-- Submit Buttons -->
                                <div class="flex justify-end space-x-3 pt-6 border-t">
                                    <Button
                                        type="button"
                                        variant="outline"
                                        @click="$inertia.visit(route('user.events.index'))"
                                    >
                                        Cancel
                                    </Button>

                                    <Button
                                        type="submit"
                                        :disabled="form.processing"
                                        class="bg-blue-600 hover:bg-blue-700 px-8"
                                    >
                                        <Camera class="w-4 h-4 mr-2" />
                                        <span v-if="form.processing">Creating...</span>
                                        <span v-else>Create Event</span>
                                    </Button>
                                </div>
                            </form>
                        </CardContent>
                    </Card>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Package Selection -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <Star class="h-5 w-5 text-yellow-600" />
                                Choose Package
                            </CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="space-y-3">
                                <div v-for="pkg in packages" :key="pkg.id">
                                    <label
                                        :for="`package-${pkg.id}`"
                                        class="block p-4 border-2 rounded-lg cursor-pointer transition-all hover:shadow-md"
                                        :class="{
                                            'border-blue-500 bg-blue-50 dark:bg-blue-900/10': form.package_id === pkg.id,
                                            'border-gray-200 dark:border-gray-700': form.package_id !== pkg.id
                                        }"
                                    >
                                        <div class="flex items-center justify-between">
                                            <input
                                                :id="`package-${pkg.id}`"
                                                v-model="form.package_id"
                                                type="radio"
                                                :value="pkg.id"
                                                class="sr-only"
                                            />
                                            <div class="flex-1">
                                                <div class="flex items-center justify-between mb-2">
                                                    <h3 class="font-semibold text-gray-900 dark:text-white">
                                                        {{ pkg.name }}
                                                    </h3>
                                                    <div class="flex items-center gap-1">
                                                        <span class="text-lg font-bold text-blue-600">
                                                            {{ pkg.price }}
                                                        </span>
                                                        <span class="text-sm text-gray-500">{{ pkg.currency }}</span>
                                                    </div>
                                                </div>
                                                <div class="space-y-1 text-sm text-gray-600 dark:text-gray-300">
                                                    <div class="flex items-center gap-2">
                                                        <Check class="h-3 w-3 text-green-600" />
                                                        {{ pkg.max_uploads ? `${pkg.max_uploads} uploads` : 'Unlimited uploads' }}
                                                    </div>
                                                    <div class="flex items-center gap-2">
                                                        <Check class="h-3 w-3 text-green-600" />
                                                        {{ pkg.storage_days }} days storage
                                                    </div>
                                                    <div class="flex items-center gap-2">
                                                        <Check class="h-3 w-3 text-green-600" />
                                                        {{ pkg.quality === 'high' ? 'High' : 'Standard' }} quality
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="ml-4">
                                                <div
                                                    class="w-4 h-4 border-2 rounded-full flex items-center justify-center"
                                                    :class="{
                                                        'border-blue-500 bg-blue-500': form.package_id === pkg.id,
                                                        'border-gray-300': form.package_id !== pkg.id
                                                    }"
                                                >
                                                    <div v-if="form.package_id === pkg.id" class="w-2 h-2 bg-white rounded-full"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            </div>
                            <div v-if="form.errors.package_id" class="flex items-center gap-1 text-red-500 text-sm">
                                <AlertCircle class="h-4 w-4" />
                                {{ form.errors.package_id }}
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Package Preview -->
                    <Card v-if="selectedPackage">
                        <CardHeader>
                            <CardTitle class="text-lg">Package Summary</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="space-y-3">
                                <div class="flex justify-between items-center">
                                    <span class="font-medium">{{ selectedPackage.name }}</span>
                                    <Badge variant="secondary">Selected</Badge>
                                </div>
                                <div class="text-2xl font-bold text-blue-600">
                                    {{ selectedPackage.price }} {{ selectedPackage.currency }}
                                </div>
                                <div class="space-y-1 text-sm text-gray-600 dark:text-gray-300">
                                    <div>✓ {{ selectedPackage.max_uploads ? `${selectedPackage.max_uploads} uploads` : 'Unlimited uploads' }}</div>
                                    <div>✓ {{ selectedPackage.storage_days }} days storage</div>
                                    <div>✓ {{ selectedPackage.upload_days }} days upload window</div>
                                    <div>✓ {{ selectedPackage.quality === 'high' ? 'High' : 'Standard' }} quality photos</div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Help Card -->
                    <Card class="bg-blue-50 dark:bg-blue-900/10 mb-8">
                        <CardContent class="pt-6">
                            <div class="text-center space-y-3">
                                <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/20 rounded-full flex items-center justify-center mx-auto">
                                    <Camera class="w-6 h-6 text-blue-600" />
                                </div>
                                <h3 class="font-medium text-gray-900 dark:text-white">Need Help?</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-300">
                                    Create your event and guests can start uploading photos immediately.
                                </p>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    </AppHeaderLayout>
</template>
