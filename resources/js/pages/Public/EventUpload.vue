<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import heic2any from 'heic2any';
import { ref, computed, onMounted } from 'vue';

interface EventModel {
    id: number;
    name: string;
    slug: string;
    description?: string;
    location: string;
    event_date: string;
    image?: string;
    package_name?: string;
    photos_count: number;
}

interface Props {
    event: EventModel;
}

const props = defineProps<Props>();

// State
const showUserModal = ref(true);
const uploaderInfo = ref({
    name: '',
    email: ''
});

const form = useForm({
    files: [] as File[],
    uploader_name: '',
    uploader_email: '',
});

const filePreviews = ref<Array<{ file: File; preview: string; type: 'image' | 'video' }>>([]);
const rejectedFiles = ref<Array<{ file: File; reason: string }>>([]);
const isDragOver = ref(false);
const uploadProgress = ref(0);
const isUploading = ref(false);
const showSuccessMessage = ref(false);
const successMessage = ref('');
const isErrorMessage = ref(false);
const emailError = ref('');
const serverFileErrors = ref<Record<string, string>>({});

// Email validation
const validateEmail = (email: string) => {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
};

// Computed
const hasUserInfo = computed(() => {
    return uploaderInfo.value.name && uploaderInfo.value.email && !emailError.value;
});

// Methods
const handleUserInfoSubmit = () => {
    // Clear previous errors
    emailError.value = '';

    // Validate email
    if (!validateEmail(uploaderInfo.value.email)) {
        emailError.value = 'Please enter a valid email address.';
        return;
    }

    if (hasUserInfo.value) {
        form.uploader_name = uploaderInfo.value.name;
        form.uploader_email = uploaderInfo.value.email;
        showUserModal.value = false;
    }
};

// Watch email input for real-time validation
const handleEmailInput = () => {
    if (uploaderInfo.value.email && !validateEmail(uploaderInfo.value.email)) {
        emailError.value = 'Please enter a valid email address.';
    } else {
        emailError.value = '';
    }
};

const handleFileSelect = (e: globalThis.Event) => {
    const target = e.target as HTMLInputElement | null;
    if (!target) return;
    const files = Array.from(target.files || []);
    addFiles(files);
};

const handleDrop = (event: DragEvent) => {
    event.preventDefault();
    isDragOver.value = false;
    const files = Array.from(event.dataTransfer?.files || []);
    addFiles(files);
};

// Boyut limitleri (gerekirse ayarlanabilir)
const MAX_IMAGE_SIZE = 15 * 1024 * 1024; // 15MB
const MAX_VIDEO_SIZE = 250 * 1024 * 1024; // 250MB
const ACCEPT_IMAGE_TYPES = [
    'image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/heic', 'image/heif', 'image/webp'
];
const ACCEPT_VIDEO_TYPES = [
    'video/mp4', 'video/quicktime', 'video/mov', 'video/avi', 'video/wmv', 'video/x-msvideo'
];

const formatBytes = (bytes: number) => {
    if (bytes === 0) return '0 B';
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
};

