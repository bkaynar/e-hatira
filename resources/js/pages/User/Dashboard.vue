<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import PlaceholderPattern from '../../components/PlaceholderPattern.vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'User Dashboard',
        href: '/user/dashboard',
    },
];
</script>

<template>
    <Head title="User Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 overflow-x-auto">
            <div class="mb-4">
                <h1 class="text-2xl font-bold text-foreground">User Panel</h1>
                <p class="text-muted-foreground">Welcome to your dashboard</p>
            </div>
            
            <div class="grid auto-rows-min gap-4 md:grid-cols-3">
                <div class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                    <div class="p-4">
                        <h3 class="font-semibold">My Profile</h3>
                        <p class="text-sm text-muted-foreground">View and edit profile</p>
                    </div>
                    <PlaceholderPattern />
                </div>
                <div class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                    <div class="p-4">
                        <h3 class="font-semibold">My Events</h3>
                        <p class="text-sm text-muted-foreground">Manage your events</p>
                    </div>
                    <PlaceholderPattern />
                </div>
                <div class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                    <div class="p-4">
                        <h3 class="font-semibold">Gallery</h3>
                        <p class="text-sm text-muted-foreground">View your photos</p>
                    </div>
                    <PlaceholderPattern />
                </div>
            </div>
            <div class="relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border">
                <div class="p-4">
                    <h3 class="font-semibold mb-2">Recent Activity</h3>
                    <p class="text-muted-foreground">Your recent activities and notifications will be displayed here.</p>
                </div>
                <PlaceholderPattern />
            </div>
        </div>
    </AppLayout>
</template>