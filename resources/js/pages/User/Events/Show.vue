<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { ref } from 'vue';
import VueEasyLightbox from 'vue-easy-lightbox';
import Swal from 'sweetalert2';

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
    upload_url: string;
    qr_code: string;
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

// Lightbox için
const showLightbox = ref(false);
const lightboxIndex = ref(0);
const lightboxImages = ref<string[]>([]);
const imageLoadStatus = ref<Record<string, boolean>>({});

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
    form.patch(`/user/events/${props.event.id}/photos/${photoId}/approve`, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: (page) => {
            if (page.props.flash?.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Başarılı!',
                    text: page.props.flash.success,
                    timer: 2000,
                    showConfirmButton: false
                });
            }
        }
    });
}

function rejectPhoto(photoId: number) {
    const form = useForm({});
    form.patch(`/user/events/${props.event.id}/photos/${photoId}/reject`, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: (page) => {
            if (page.props.flash?.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Başarılı!',
                    text: page.props.flash.success,
                    timer: 2000,
                    showConfirmButton: false
                });
            }
        }
    });
}

function deletePhoto(photoId: number) {
    Swal.fire({
        title: 'Emin misiniz?',
        text: 'Bu fotoğrafı silmek istediğinizden emin misiniz?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Evet, sil!',
        cancelButtonText: 'İptal'
    }).then((result) => {
        if (result.isConfirmed) {
            const form = useForm({});
            form.delete(`/user/events/${props.event.id}/photos/${photoId}`, {
                preserveState: true,
                preserveScroll: true,
                onSuccess: (page) => {
                    if (page.props.flash?.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Başarılı!',
                            text: page.props.flash.success,
                            timer: 2000,
                            showConfirmButton: false
                        });
                    }
                }
            });
        }
    });
}

function setCoverPhoto(photoId: number) {
    const form = useForm({});
    form.patch(`/user/events/${props.event.id}/photos/${photoId}/set-cover`, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: (page) => {
            if (page.props.flash?.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Başarılı!',
                    text: page.props.flash.success,
                    timer: 2000,
                    showConfirmButton: false
                });
            }
        }
    });
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

async function copyToClipboard(text: string) {
    try {
        await navigator.clipboard.writeText(text);
        alert('URL copied to clipboard!');
    } catch (err) {
        console.error('Failed to copy:', err);
        // Fallback for older browsers
        const textArea = document.createElement('textarea');
        textArea.value = text;
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand('copy');
        document.body.removeChild(textArea);
        alert('URL copied to clipboard!');
    }
}

function openLightbox(photoIndex: number) {
    // Tüm fotoğrafları lightbox'a ekle
    const photoUrls = props.event.photos.map(photo => photo.photo_url);

    // Debug için URL'leri console'a yazdır
    console.log('Lightbox URLs:', photoUrls);

    lightboxImages.value = photoUrls;

    // Tıklanan fotoğrafın index'ini direkt kullan
    lightboxIndex.value = photoIndex;
    showLightbox.value = true;
}