const addFiles = async (newFiles: File[]) => {
    console.log('Seçilen dosyalar:', newFiles.map(f => f.name));
    rejectedFiles.value = []; // yeni seçimde önceki reddedilenleri temizle
    for (const original of newFiles) {
        let file = original;
        // Bazı iOS tarayıcılarında HEIC dosyalarının type değeri boş gelebiliyor.
        const lowerName = file.name.toLowerCase();
        const rawType = file.type;
        console.log(`İşleniyor: ${file.name}, tip: ${rawType}, boyut: ${formatBytes(file.size)}`);
        const looksHeicByName = (!rawType || rawType === '') && (lowerName.endsWith('.heic') || lowerName.endsWith('.heif'));
        let isImage = rawType.startsWith('image/');
        const isVideo = rawType.startsWith('video/');
        if (!isImage && !isVideo && looksHeicByName) {
            // HEIC olduğunu varsay
            isImage = true;
            file = new File([file], file.name, { type: 'image/heic' });
            console.log(`HEIC olarak tanımlandı: ${file.name}`);
        }

        if (!isImage && !isVideo) {
            rejectedFiles.value.push({ file, reason: 'Desteklenmeyen tür' });
            continue;
        }

        // HEIC/HEIF dönüştürme
        if (isImage && (file.type === 'image/heic' || file.type === 'image/heif')) {
            try {
                const convertedResult = await heic2any({ blob: file, toType: 'image/jpeg', quality: 0.9 });
                const convertedBlob = Array.isArray(convertedResult) ? convertedResult[0] : convertedResult;
                const jpegFile = new File([convertedBlob as BlobPart], file.name.replace(/\.(heic|heif)$/i, '') + '.jpg', { type: 'image/jpeg' });
                file = jpegFile;
            } catch {
                rejectedFiles.value.push({ file, reason: 'HEIC dönüştürme başarısız' });
                continue;
            }
        }

        if (isImage && !ACCEPT_IMAGE_TYPES.includes(file.type)) {
            // HEIC bazı tarayıcılarda image/heic döner; yine de kabul listesine ekledik. Buraya düşerse tür açıkça desteklenmiyor.
            console.log(`Desteklenmeyen görsel tipi: ${file.type}`);
            rejectedFiles.value.push({ file, reason: 'Görsel formatı desteklenmiyor' });
            continue;
        }

        if (isVideo && !ACCEPT_VIDEO_TYPES.includes(file.type)) {
            console.log(`Desteklenmeyen video tipi: ${file.type}`);
            rejectedFiles.value.push({ file, reason: 'Video formatı desteklenmiyor' });
            continue;
        }

        const maxSize = isImage ? MAX_IMAGE_SIZE : MAX_VIDEO_SIZE;
        if (file.size > maxSize) {
            console.log(`Dosya çok büyük: ${formatBytes(file.size)} > ${formatBytes(maxSize)}`);
            rejectedFiles.value.push({ file, reason: `Dosya çok büyük (>${formatBytes(maxSize)})` });
            continue;
        }

        // Aynı isim & boyutlu dosyayı tekrar eklemeyi engelle (isteğe bağlı)
        const duplicate = filePreviews.value.some(p => p.file.name === file.name && p.file.size === file.size);
        if (duplicate) {
            console.log(`Tekrarlanan dosya: ${file.name}`);
            rejectedFiles.value.push({ file, reason: 'Zaten eklendi' });
            continue;
        }

        const type = isVideo ? 'video' : 'image';
        const preview = URL.createObjectURL(file);
        filePreviews.value.push({ file, preview, type });
        form.files.push(file);
    }
};

const removeFile = (index: number) => {
    URL.revokeObjectURL(filePreviews.value[index].preview);
    filePreviews.value.splice(index, 1);
    form.files.splice(index, 1);
};

const uploadFiles = async () => {
    if (!hasUserInfo.value || form.files.length === 0) return;

    isUploading.value = true;
    uploadProgress.value = 0;

    // Dosyaların son durumunu log'a yazdır
    console.log(`Upload başlıyor: ${form.files.length} dosya`);
    form.files.forEach((file, idx) => {
        console.log(`Dosya #${idx + 1}: ${file.name}, tip: ${file.type}, boyut: ${formatBytes(file.size)}`);
    });

    const batchSize = 20; // Must match controller max:20
    const totalFiles = form.files.length;
    const batches = [];

    // Split files into batches
    for (let i = 0; i < totalFiles; i += batchSize) {
        batches.push(form.files.slice(i, i + batchSize));
    }

    console.log(`${batches.length} batch oluşturuldu`);

    try {
        let totalUploadedFiles = 0;

        for (const [batchIndex, batch] of batches.entries()) {
            console.log(`Batch #${batchIndex + 1} gönderiliyor (${batch.length} dosya)`);

            const batchForm = useForm({
                files: batch,
                uploader_name: form.uploader_name,
                uploader_email: form.uploader_email,
            });

            await new Promise((resolve, reject) => {
                batchForm.post(route('events.public.upload', props.event.slug), {
                    onSuccess: (successResponse) => {
                        console.log(`Batch #${batchIndex + 1} başarılı:`, successResponse);
                        totalUploadedFiles += batch.length;
                        uploadProgress.value = (totalUploadedFiles / totalFiles) * 100;
                        resolve(successResponse);
                    },
                    onError: (errors) => {
                        console.error(`Batch #${batchIndex + 1} hata:`, errors);
                        serverFileErrors.value = errors as Record<string, string>;
                        reject(new Error(`Batch ${batchIndex + 1} failed: ${JSON.stringify(errors)}`));
                    }
                });
            });

        }

        // All batches completed successfully
        uploadProgress.value = 100;
        console.log(`Tüm dosyalar başarıyla yüklendi: ${totalUploadedFiles} dosya`);

        // Show success message
        successMessage.value = `${totalUploadedFiles} dosya başarıyla yüklendi! Onay bekliyor.`;
        isErrorMessage.value = false;
        showSuccessMessage.value = true;
        setTimeout(() => {
            showSuccessMessage.value = false;
        }, 5000);

        // Clean up
        filePreviews.value.forEach(item => URL.revokeObjectURL(item.preview));
        filePreviews.value = [];
        form.reset('files');

        setTimeout(() => {
            isUploading.value = false;
            uploadProgress.value = 0;
        }, 1000);

    } catch (error) {
        console.error('Upload başarısız:', error);
        isUploading.value = false;
        uploadProgress.value = 0;

        // Hata detaylarını göster
        console.log('Server hata detayları:', serverFileErrors.value);

        // Show error message
        successMessage.value = 'Upload failed! Please try again.';
        isErrorMessage.value = true;
        showSuccessMessage.value = true;
        setTimeout(() => {
            showSuccessMessage.value = false;
        }, 3000);
    }
};

