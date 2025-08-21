<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';

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
    photos_count?: number;
}

interface PaginatedEvents {
    data: Event[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number;
    to: number;
}

interface Props {
    events: PaginatedEvents;
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
];

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

function getStatusText(status: string) {
    switch (status) {
        case 'published':
            return 'Published';
        case 'draft':
            return 'Draft';
        case 'cancelled':
            return 'Cancelled';
        default:
            return status;
    }
}

function formatDate(dateString: string) {
    return new Date(dateString).toLocaleDateString('tr-TR');
}
</script>

<template>
    <Head title="Events" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 m-6">
            <!-- Header -->
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">Etkinliklerim</h1>
                    <p class="text-muted-foreground">Etkinliklerinizi yönetin ve paylaşın.</p>
                </div>

                <Link href="/user/events/create">
                    <Button class="bg-blue-600 hover:bg-blue-700">
                        Yeni Etkinlik Oluştur
                    </Button>
                </Link>
            </div>

            <!-- Events List -->
            <div class="grid gap-6">
                <div v-if="events.data.length === 0" class="text-center py-8">
                    <p class="text-muted-foreground">No events found.</p>
                    <Link href="/user/events/create">
                        <Button class="mt-4 bg-blue-600 hover:bg-blue-700">
                            Yeni Etkinlik Oluştur
                        </Button>
                    </Link>
                </div>

                <Card v-for="event in events.data" :key="event.id">
                    <CardContent class="p-6">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-2">
                                    <h3 class="text-xl font-semibold">{{ event.name }}</h3>
                                    <Badge :class="getStatusColor(event.status)">
                                        {{ getStatusText(event.status) }}
                                    </Badge>
                                </div>

                                <p v-if="event.description" class="text-muted-foreground mb-3">
                                    {{ event.description }}
                                </p>

                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm text-muted-foreground">
                                    <div>
                                        <strong>Konum:</strong> {{ event.location }}
                                    </div>
                                    <div>
                                        <strong>Tarih:</strong> {{ formatDate(event.event_date) }}
                                        <span v-if="event.event_time"> at {{ event.event_time }}</span>
                                    </div>
                                    <div>
                                        <strong>Paket:</strong> {{ event.package.name }}
                                    </div>
                                </div>
                            </div>

                            <div v-if="event.image" class="ml-4">
                                <img
                                    :src="`/storage/${event.image}`"
                                    :alt="event.name"
                                    class="w-24 h-24 object-cover rounded-lg"
                                />
                            </div>
                        </div>

                        <div class="flex justify-between items-center mt-6 pt-4 border-t">
                            <div class="text-sm text-muted-foreground">
                                Oluşturma Tarihi: {{ formatDate(event.created_at) }}
                                <span v-if="event.photos_count">
                                    • {{ event.photos_count }} photos
                                </span>
                            </div>

                            <div class="flex gap-2">
                                <Link :href="`/user/events/${event.id}`">
                                    <Button variant="outline" size="sm">
                                        Görüntüle
                                    </Button>
                                </Link>
                                <Link :href="`/user/events/${event.id}/edit`">
                                    <Button variant="outline" size="sm">
                                        Düzenle
                                    </Button>
                                </Link>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Pagination -->
            <div v-if="events.last_page > 1" class="flex justify-center">
                <div class="flex space-x-2">
                    <Link
                        v-if="events.current_page > 1"
                        :href="`/user/events?page=${events.current_page - 1}`"
                    >
                        <Button variant="outline">Previous</Button>
                    </Link>

                    <template v-for="page in events.last_page" :key="page">
                        <Link
                            v-if="Math.abs(page - events.current_page) <= 2"
                            :href="`/user/events?page=${page}`"
                        >
                            <Button
                                :variant="page === events.current_page ? 'default' : 'outline'"
                            >
                                {{ page }}
                            </Button>
                        </Link>
                    </template>

                    <Link
                        v-if="events.current_page < events.last_page"
                        :href="`/user/events?page=${events.current_page + 1}`"
                    >
                        <Button variant="outline">Next</Button>
                    </Link>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
