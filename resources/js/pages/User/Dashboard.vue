<script setup lang="ts">
import AppHeaderLayout from '@/layouts/app/AppHeaderLayout.vue';
import FeatureDiscovery from '@/components/FeatureDiscovery.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { ref, onMounted, computed } from 'vue';
import {
    Camera,
    Upload,
    Calendar,
    Star,
    TrendingUp,
    Clock,
    Heart,
    Download,
    Share2,
    Plus,
    Check,
    MapPin
} from 'lucide-vue-next';

const page = usePage();
const user = page.props.auth?.user;
const showFeatureDiscovery = page.props.showFeatureDiscovery;
const recentEvents = page.props.recentEvents || [];
const stats = page.props.stats || {};
const featureDiscoveryRef = ref<{ startTour: () => void } | null>(null);

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Kontrol Paneli',
        href: '/user/dashboard',
    },
];

// Stats configuration with real data
const statsConfig = computed(() => [
    { label: 'Toplam Etkinlik', value: stats.total_events || 0, icon: Calendar, color: 'text-blue-600', bgColor: 'bg-blue-100' },
    { label: 'Yüklenmiş Fotoğraf', value: stats.total_photos || 0, icon: Camera, color: 'text-green-600', bgColor: 'bg-green-100' },
    { label: 'Onaylanmış Fotoğraf', value: stats.approved_photos || 0, icon: Check, color: 'text-purple-600', bgColor: 'bg-purple-100' },
    { label: 'Bekleyen Fotoğraf', value: stats.pending_photos || 0, icon: Clock, color: 'text-orange-600', bgColor: 'bg-orange-100' }
]);

const quickActions = [
    { title: 'Fotoğraf Yükle', desc: 'Etkinliklerinize yeni fotoğraflar ekleyin', icon: Upload, action: 'upload', href: route('user.events.index') },
    { title: 'Etkinlik Oluştur', desc: 'Yeni bir fotoğraf koleksiyonu başlatın', icon: Plus, action: 'create', href: route('user.events.create') },
    { title: 'Galeri Paylaş', desc: 'Fotoğraflarınızı başkalarıyla paylaşın', icon: Share2, action: 'share', href: route('user.events.index') }
];

const featureDiscoverySteps = [
    {
        element: '[data-intro="dashboard"]',
        intro: "Kontrol panelinize hoş geldiniz! Buradan tüm etkinlik istatistiklerinizi ve son aktivitelerinizi görebilirsiniz.",
        position: 'bottom'
    },
    {
        element: '[data-intro="events"]',
        intro: "Buradan tüm etkinliklerinizi görebilir ve yönetebilirsiniz. Etkinlik anılarınız burada yaşar!",
        position: 'right'
    },
    {
        element: '[data-intro="quick-actions"]',
        intro: "Bu hızlı işlemlerle fotoğraf yükleyebilir, yeni etkinlik oluşturabilir veya galerilerinizi paylaşabilirsiniz.",
        position: 'left'
    },
    {
        element: '[data-intro="profile"]',
        intro: "Üst menüden profil ayarlarınıza ve hesap tercihlerinize erişebilirsiniz.",
        position: 'bottom'
    }
];

onMounted(() => {
    if (showFeatureDiscovery) {
        setTimeout(() => {
            featureDiscoveryRef.value?.startTour();
        }, 1500);
    }
});
</script>

<template>
    <Head title="Kontrol Paneli" />

    <AppHeaderLayout :breadcrumbs="breadcrumbs">
        <!-- Feature Discovery Component -->
        <FeatureDiscovery
            ref="featureDiscoveryRef"
            :steps="featureDiscoverySteps"
            :autoStart="false"
        />

        <!-- Hero Section -->
