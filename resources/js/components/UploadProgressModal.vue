<script setup lang="ts">
import { computed, ref, watch } from 'vue';

interface Props {
  isOpen: boolean;
  uploadProgress: number;
  isUploading: boolean;
  currentFileName?: string;
  totalFiles: number;
  uploadedFiles: number;
  currentBatch: number;
  totalBatches: number;
}

const props = defineProps<Props>();

const emit = defineEmits<{
  close: [];
  cancel: [];
}>();

const progressPercentage = computed(() => Math.round(props.uploadProgress));
const showCancelButton = computed(() => props.isUploading && props.uploadProgress < 100);

const getProgressText = () => {
  if (!props.isUploading) return '';
  if (props.totalBatches > 1) {
    return `Batch ${props.currentBatch}/${props.totalBatches} - ${props.uploadedFiles}/${props.totalFiles} dosya yüklendi`;
  }
  return `${props.uploadedFiles}/${props.totalFiles} dosya yüklendi`;
};

const getProgressColor = () => {
  if (props.uploadProgress === 100) return 'bg-green-500';
  if (props.uploadProgress > 0) return 'bg-blue-500';
  return 'bg-gray-300';
};

const animationClass = ref('');

watch(() => props.uploadProgress, (newProgress) => {
  if (newProgress === 100) {
    animationClass.value = 'animate-pulse';
    setTimeout(() => {
      animationClass.value = '';
    }, 2000);
  }
});
</script>

<template>
  <div v-if="isOpen" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-lg shadow-2xl w-full max-w-md p-6 transform transition-all duration-300 scale-100">
      <!-- Header -->
      <div class="text-center mb-6">
        <div class="w-16 h-16 mx-auto mb-4 relative">
          <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center">
            <svg v-if="uploadProgress < 100" class="w-8 h-8 text-blue-600 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
            </svg>
            <svg v-else class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
          </div>
          <!-- Pulsing ring for active upload -->
          <div v-if="isUploading && uploadProgress < 100" class="absolute inset-0 bg-blue-400 rounded-full animate-ping opacity-25"></div>
        </div>
        
        <h3 class="text-xl font-semibold text-gray-900 mb-2">
          {{ uploadProgress === 100 ? 'Yükleme Tamamlandı!' : 'Dosyalar Yükleniyor...' }}
        </h3>
        
        <p class="text-gray-600 text-sm">
          {{ getProgressText() }}
        </p>
        
        <p v-if="currentFileName && uploadProgress < 100" class="text-gray-500 text-xs mt-1 truncate">
          {{ currentFileName }}
        </p>
      </div>

      <!-- Progress Bar -->
      <div class="mb-6">
        <div class="flex items-center justify-between text-sm mb-2">
          <span class="text-gray-600">İlerleme</span>
          <span class="font-medium text-gray-900">{{ progressPercentage }}%</span>
        </div>
        
        <div class="w-full bg-gray-200 rounded-full h-3 overflow-hidden">
          <div 
            :class="[getProgressColor(), animationClass]"
            class="h-3 rounded-full transition-all duration-500 ease-out relative overflow-hidden"
            :style="{ width: `${uploadProgress}%` }"
          >
            <!-- Animated shine effect -->
            <div v-if="isUploading && uploadProgress < 100" class="absolute inset-0 bg-gradient-to-r from-transparent via-white via-transparent to-transparent opacity-30 animate-pulse"></div>
          </div>
        </div>
      </div>

      <!-- File Details -->
      <div v-if="totalFiles > 0" class="bg-gray-50 rounded-lg p-4 mb-6">
        <div class="grid grid-cols-2 gap-4 text-sm">
          <div class="text-center">
            <div class="font-semibold text-gray-900">{{ uploadedFiles }}</div>
            <div class="text-gray-600">Yüklendi</div>
          </div>
          <div class="text-center">
            <div class="font-semibold text-gray-900">{{ totalFiles }}</div>
            <div class="text-gray-600">Toplam</div>
          </div>
        </div>
        
        <div v-if="totalBatches > 1" class="mt-3 pt-3 border-t border-gray-200">
          <div class="text-center text-xs text-gray-500">
            Batch {{ currentBatch }} / {{ totalBatches }}
          </div>
        </div>
      </div>

      <!-- Success Message -->
      <div v-if="uploadProgress === 100" class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
        <div class="flex items-center">
          <svg class="w-5 h-5 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
          </svg>
          <span class="text-green-800 text-sm font-medium">
            Tüm dosyalar başarıyla yüklendi!
          </span>
        </div>
        <p class="text-green-700 text-xs mt-1 ml-7">
          Dosyalarınız onay bekliyor ve yakında galeriye eklenecek.
        </p>
      </div>

      <!-- Action Buttons -->
      <div class="flex gap-3">
        <button 
          v-if="showCancelButton"
          @click="emit('cancel')"
          class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 transition-colors text-sm font-medium"
        >
          İptal Et
        </button>
        
        <button 
          v-if="uploadProgress === 100"
          @click="emit('close')"
          class="flex-1 px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors text-sm font-medium"
        >
          Tamam
        </button>
        
        <button 
          v-else-if="!isUploading"
          @click="emit('close')"
          class="flex-1 px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 transition-colors text-sm font-medium"
        >
          Kapat
        </button>
      </div>
    </div>
  </div>
</template>

<style scoped>
@keyframes fadeIn {
  from { opacity: 0; transform: scale(0.95); }
  to { opacity: 1; transform: scale(1); }
}

.animate-fadeIn {
  animation: fadeIn 0.3s ease-out;
}
</style>