onMounted(() => {
    // Hiçbir scroll bloklaması yok!
    document.body.style.overflow = '';
    document.documentElement.style.overflow = '';
});
</script>

<template>

    <Head :title="`Upload to ${event.name}`" />

    <div class="min-h-screen bg-gradient-to-br from-blue-50 to-purple-100">
        <!-- Success/Error Alert -->
        <div v-if="showSuccessMessage" :class="{
            'bg-green-500': !isErrorMessage,
            'bg-red-500': isErrorMessage
        }" class="fixed top-4 right-4 text-white px-6 py-4 rounded-lg shadow-lg z-50 flex items-center gap-3">
            <svg v-if="!isErrorMessage" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <span>{{ successMessage }}</span>
            <button @click="showSuccessMessage = false" :class="{
                'hover:bg-green-600': !isErrorMessage,
                'hover:bg-red-600': isErrorMessage
            }" class="ml-2 rounded p-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                    </path>
                </svg>
            </button>
        </div>

        <!-- User Info Modal -->
        <div v-if="showUserModal"
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-40">
            <div class="bg-white rounded-lg shadow-xl w-full max-w-md p-6">
                <div class="text-center mb-6">
                    <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z">
                            </path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                    </div>
                    <h2 class="text-xl font-bold text-gray-900 mb-2">Welcome to {{ event.name }}!</h2>
                    <p class="text-gray-600">Please enter your details to upload photos and videos</p>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Your Name *</label>
                        <input v-model="uploaderInfo.name" type="text" placeholder="Enter your full name"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            required />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email Address *</label>
                        <input v-model="uploaderInfo.email" @input="handleEmailInput" type="email"
                            placeholder="Enter your email" :class="{
                                'border-red-500 focus:ring-red-500': emailError,
                                'border-gray-300 focus:ring-blue-500': !emailError
                            }" class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2" required />
                        <p v-if="emailError" class="text-red-500 text-sm mt-1">{{ emailError }}</p>
                    </div>
                    <button @click="handleUserInfoSubmit" :disabled="!hasUserInfo"
                        class="w-full bg-blue-600 hover:bg-blue-700 disabled:bg-gray-400 text-white font-medium py-2 px-4 rounded-md transition-colors">
                        Continue to Upload
                    </button>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="container mx-auto px-4 py-8">
            <!-- Header -->
            <div class="text-center mb-8">
                <div class="w-20 h-20 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg class="w-10 h-10 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z">
                        </path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                </div>
                <h1 class="text-4xl font-bold text-gray-900 mb-4">{{ event.name }}</h1>
                <div class="flex items-center justify-center gap-6 text-gray-600 mb-4 flex-wrap">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z">
                            </path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        <span>{{ event.location }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                            </path>
                        </svg>
                        <span>{{ event.event_date }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z">
                            </path>
                        </svg>
                        <span>{{ event.photos_count }} photos uploaded</span>
                    </div>
                </div>
                <p class="text-lg text-gray-600 max-w-2xl mx-auto">
                    {{ event.description || 'Share your photos and videos from this amazing event!' }}
                </p>
            </div>

            <!-- Upload Section -->
            <div class="max-w-4xl mx-auto">
                <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                    <div class="p-6 border-b">
                        <div class="flex items-center gap-3">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12">
                                </path>
                            </svg>
                            <h2 class="text-xl font-semibold">Upload Photos & Videos</h2>
                            <span v-if="uploaderInfo.name" class="bg-gray-100 text-gray-800 px-2 py-1 rounded text-sm">
                                {{ uploaderInfo.name }}
                            </span>
                        </div>
                    </div>

                    <div class="p-6 space-y-6">
                        <!-- Drag & Drop Area -->
                        <div @drop="handleDrop" @dragover.prevent="isDragOver = true" @dragleave="isDragOver = false"
                            :class="{
                                'border-blue-400 bg-blue-50': isDragOver,
                                'border-gray-300': !isDragOver
                            }" class="border-2 border-dashed rounded-lg p-8 transition-colors text-center">
                            <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12">
                                </path>
                            </svg>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Drag & drop your files here</h3>
                            <p class="text-gray-500 mb-4">or click to browse files</p>
                            <input type="file" multiple accept="image/*,video/*" @change="handleFileSelect"
                                class="hidden" id="file-input" />
                            <label for="file-input"
                                class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 cursor-pointer">
                                Choose Files
                            </label>
                            <p class="text-xs text-gray-400 mt-2 leading-relaxed">
                                Desteklenen görseller: JPG, PNG, GIF, WEBP, HEIC/HEIF • Max {{
                                    (MAX_IMAGE_SIZE / 1024 / 1024).toFixed(0) }}MB<br>
                                Desteklenen videolar: MP4, MOV (QuickTime), AVI, WMV • Max {{
                                    (MAX_VIDEO_SIZE / 1024 / 1024).toFixed(0) }}MB
                            </p>
                        </div>

                        <!-- Reddedilen Dosyalar -->
                        <div v-if="rejectedFiles.length" class="space-y-2">
                            <div class="flex items-center gap-2 text-red-600 text-sm font-medium">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Eklenemeyen {{ rejectedFiles.length }} dosya:
                            </div>
                            <ul class="text-xs text-red-500 space-y-1 max-h-24 overflow-auto pr-2">
                                <li v-for="(r, i) in rejectedFiles" :key="i" class="flex justify-between gap-2">
                                    <span class="truncate">{{ r.file.name }}</span>
                                    <span class="whitespace-nowrap">- {{ r.reason }}</span>
                                </li>
                            </ul>
                        </div>

                        <!-- File Previews -->
                        <div v-if="filePreviews.length > 0" class="space-y-4">
                            <h4 class="font-medium text-gray-900">Selected Files ({{ filePreviews.length }})</h4>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                <div v-for="(item, index) in filePreviews" :key="index" class="relative group">
                                    <div class="aspect-square rounded-lg overflow-hidden bg-gray-100">
                                        <img v-if="item.type === 'image'" :src="item.preview" :alt="item.file.name"
                                            class="w-full h-full object-cover" />
                                        <div v-else class="w-full h-full flex items-center justify-center">
                                            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z">
                                                </path>
                                            </svg>
                                        </div>
                                    </div>
                                    <button @click="removeFile(index)"
                                        class="absolute -top-2 -right-2 w-6 h-6 bg-red-500 hover:bg-red-600 text-white rounded-full opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </button>
                                    <p class="text-xs text-gray-500 mt-1 truncate">{{ item.file.name }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Upload Progress -->
                        <div v-if="isUploading" class="space-y-2">
                            <div class="flex items-center justify-between text-sm">
                                <span>Uploading...</span>
                                <span>{{ Math.round(uploadProgress) }}%</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-blue-600 h-2 rounded-full transition-all duration-300"
                                    :style="{ width: uploadProgress + '%' }"></div>
                            </div>
                        </div>

                        <!-- Error Messages -->
                        <div v-if="form.errors.files || Object.keys(serverFileErrors).length" class="space-y-2">
                            <div v-if="form.errors.files" class="flex items-center gap-2 text-red-600 text-sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                {{ form.errors.files }}
                            </div>
                            <div v-if="Object.keys(serverFileErrors).length"
                                class="text-xs text-red-600 bg-red-50 border border-red-200 rounded p-2 max-h-40 overflow-auto">
                                <ul class="space-y-1">
                                    <li v-for="(msg, key) in serverFileErrors" :key="key">
                                        <strong>{{ key }}:</strong> {{ msg }}
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <!-- Upload Button -->
                        <div class="flex justify-center pt-4">
                            <button @click="uploadFiles"
                                :disabled="!hasUserInfo || form.files.length === 0 || isUploading"
                                class="bg-blue-600 hover:bg-blue-700 disabled:bg-gray-400 text-white font-medium py-3 px-8 rounded-md transition-colors flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7"></path>
                                </svg>
                                Upload {{ form.files.length }} File{{ form.files.length !== 1 ? 's' : '' }}
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Help Section -->
                <div class="mt-6 bg-blue-50 rounded-lg p-6">
                    <div class="text-center space-y-3">
                        <h3 class="font-medium text-gray-900">📸 Share Your Memories</h3>
                        <p class="text-sm text-gray-600">
                            All uploaded files will be reviewed before appearing in the gallery.
                            Thank you for contributing to {{ event.name }}!
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
