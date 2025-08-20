<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { ref } from 'vue';

interface Package {
    id: number;
    name: string;
    slug: string;
    price: number;
    currency: string;
}

interface User {
    id: number;
    name: string;
    email: string;
}

interface EventPhoto {
    id: number;
    photo_path: string;
    original_name: string;
    file_size: number;
    mime_type: string;
    order: number;
    is_cover: boolean;
    uploader_name?: string;
    uploader_email?: string;
    status: 'pending' | 'approved' | 'rejected' | 'deleted';
    created_at: string;
    photo_url: string;
}

interface Event {
    id: number;
    name: string;
    slug: string;
    description?: string;
    location: string;
    event_date: string;
    event_time?: string;
    image?: string;
    status: 'draft' | 'published' | 'cancelled';
    is_active: boolean;
    created_at: string;
    updated_at: string;
    user: User;
    package: Package;
    photos: EventPhoto[];
}

interface Props {
    event: Event;
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
        title: props.event.name,
        href: `/user/events/${props.event.id}`,
    },
];

const uploadForm = useForm({
    photos: [] as File[],
    uploader_name: '',
    uploader_email: '',
});

const selectedFiles = ref<File[]>([]);

function getStatusColor(status: string) {
    switch (status) {
        case 'published':
            return 'bg-green-100 text-green-800';
        case 'draft':
            return 'bg-gray-100 text-gray-800';
        case 'cancelled':
            return 'bg-red-100 text-red-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
}

function getPhotoStatusColor(status: string) {
    switch (status) {
        case 'approved':
            return 'bg-green-100 text-green-800';
        case 'pending':
            return 'bg-yellow-100 text-yellow-800';
        case 'rejected':
            return 'bg-red-100 text-red-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
}

function handleFileSelect(event: Event) {
    const target = event.target as HTMLInputElement;
    if (target.files) {
        selectedFiles.value = Array.from(target.files);
        uploadForm.photos = Array.from(target.files);
    }
}

function uploadPhotos() {
    uploadForm.post(`/user/events/${props.event.id}/photos`, {
        onSuccess: () => {
            selectedFiles.value = [];
            uploadForm.reset();
            // Clear file input
            const fileInput = document.getElementById('photos') as HTMLInputElement;
            if (fileInput) fileInput.value = '';
        }
    });
}

function approvePhoto(photoId: number) {
    const form = useForm({});
    form.patch(`/user/events/${props.event.id}/photos/${photoId}/approve`);
}

function rejectPhoto(photoId: number) {
    const form = useForm({});
    form.patch(`/user/events/${props.event.id}/photos/${photoId}/reject`);
}

function deletePhoto(photoId: number) {
    if (confirm('Are you sure you want to delete this photo?')) {
        const form = useForm({});
        form.delete(`/user/events/${props.event.id}/photos/${photoId}`);
    }
}

function setCoverPhoto(photoId: number) {
    const form = useForm({});
    form.patch(`/user/events/${props.event.id}/photos/${photoId}/set-cover`);
}

function formatDate(dateString: string) {
    return new Date(dateString).toLocaleDateString('tr-TR');
}

function formatFileSize(bytes: number) {
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    if (bytes === 0) return '0 Byte';
    const i = parseInt(Math.floor(Math.log(bytes) / Math.log(1024)).toString());
    return Math.round(bytes / Math.pow(1024, i) * 100) / 100 + ' ' + sizes[i];
}
</script>

<template>
    <Head :title="event.name" />
    
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6">
            <!-- Event Details -->
            <Card>
                <CardHeader>
                    <div class="flex justify-between items-start">
                        <div>
                            <div class="flex items-center gap-3 mb-2">
                                <CardTitle class="text-2xl">{{ event.name }}</CardTitle>
                                <Badge :class="getStatusColor(event.status)">
                                    {{ event.status }}
                                </Badge>
                            </div>
                            <p v-if="event.description" class="text-muted-foreground">
                                {{ event.description }}
                            </p>
                        </div>
                        
                        <div class="flex gap-2">
                            <Link :href="`/user/events/${event.id}/edit`">
                                <Button variant="outline">Edit</Button>
                            </Link>
                        </div>
                    </div>
                </CardHeader>
                
                <CardContent>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div>
                            <strong class="block text-sm text-muted-foreground">Location</strong>
                            <p>{{ event.location }}</p>
                        </div>
                        <div>
                            <strong class="block text-sm text-muted-foreground">Date</strong>
                            <p>{{ formatDate(event.event_date) }}
                                <span v-if="event.event_time"> at {{ event.event_time }}</span>
                            </p>
                        </div>
                        <div>
                            <strong class="block text-sm text-muted-foreground">Package</strong>
                            <p>{{ event.package.name }}</p>
                        </div>
                        <div>
                            <strong class="block text-sm text-muted-foreground">Photos</strong>
                            <p>{{ event.photos.length }} uploaded</p>
                        </div>
                    </div>
                    
                    <div v-if="event.image" class="mt-4">
                        <img
                            :src="`/storage/${event.image}`"
                            :alt="event.name"
                            class="max-w-sm rounded-lg shadow-sm"
                        />
                    </div>
                </CardContent>
            </Card>

            <!-- Upload Photos -->
            <Card>
                <CardHeader>
                    <CardTitle>Upload Photos</CardTitle>
                </CardHeader>
                
                <CardContent>
                    <form @submit.prevent="uploadPhotos" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium mb-2">Uploader Name (Optional)</label>
                                <input
                                    v-model="uploadForm.uploader_name"
                                    type="text"
                                    class="w-full px-3 py-2 border rounded-md"
                                />
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-2">Uploader Email (Optional)</label>
                                <input
                                    v-model="uploadForm.uploader_email"
                                    type="email"
                                    class="w-full px-3 py-2 border rounded-md"
                                />
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium mb-2">Select Photos (Max 10)</label>
                            <input
                                id="photos"
                                type="file"
                                multiple
                                accept="image/*"
                                @change="handleFileSelect"
                                class="w-full px-3 py-2 border rounded-md"
                            />
                        </div>
                        
                        <div v-if="selectedFiles.length > 0" class="space-y-2">
                            <p class="text-sm text-muted-foreground">Selected Files:</p>
                            <ul class="text-sm">
                                <li v-for="file in selectedFiles" :key="file.name">
                                    {{ file.name }} ({{ formatFileSize(file.size) }})
                                </li>
                            </ul>
                        </div>
                        
                        <Button
                            type="submit"
                            :disabled="uploadForm.processing || selectedFiles.length === 0"
                            class="bg-blue-600 hover:bg-blue-700"
                        >
                            <span v-if="uploadForm.processing">Uploading...</span>
                            <span v-else>Upload Photos</span>
                        </Button>
                    </form>
                </CardContent>
            </Card>

            <!-- Photos Gallery -->
            <Card v-if="event.photos.length > 0">
                <CardHeader>
                    <CardTitle>Photo Gallery</CardTitle>
                </CardHeader>
                
                <CardContent>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                        <div v-for="photo in event.photos" :key="photo.id" class="relative group">
                            <div class="aspect-square bg-gray-100 rounded-lg overflow-hidden">
                                <img
                                    :src="photo.photo_url"
                                    :alt="photo.original_name"
                                    class="w-full h-full object-cover"
                                />
                            </div>
                            
                            <!-- Photo Info -->
                            <div class="mt-2">
                                <div class="flex items-center justify-between">
                                    <Badge :class="getPhotoStatusColor(photo.status)" class="text-xs">
                                        {{ photo.status }}
                                    </Badge>
                                    <span v-if="photo.is_cover" class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded">
                                        Cover
                                    </span>
                                </div>
                                
                                <p class="text-xs text-muted-foreground mt-1" :title="photo.original_name">
                                    {{ photo.original_name.length > 20 ? photo.original_name.substring(0, 20) + '...' : photo.original_name }}
                                </p>
                                
                                <div v-if="photo.uploader_name" class="text-xs text-muted-foreground">
                                    By: {{ photo.uploader_name }}
                                </div>
                            </div>
                            
                            <!-- Photo Actions -->
                            <div class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                <div class="flex flex-col gap-1">
                                    <button
                                        v-if="photo.status === 'pending'"
                                        @click="approvePhoto(photo.id)"
                                        class="bg-green-600 hover:bg-green-700 text-white p-1 rounded text-xs"
                                        title="Approve"
                                    >
                                        ✓
                                    </button>
                                    
                                    <button
                                        v-if="photo.status === 'pending'"
                                        @click="rejectPhoto(photo.id)"
                                        class="bg-red-600 hover:bg-red-700 text-white p-1 rounded text-xs"
                                        title="Reject"
                                    >
                                        ✗
                                    </button>
                                    
                                    <button
                                        v-if="photo.status === 'approved' && !photo.is_cover"
                                        @click="setCoverPhoto(photo.id)"
                                        class="bg-blue-600 hover:bg-blue-700 text-white p-1 rounded text-xs"
                                        title="Set as Cover"
                                    >
                                        ⭐
                                    </button>
                                    
                                    <button
                                        @click="deletePhoto(photo.id)"
                                        class="bg-red-600 hover:bg-red-700 text-white p-1 rounded text-xs"
                                        title="Delete"
                                    >
                                        🗑
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>
            
            <div v-else class="text-center py-8 text-muted-foreground">
                <p>No photos uploaded yet.</p>
            </div>
        </div>
    </AppLayout>
</template>