<div class="mt-8 bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50
            dark:from-gray-900 dark:via-blue-900 dark:to-purple-900
            rounded-2xl p-8 mb-8" data-intro="dashboard">            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">
                        Hoş geldin, {{ user?.name || 'Kullanıcı' }}! 👋
                    </h1>
                    <p class="text-lg text-gray-600 dark:text-gray-300 mb-6">
                        {{ stats.total_events > 0 ? `Toplam ${stats.total_events} etkinliğiniz ve ${stats.total_photos} fotoğrafınız var!` : 'Anılarınızı yakalamaya ve paylaşmaya hazır mısınız?' }}
                    </p>
                    <Link :href="route('user.events.create')">
                        <Button class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-6 py-3 rounded-lg shadow-md hover:shadow-lg transition-all duration-200 inline-flex items-center gap-2">
                            <Plus class="h-5 w-5" />
                            {{ stats.total_events > 0 ? 'Yeni Etkinlik Oluştur' : 'İlk Etkinliğinizi Oluşturun' }}
                        </Button>
                    </Link>
                </div>
                <div class="hidden md:block">
                    <div class="relative">
                        <Camera class="h-24 w-24 text-blue-500 opacity-20" />
                        <div class="absolute inset-0 flex items-center justify-center">
                            <div class="h-12 w-12 bg-blue-500 rounded-full flex items-center justify-center">
                                <Camera class="h-6 w-6 text-white" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-4 mb-8">
            <Card v-for="stat in statsConfig" :key="stat.label" class="hover:shadow-lg transition-shadow">
                <CardContent class="p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">{{ stat.label }}</p>
                            <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">{{ stat.value }}</p>
                        </div>
                        <div :class="`${stat.bgColor} p-3 rounded-full`">
                            <component :is="stat.icon" :class="`h-6 w-6 ${stat.color}`" />
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Main Content Grid -->
        <div class="grid gap-8 lg:grid-cols-3">
            <!-- Recent Events -->
            <div class="lg:col-span-2" data-intro="events">
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <Calendar class="h-5 w-5 text-blue-600" />
                            Son Etkinlikler
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-4">
                            <div v-if="recentEvents.length === 0" class="text-center py-8">
                                <Camera class="mx-auto h-12 w-12 text-gray-400 mb-4" />
                                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No events yet</h3>
                                <p class="text-gray-500 dark:text-gray-400 mb-4">Create your first event to start collecting photos</p>
                                <Link :href="route('user.events.create')">
                                    <Button class="bg-blue-600 hover:bg-blue-700">
                                        <Plus class="w-4 h-4 mr-2" />
                                        Create First Event
                                    </Button>
                                </Link>
                            </div>
                            <div v-else>
                                <div
                                    v-for="event in recentEvents"
                                    :key="event.id"
                                    class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-800 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors cursor-pointer"
                                    @click="$inertia.visit(route('user.events.show', event.id))"
                                >
                                    <div class="flex items-center gap-4">
                                        <div class="h-12 w-12 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center">
                                            <Camera class="h-6 w-6 text-white" />
                                        </div>
                                        <div>
                                            <h4 class="font-semibold text-gray-900 dark:text-white">{{ event.name }}</h4>
                                            <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                                                <MapPin class="h-3 w-3" />
                                                <span>{{ event.location }}</span>
                                                <span>•</span>
                                                <span>{{ event.formatted_date }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <div class="text-right">
                                            <div class="text-sm font-medium text-gray-900 dark:text-white">{{ event.photos_count }} photos</div>
                                            <div class="text-xs text-gray-500">{{ event.approved_photos_count }} approved</div>
                                        </div>
                                        <Badge
                                            :variant="event.status === 'published' ? 'default' : event.status === 'draft' ? 'secondary' : 'outline'"
                                        >
                                            {{ event.status }}
                                        </Badge>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mt-6">
                            <Link :href="route('user.events.index')">
                                <Button class="w-full" variant="outline">
                                    Tüm Etkinlikleri Görüntüle
                                </Button>
                            </Link>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Quick Actions & Activity -->
            <div class="space-y-6">
                <!-- Quick Actions -->
                <Card data-intro="quick-actions">
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <TrendingUp class="h-5 w-5 text-green-600" />
                           Hızlı İşlemler
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-3">
                            <Link
                                v-for="action in quickActions"
                                :key="action.action"
                                :href="action.href"
                            >
                                <Button
                                    variant="outline"
                                    class="w-full justify-start gap-3 h-auto p-4"
                                >
                                    <component :is="action.icon" class="h-5 w-5 text-blue-600" />
                                    <div class="text-left">
                                        <div class="font-medium">{{ action.title }}</div>
                                        <div class="text-xs text-gray-500">{{ action.desc }}</div>
                                    </div>
                                </Button>
                            </Link>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>

        <!-- Featured Section -->
        <div class="mt-8">
            <Card class="bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500 text-white border-none">
                <CardContent class="p-8">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-2xl font-bold mb-2">Pro'ya Geçiş Yap</h3>
                            <p class="text-indigo-100 mb-4">Sınırsız yükleme, premium özellikler ve öncelikli destek alın.</p>
                            <Button variant="secondary" class="bg-white text-indigo-600 hover:bg-gray-100">
                                Planları Görüntüle
                            </Button>
                        </div>
                        <div class="hidden md:block">
                            <Star class="h-20 w-20 text-yellow-300 opacity-50" />
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppHeaderLayout>
</template>
