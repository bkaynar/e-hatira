<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { ref, computed } from 'vue';
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
        title: 'Kontrol Paneli',
        href: '/user/dashboard',
    },
    {
        title: 'Etkinlikler',
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
const selectedPhotos = ref<Set<number>>(new Set());
const isSelectMode = ref(false);

// Lightbox için
const showLightbox = ref(false);
const lightboxIndex = ref(0);
const lightboxImages = ref<string[]>([]);
const lightboxVideos = ref<string[]>([]);
const lightboxTypes = ref<('image' | 'video')[]>([]);
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

function isVideo(mimeType: string) {
    return mimeType.startsWith('video/');
}

const selectedCount = computed(() => selectedPhotos.value.size);

function toggleSelectMode() {
    isSelectMode.value = !isSelectMode.value;
    if (!isSelectMode.value) {
        selectedPhotos.value.clear();
    }
}

function togglePhotoSelection(photoId: number) {
    if (selectedPhotos.value.has(photoId)) {
        selectedPhotos.value.delete(photoId);
    } else {
        selectedPhotos.value.add(photoId);
    }
}

function selectAllPhotos() {
    event.photos.forEach(photo => {
        selectedPhotos.value.add(photo.id);
    });
}

function clearSelection() {
    selectedPhotos.value.clear();
}

function bulkDeletePhotos() {
    if (selectedPhotos.value.size === 0) return;
    
    Swal.fire({
        title: 'Emin misiniz?',
        text: `${selectedCount.value} fotoğrafı silmek istediğinizden emin misiniz?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Evet, sil!',
        cancelButtonText: 'İptal'
    }).then((result) => {
        if (result.isConfirmed) {
            const form = useForm({
                photo_ids: Array.from(selectedPhotos.value)
            });
            
            form.post(`/user/events/${props.event.id}/photos/bulk-delete`, {
                preserveState: true,
                preserveScroll: true,
                onSuccess: (page) => {
                    selectedPhotos.value.clear();
                    isSelectMode.value = false;
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

async function copyToClipboard(text: string) {
    try {
        await navigator.clipboard.writeText(text);
        alert('URL copied to clipboard!');
    } catch (err) {
        console.error('Kopyalama başarısız:', err);
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
    const photo = props.event.photos[photoIndex];
    
    if (isVideo(photo.mime_type)) {
        // Video için modal aç
        openVideoModal(photo.photo_url);
        return;
    }
    
    // Sadece resimleri lightbox'a ekle
    const imagePhotos = props.event.photos.filter(p => !isVideo(p.mime_type));
    const photoUrls = imagePhotos.map(photo => photo.photo_url);
    
    // Tıklanan fotoğrafın image listesindeki index'ini bul
    const imageIndex = imagePhotos.findIndex(p => p.id === photo.id);

    lightboxImages.value = photoUrls;
    lightboxIndex.value = imageIndex;
    showLightbox.value = true;
}

const showVideoModal = ref(false);
const currentVideoUrl = ref('');

function openVideoModal(videoUrl: string) {
    currentVideoUrl.value = videoUrl;
    showVideoModal.value = true;
}

function closeVideoModal() {
    showVideoModal.value = false;
    currentVideoUrl.value = '';
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
        alert('QR kod indirilemedi. Lütfen tekrar deneyin.');
    }
}
</script>

<template>

    <Head :title="event.name" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6">
            <!-- Event Details -->
            <Card class="p-6 m-6">
                <CardHeader>
                    <div class="flex justify-between items-start">
                        <div class="flex gap-4">
                            <!-- Event Avatar -->
                            <div v-if="event.image" class="flex-shrink-0">
                                <img :src="`/storage/${event.image}`" :alt="event.name"
                                     class="w-16 h-16 rounded-xl object-cover shadow-md border-2 border-white" />
                            </div>
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
                        </div>

                        <div class="flex gap-2">
                            <Link :href="`/user/events/${event.id}/edit`">
                            <Button variant="outline">Düzenle</Button>
                            </Link>
                        </div>
                    </div>
                </CardHeader>

                <CardContent>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div>
                            <strong class="block text-sm text-muted-foreground">Konum</strong>
                            <p>{{ event.location }}</p>
                        </div>
                        <div>
                            <strong class="block text-sm text-muted-foreground">Tarih</strong>
                            <p>{{ formatDate(event.event_date) }}
                                <span v-if="event.event_time"> at {{ event.event_time }}</span>
                            </p>
                        </div>
                        <div>
                            <strong class="block text-sm text-muted-foreground">Paket</strong>
                            <p>{{ event.package.name }}</p>
                        </div>
                        <div>
                            <strong class="block text-sm text-muted-foreground">Fotoğraflar</strong>
                            <p>{{ event.photos.length }} uploaded</p>
                        </div>
                    </div>

                </CardContent>
            </Card>

            <!-- Guest Upload Section -->
            <Card v-if="event.status === 'published'" class="p-6 m-6">
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z">
                            </path>
                        </svg>
                        Misafir Fotoğraf Yükleme
                    </CardTitle>
                </CardHeader>

                <CardContent>
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- QR Code -->
                        <div class="text-center">
                            <h3 class="font-medium mb-3">Mobil Yükleme için QR Kod</h3>
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
                            <h3 class="font-medium mb-3">Direkt Yükleme Bağlantısı</h3>
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
                                            Yükleme Sayfasını Aç
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


            <!-- Photos Gallery -->
            <Card v-if="event.photos.length > 0" class="p-6 m-6">
                <CardHeader>
                    <div class="flex justify-between items-center">
                        <CardTitle>Photo Gallery ({{ event.photos.length }})</CardTitle>
                        <div class="flex items-center gap-4">
                            <!-- Bulk Select Controls -->
                            <div v-if="!isSelectMode" class="flex items-center gap-2">
                                <Button @click="toggleSelectMode" variant="outline" size="sm">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    Toplu Seç
                                </Button>
                            </div>
                            
                            <div v-if="isSelectMode" class="flex items-center gap-2">
                                <span class="text-sm text-gray-600">{{ selectedCount }} seçili</span>
                                <Button @click="selectAllPhotos" variant="outline" size="sm">Tümü</Button>
                                <Button @click="clearSelection" variant="outline" size="sm">Temizle</Button>
                                <Button @click="bulkDeletePhotos" variant="destructive" size="sm" :disabled="selectedCount === 0">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                    Sil ({{ selectedCount }})
                                </Button>
                                <Button @click="toggleSelectMode" variant="outline" size="sm">İptal</Button>
                            </div>
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
                            <!-- Bulk Select Checkbox -->
                            <div v-if="isSelectMode" class="absolute top-2 left-2 z-10">
                                <input 
                                    type="checkbox" 
                                    :checked="selectedPhotos.has(photo.id)"
                                    @change="togglePhotoSelection(photo.id)"
                                    class="w-5 h-5 text-blue-600 bg-white border-gray-300 rounded focus:ring-blue-500 focus:ring-2"
                                >
                            </div>
                            
                            <div class="bg-white p-2 rounded-lg shadow cursor-pointer" 
                                 @click="isSelectMode ? togglePhotoSelection(photo.id) : openLightbox(index)"
                                 :class="{'ring-2 ring-blue-500': isSelectMode && selectedPhotos.has(photo.id)}">
                                <!-- Video görüntüleme -->
                                <div v-if="isVideo(photo.mime_type)" class="relative aspect-video bg-gray-100 rounded-lg overflow-hidden">
                                    <video class="w-full h-full object-cover" :src="photo.photo_url" preload="metadata">
                                        <source :src="photo.photo_url" :type="photo.mime_type">
                                        Video oynatılamıyor.
                                    </video>
                                    <div class="absolute inset-0 flex items-center justify-center bg-black bg-opacity-50 hover:bg-opacity-30 transition-all">
                                        <svg class="w-16 h-16 text-white" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M8 5v14l11-7z"/>
                                        </svg>
                                    </div>
                                    <div class="absolute top-2 right-2 bg-black bg-opacity-75 text-white px-2 py-1 rounded text-xs">
                                        VIDEO
                                    </div>
                                </div>
                                
                                <!-- Resim görüntüleme -->
                                <img v-else :src="photo.photo_url" :alt="photo.original_name"
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
                                            Kapak
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
                                        title="Onayla">
                                        ✓
                                    </button>

                                    <button v-if="photo.status === 'pending'" @click.stop="rejectPhoto(photo.id)"
                                        class="bg-red-600 hover:bg-red-700 text-white p-1 rounded text-xs"
                                        title="Reddet">
                                        ✗
                                    </button>

                                    <button v-if="photo.status === 'approved' && !photo.is_cover"
                                        @click.stop="setCoverPhoto(photo.id)"
                                        class="bg-blue-600 hover:bg-blue-700 text-white p-1 rounded text-xs"
                                        title="Kapak Yap">
                                        ⭐
                                    </button>

                                    <button @click.stop="deletePhoto(photo.id)"
                                        class="bg-red-600 hover:bg-red-700 text-white p-1 rounded text-xs"
                                        title="Sil">
                                        🗑
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <div v-else class="text-center py-8 text-muted-foreground">
                <p>Henüz fotoğraf yüklenmemiş.</p>
            </div>
        </div>

        <!-- Lightbox Component -->
        <VueEasyLightbox :visible="showLightbox" :imgs="lightboxImages" :index="lightboxIndex"
            @hide="showLightbox = false" />
            
        <!-- Video Modal -->
        <div v-if="showVideoModal" class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50" 
             @click="closeVideoModal">
            <div class="relative max-w-4xl max-h-full p-4" @click.stop>
                <button @click="closeVideoModal" 
                        class="absolute -top-10 -right-10 text-white hover:text-gray-300 text-2xl z-10">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
                <video controls autoplay class="max-w-full max-h-[80vh] rounded-lg">
                    <source :src="currentVideoUrl" type="video/mp4">
                    <source :src="currentVideoUrl" type="video/quicktime">
                    <source :src="currentVideoUrl" type="video/mov">
                    Video oynatılamıyor.
                </video>
            </div>
        </div>
    </AppLayout>
</template>
