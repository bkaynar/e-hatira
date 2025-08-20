<script setup lang="ts">
import AppHeaderLayout from '@/layouts/app/AppHeaderLayout.vue';
import FeatureDiscovery from '@/components/FeatureDiscovery.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { ref, onMounted } from 'vue';
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
    Plus
} from 'lucide-vue-next';

const page = usePage();
const user = page.props.auth?.user;
const showFeatureDiscovery = page.props.showFeatureDiscovery;
const featureDiscoveryRef = ref<{ startTour: () => void } | null>(null);

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/user/dashboard',
    },
];

// Mock data - bu veriler gerçek API'dan gelecek
const stats = [
    { label: 'Total Events', value: '12', icon: Calendar, color: 'text-blue-600', bgColor: 'bg-blue-100' },
    { label: 'Photos Uploaded', value: '234', icon: Camera, color: 'text-green-600', bgColor: 'bg-green-100' },
    { label: 'Downloads', value: '1,456', icon: Download, color: 'text-purple-600', bgColor: 'bg-purple-100' },
    { label: 'Likes Received', value: '89', icon: Heart, color: 'text-red-600', bgColor: 'bg-red-100' }
];

const recentEvents = [
    { id: 1, name: 'Wedding Photography', date: '2024-01-15', photos: 45, status: 'completed' },
    { id: 2, name: 'Birthday Party', date: '2024-01-20', photos: 28, status: 'processing' },
    { id: 3, name: 'Corporate Event', date: '2024-01-25', photos: 67, status: 'active' }
];

const quickActions = [
    { title: 'Upload Photos', desc: 'Add new photos to your events', icon: Upload, action: 'upload', href: route('user.events.index') },
    { title: 'Create Event', desc: 'Start a new photo collection', icon: Plus, action: 'create', href: route('user.events.create') },
    { title: 'Share Gallery', desc: 'Share your photos with others', icon: Share2, action: 'share', href: route('user.events.index') }
];

const featureDiscoverySteps = [
    {
        element: '[data-intro="dashboard"]',
        intro: "Welcome to your dashboard! This is your control center where you can see all your event statistics and recent activity.",
        position: 'bottom'
    },
    {
        element: '[data-intro="events"]',
        intro: "Here you can view and manage all your events. This is where your event memories live!",
        position: 'right'
    },
    {
        element: '[data-intro="quick-actions"]',
        intro: "Use these quick actions to upload photos, create new events, or share your galleries with others.",
        position: 'left'
    },
    {
        element: '[data-intro="profile"]',
        intro: "Access your profile settings and account preferences from the top navigation.",
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
    <Head title="Dashboard" />

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
                        Welcome back, {{ user?.name || 'User' }}! 👋
                    </h1>
                    <p class="text-lg text-gray-600 dark:text-gray-300 mb-6">
                        Ready to capture and share your moments?
                    </p>
                    <Button 
                        :href="route('user.events.create')"
                        as="Link"
                        class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-6 py-3 rounded-lg shadow-md hover:shadow-lg transition-all duration-200 inline-flex items-center gap-2"
                    >
                        <Plus class="h-5 w-5" />
                        Create Your First Event
                    </Button>
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
            <Card v-for="stat in stats" :key="stat.label" class="hover:shadow-lg transition-shadow">
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
                            Recent Events
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-4">
                            <div
                                v-for="event in recentEvents"
                                :key="event.id"
                                class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-800 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                            >
                                <div class="flex items-center gap-4">
                                    <div class="h-10 w-10 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center">
                                        <Camera class="h-5 w-5 text-white" />
                                    </div>
                                    <div>
                                        <h4 class="font-semibold text-gray-900 dark:text-white">{{ event.name }}</h4>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ event.date }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3">
                                    <span class="text-sm text-gray-600 dark:text-gray-400">{{ event.photos }} photos</span>
                                    <Badge
                                        :variant="event.status === 'completed' ? 'success' : event.status === 'processing' ? 'secondary' : 'default'"
                                    >
                                        {{ event.status }}
                                    </Badge>
                                </div>
                            </div>
                        </div>
                        <div class="mt-6">
                            <Button 
                                :href="route('user.events.index')"
                                as="Link"
                                class="w-full" 
                                variant="outline"
                            >
                                View All Events
                            </Button>
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
                            Quick Actions
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-3">
                            <Button
                                v-for="action in quickActions"
                                :key="action.action"
                                :href="action.href"
                                as="Link"
                                variant="outline"
                                class="w-full justify-start gap-3 h-auto p-4"
                            >
                                <component :is="action.icon" class="h-5 w-5 text-blue-600" />
                                <div class="text-left">
                                    <div class="font-medium">{{ action.title }}</div>
                                    <div class="text-xs text-gray-500">{{ action.desc }}</div>
                                </div>
                            </Button>
                        </div>
                    </CardContent>
                </Card>

                <!-- Recent Activity -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <Clock class="h-5 w-5 text-orange-600" />
                            Recent Activity
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-4 text-sm">
                            <div class="flex items-start gap-3">
                                <div class="h-2 w-2 bg-green-500 rounded-full mt-2 flex-shrink-0"></div>
                                <div>
                                    <p class="text-gray-900 dark:text-white">New photos uploaded</p>
                                    <p class="text-gray-500 text-xs">2 hours ago</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3">
                                <div class="h-2 w-2 bg-blue-500 rounded-full mt-2 flex-shrink-0"></div>
                                <div>
                                    <p class="text-gray-900 dark:text-white">Event shared with guests</p>
                                    <p class="text-gray-500 text-xs">5 hours ago</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3">
                                <div class="h-2 w-2 bg-purple-500 rounded-full mt-2 flex-shrink-0"></div>
                                <div>
                                    <p class="text-gray-900 dark:text-white">Received 12 new likes</p>
                                    <p class="text-gray-500 text-xs">1 day ago</p>
                                </div>
                            </div>
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
                            <h3 class="text-2xl font-bold mb-2">Upgrade to Pro</h3>
                            <p class="text-indigo-100 mb-4">Get unlimited uploads, premium features, and priority support.</p>
                            <Button variant="secondary" class="bg-white text-indigo-600 hover:bg-gray-100">
                                View Plans
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