function downloadQRCode() {
    try {
        const qrContainer = document.getElementById('qr-code');
        if (!qrContainer) return;

        const svgElement = qrContainer.querySelector('svg');
        if (!svgElement) return;

        // Clone the SVG to avoid modifying the original
        const clonedSvg = svgElement.cloneNode(true) as SVGElement;

        // Create a canvas to convert SVG to PNG
        const canvas = document.createElement('canvas');
        const ctx = canvas.getContext('2d');
        if (!ctx) return;

        // Set canvas size (make it larger for better quality)
        canvas.width = 400;
        canvas.height = 400;

        // Create an image from SVG
        const svgData = new XMLSerializer().serializeToString(clonedSvg);
        const svgBlob = new Blob([svgData], { type: 'image/svg+xml' });
        const url = URL.createObjectURL(svgBlob);

        const img = new Image();
        img.onload = function () {
            // Fill with white background
            ctx.fillStyle = 'white';
            ctx.fillRect(0, 0, canvas.width, canvas.height);

            // Draw the QR code
            ctx.drawImage(img, 0, 0, canvas.width, canvas.height);

            // Convert to blob and download
            canvas.toBlob(function (blob) {
                if (blob) {
                    const downloadUrl = URL.createObjectURL(blob);
                    const link = document.createElement('a');
                    link.href = downloadUrl;
                    link.download = `${props.event.name.replace(/[^a-zA-Z0-9]/g, '_')}_QR_Code.png`;
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                    URL.revokeObjectURL(downloadUrl);
                }
            }, 'image/png');

            URL.revokeObjectURL(url);
        };

        img.src = url;
    } catch (error) {
        console.error('Failed to download QR code:', error);
        alert('Failed to download QR code. Please try again.');
    }
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
                        <img :src="`/storage/${event.image}`" :alt="event.name" class="max-w-sm rounded-lg shadow-sm" />
                    </div>
                </CardContent>
            </Card>

            <!-- Guest Upload Section -->
            <Card v-if="event.status === 'published'">
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z">
                            </path>
                        </svg>
                        Guest Photo Upload
                    </CardTitle>
                </CardHeader>

                <CardContent>
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- QR Code -->
                        <div class="text-center">
                            <h3 class="font-medium mb-3">QR Code for Mobile Upload</h3>
                            <div class="bg-white p-4 rounded-lg border inline-block" v-html="event.qr_code"
                                id="qr-code"></div>
                            <p class="text-sm text-muted-foreground mt-2 mb-3">
                                Guests can scan this QR code to upload photos directly
                            </p>
                            <Button @click="downloadQRCode" variant="outline" size="sm"
                                class="inline-flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                    </path>
                                </svg>
                                Download QR Code
                            </Button>
                        </div>

                        <!-- Upload URL -->
                        <div>
                            <h3 class="font-medium mb-3">Direct Upload Link</h3>
                            <div class="space-y-3">
                                <div class="flex gap-2">
                                    <input :value="event.upload_url" readonly
                                        class="flex-1 px-3 py-2 border rounded-md bg-gray-50 text-sm" />
                                    <Button @click="copyToClipboard(event.upload_url)" variant="outline" size="sm">
                                        Copy
                                    </Button>
                                </div>

                                <div class="flex gap-2">
                                    <a :href="event.upload_url" target="_blank" class="flex-1">
                                        <Button variant="outline" class="w-full">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14">
                                                </path>
                                            </svg>
                                            Open Upload Page
                                        </Button>
                                    </a>
                                </div>

                                <p class="text-sm text-muted-foreground">
                                    Share this link with guests so they can upload their photos from the event
                                </p>
                            </div>
                        </div>
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
                                <input v-model="uploadForm.uploader_name" type="text"
                                    class="w-full px-3 py-2 border rounded-md" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-2">Uploader Email (Optional)</label>
                                <input v-model="uploadForm.uploader_email" type="email"
                                    class="w-full px-3 py-2 border rounded-md" />
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2">Select Photos (Max 10)</label>
                            <input id="photos" type="file" multiple accept="image/*" @change="handleFileSelect"
                                class="w-full px-3 py-2 border rounded-md" />
                        </div>

                        <div v-if="selectedFiles.length > 0" class="space-y-2">
                            <p class="text-sm text-muted-foreground">Selected Files:</p>
                            <ul class="text-sm">
                                <li v-for="file in selectedFiles" :key="file.name">
                                    {{ file.name }} ({{ formatFileSize(file.size) }})
                                </li>
                            </ul>
                        </div>

                        <Button type="submit" :disabled="uploadForm.processing || selectedFiles.length === 0"
                            class="bg-blue-600 hover:bg-blue-700">
                            <span v-if="uploadForm.processing">Uploading...</span>
                            <span v-else>Upload Photos</span>
                        </Button>
                    </form>
                </CardContent>
            </Card>

            <!-- Photos Gallery -->
            <Card v-if="event.photos.length > 0">
                <CardHeader>
                    <div class="flex justify-between items-center">
                        <CardTitle>Photo Gallery ({{ event.photos.length }})</CardTitle>
                        <div class="flex items-center gap-4">
                            <div class="flex gap-2 text-sm">
                                <span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded">
                                    {{event.photos.filter(p => p.status === 'pending').length}} Pending
                                </span>
                                <span class="bg-green-100 text-green-800 px-2 py-1 rounded">
                                    {{event.photos.filter(p => p.status === 'approved').length}} Approved
                                </span>
                                <span class="bg-red-100 text-red-800 px-2 py-1 rounded">
                                    {{event.photos.filter(p => p.status === 'rejected').length}} Rejected
                                </span>
                            </div>
                            <a :href="`/user/events/${event.id}/photos/download-all`" target="_blank">
                                <Button variant="outline" size="sm" class="inline-flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                              d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                        </path>
                                    </svg>
                                    Tüm Fotoğrafları İndir
                                </Button>
                            </a>
                        </div>
                    </div>
                </CardHeader>

                <CardContent>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                        <div v-for="(photo, index) in event.photos" :key="photo.id" class="relative group">
                            <div class="bg-white p-2 rounded-lg shadow cursor-pointer" @click="openLightbox(index)">
                                <!-- En basit şekilde görüntüleme -->
                                <img :src="photo.photo_url" :alt="photo.original_name"
                                    style="width: 100%; display: block; margin: 0 auto;"
                                    @error="console.error('Failed to load image:', photo.photo_url)"
                                    @load="console.log('Image loaded successfully:', photo.photo_url)" loading="lazy"
                                    decoding="async" />

                                <!-- Fotoğraf bilgileri -->
                                <div class="mt-2">
                                    <div class="flex items-center justify-between">
                                        <Badge :class="getPhotoStatusColor(photo.status)" class="text-xs">
                                            {{ photo.status }}
                                        </Badge>
                                        <span v-if="photo.is_cover"
                                            class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded">
                                            Cover
                                        </span>
                                    </div>

                                    <p class="text-xs text-muted-foreground mt-1" :title="photo.original_name">
                                        {{ photo.original_name.length > 20 ? photo.original_name.substring(0, 20) +
                                        '...' : photo.original_name }}
                                    </p>

                                    <!-- Uploader Info -->
                                    <div v-if="photo.uploader_name || photo.uploader_email"
                                        class="bg-gray-50 p-2 rounded text-xs mt-2">
                                        <div v-if="photo.uploader_name" class="font-medium text-gray-700">
                                            👤 {{ photo.uploader_name }}
                                        </div>
                                        <div v-if="photo.uploader_email" class="text-gray-600">
                                            ✉️ {{ photo.uploader_email }}
                                        </div>
                                    </div>

                                    <!-- File Info -->
                                    <div class="text-xs text-gray-500 mt-2">
                                        📅 {{ formatDate(photo.created_at) }} · 📁 {{ formatFileSize(photo.file_size) }}
                                    </div>
                                </div>
                            </div>

                            <!-- Photo Actions -->
                            <div class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                <div class="flex flex-col gap-1">
                                    <button v-if="photo.status === 'pending'" @click.stop="approvePhoto(photo.id)"
                                        class="bg-green-600 hover:bg-green-700 text-white p-1 rounded text-xs"
                                        title="Approve">
                                        ✓
                                    </button>

                                    <button v-if="photo.status === 'pending'" @click.stop="rejectPhoto(photo.id)"
                                        class="bg-red-600 hover:bg-red-700 text-white p-1 rounded text-xs"
                                        title="Reject">
                                        ✗
                                    </button>

                                    <button v-if="photo.status === 'approved' && !photo.is_cover"
                                        @click.stop="setCoverPhoto(photo.id)"
                                        class="bg-blue-600 hover:bg-blue-700 text-white p-1 rounded text-xs"
                                        title="Set as Cover">
                                        ⭐
                                    </button>

                                    <button @click.stop="deletePhoto(photo.id)"
                                        class="bg-red-600 hover:bg-red-700 text-white p-1 rounded text-xs"
                                        title="Delete">
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

        <!-- Lightbox Component -->
        <VueEasyLightbox :visible="showLightbox" :imgs="lightboxImages" :index="lightboxIndex"
            @hide="showLightbox = false" />
    </AppLayout>
</template>
