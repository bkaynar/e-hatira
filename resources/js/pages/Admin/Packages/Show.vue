<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Edit, ArrowLeft } from 'lucide-vue-next';

interface Package {
    id: number;
    name: string;
    slug: string;
    price: number | string;
    currency: string;
    max_uploads: number | null;
    storage_days: number;
    upload_days: number;
    quality: 'normal' | 'high';
    advanced_customization: boolean;
    features: string[] | null;
    is_active: boolean;
    created_at: string;
    updated_at: string;
}

interface Props {
    package: Package;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Admin Dashboard',
        href: '/admin/dashboard',
    },
    {
        title: 'Packages',
        href: '/admin/packages',
    },
    {
        title: props.package.name,
        href: `/admin/packages/${props.package.id}`,
    },
];

const formatPrice = (price: number | string, currency: string) => {
    const numericPrice = typeof price === 'string' ? parseFloat(price) : price;
    return `${numericPrice.toFixed(2)} ${currency}`;
};

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('tr-TR', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};
</script>

<template>
    <Head :title="props.package.name" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-foreground">{{ props.package.name }}</h1>
                    <p class="text-muted-foreground">Package details and information</p>
                </div>
                <div class="flex gap-2">
                    <Link href="/admin/packages">
                        <Button variant="outline">
                            <ArrowLeft class="h-4 w-4 mr-2" />
                            Back to Packages
                        </Button>
                    </Link>
                    <Link :href="`/admin/packages/${props.package.id}/edit`">
                        <Button>
                            <Edit class="h-4 w-4 mr-2" />
                            Edit Package
                        </Button>
                    </Link>
                </div>
            </div>

            <div class="grid gap-6 md:grid-cols-2">
                <Card>
                    <CardHeader>
                        <CardTitle>Basic Information</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="flex justify-between">
                            <span class="font-medium">Name:</span>
                            <span>{{ props.package.name }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="font-medium">Slug:</span>
                            <span class="font-mono text-sm">{{ props.package.slug }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="font-medium">Price:</span>
                            <span class="font-semibold">{{ formatPrice(props.package.price, props.package.currency) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="font-medium">Status:</span>
                            <Badge :variant="props.package.is_active ? 'success' : 'secondary'">
                                {{ props.package.is_active ? 'Active' : 'Inactive' }}
                            </Badge>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Limits & Settings</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="flex justify-between">
                            <span class="font-medium">Max Uploads:</span>
                            <span>{{ props.package.max_uploads || 'Unlimited' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="font-medium">Storage Days:</span>
                            <span>{{ props.package.storage_days }} days</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="font-medium">Upload Days:</span>
                            <span>{{ props.package.upload_days }} days</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="font-medium">Quality:</span>
                            <Badge :variant="props.package.quality === 'high' ? 'default' : 'secondary'">
                                {{ props.package.quality }}
                            </Badge>
                        </div>
                        <div class="flex justify-between">
                            <span class="font-medium">Advanced Customization:</span>
                            <Badge :variant="props.package.advanced_customization ? 'success' : 'secondary'">
                                {{ props.package.advanced_customization ? 'Enabled' : 'Disabled' }}
                            </Badge>
                        </div>
                    </CardContent>
                </Card>

                <Card v-if="props.package.features && props.package.features.length > 0" class="md:col-span-2">
                    <CardHeader>
                        <CardTitle>Features</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <ul class="list-disc list-inside space-y-2">
                            <li v-for="feature in props.package.features" :key="feature" class="text-sm">
                                {{ feature }}
                            </li>
                        </ul>
                    </CardContent>
                </Card>

                <Card class="md:col-span-2">
                    <CardHeader>
                        <CardTitle>Timestamps</CardTitle>
                    </CardHeader>
                    <CardContent class="grid gap-4 md:grid-cols-2">
                        <div class="flex justify-between">
                            <span class="font-medium">Created:</span>
                            <span class="text-sm">{{ formatDate(props.package.created_at) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="font-medium">Last Updated:</span>
                            <span class="text-sm">{{ formatDate(props.package.updated_at) }}</span>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>