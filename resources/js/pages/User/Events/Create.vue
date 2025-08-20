<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

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

function submitForm() {
    form.post('/user/events', {
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
    
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="max-w-4xl mx-auto">
            <Card>
                <CardHeader>
                    <CardTitle>Create New Event</CardTitle>
                </CardHeader>
                
                <CardContent>
                    <form @submit.prevent="submitForm" class="space-y-6">
                        <!-- Event Name -->
                        <div class="space-y-2">
                            <Label for="name">Event Name</Label>
                            <Input
                                id="name"
                                v-model="form.name"
                                type="text"
                                required
                                :class="{ 'border-red-500': form.errors.name }"
                            />
                            <div v-if="form.errors.name" class="text-red-500 text-sm">
                                {{ form.errors.name }}
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="space-y-2">
                            <Label for="description">Description</Label>
                            <textarea
                                id="description"
                                v-model="form.description"
                                rows="4"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                :class="{ 'border-red-500': form.errors.description }"
                            />
                            <div v-if="form.errors.description" class="text-red-500 text-sm">
                                {{ form.errors.description }}
                            </div>
                        </div>

                        <!-- Location -->
                        <div class="space-y-2">
                            <Label for="location">Location</Label>
                            <Input
                                id="location"
                                v-model="form.location"
                                type="text"
                                required
                                :class="{ 'border-red-500': form.errors.location }"
                            />
                            <div v-if="form.errors.location" class="text-red-500 text-sm">
                                {{ form.errors.location }}
                            </div>
                        </div>

                        <!-- Date and Time -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <Label for="event_date">Event Date</Label>
                                <Input
                                    id="event_date"
                                    v-model="form.event_date"
                                    type="date"
                                    required
                                    :class="{ 'border-red-500': form.errors.event_date }"
                                />
                                <div v-if="form.errors.event_date" class="text-red-500 text-sm">
                                    {{ form.errors.event_date }}
                                </div>
                            </div>

                            <div class="space-y-2">
                                <Label for="event_time">Event Time (Optional)</Label>
                                <Input
                                    id="event_time"
                                    v-model="form.event_time"
                                    type="time"
                                    :class="{ 'border-red-500': form.errors.event_time }"
                                />
                                <div v-if="form.errors.event_time" class="text-red-500 text-sm">
                                    {{ form.errors.event_time }}
                                </div>
                            </div>
                        </div>

                        <!-- Package Selection -->
                        <div class="space-y-2">
                            <Label for="package_id">Package</Label>
                            <select
                                id="package_id"
                                v-model="form.package_id"
                                required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                :class="{ 'border-red-500': form.errors.package_id }"
                            >
                                <option value="">Select a package...</option>
                                <option
                                    v-for="pkg in packages"
                                    :key="pkg.id"
                                    :value="pkg.id"
                                >
                                    {{ pkg.name }} - {{ pkg.price }} {{ pkg.currency }}
                                    ({{ pkg.max_uploads ? pkg.max_uploads + ' uploads' : 'Unlimited' }})
                                </option>
                            </select>
                            <div v-if="form.errors.package_id" class="text-red-500 text-sm">
                                {{ form.errors.package_id }}
                            </div>
                        </div>

                        <!-- Cover Image -->
                        <div class="space-y-2">
                            <Label for="image">Cover Image (Optional)</Label>
                            <input
                                id="image"
                                type="file"
                                accept="image/*"
                                @change="(e) => form.image = (e.target as HTMLInputElement).files?.[0] || null"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                :class="{ 'border-red-500': form.errors.image }"
                            />
                            <div v-if="form.errors.image" class="text-red-500 text-sm">
                                {{ form.errors.image }}
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="flex justify-end space-x-4">
                            <Button
                                type="button"
                                variant="outline"
                                @click="$inertia.visit('/user/events')"
                            >
                                Cancel
                            </Button>
                            
                            <Button
                                type="submit"
                                :disabled="form.processing"
                                class="bg-blue-600 hover:bg-blue-700"
                            >
                                <span v-if="form.processing">Creating...</span>
                                <span v-else>Create Event</span>
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>