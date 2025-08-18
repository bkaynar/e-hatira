<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import Swal from 'sweetalert2';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Trash2, Edit, Plus, Eye } from 'lucide-vue-next';

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
    packages: {
        data: Package[];
        links: any[];
        meta: any;
    };
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
];

const deletePackage = (id: number, packageName: string) => {
    Swal.fire({
        title: 'Are you sure?',
        text: `You are about to delete "${packageName}" package. This action cannot be undone!`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(`/admin/packages/${id}`, {
                onSuccess: () => {
                    Swal.fire({
                        title: 'Deleted!',
                        text: 'Package has been deleted successfully.',
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false
                    });
                },
                onError: () => {
                    Swal.fire({
                        title: 'Error!',
                        text: 'Something went wrong while deleting the package.',
                        icon: 'error'
                    });
                }
            });
        }
    });
};

const formatPrice = (price: number | string, currency: string) => {
    const numericPrice = typeof price === 'string' ? parseFloat(price) : price;
    return `${numericPrice.toFixed(2)} ${currency}`;
};
</script>

<template>
    <Head title="Package Management" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-foreground">Package Management</h1>
                    <p class="text-muted-foreground">Manage subscription packages</p>
                </div>
                <Link href="/admin/packages/create">
                    <Button class="flex items-center gap-2">
                        <Plus class="h-4 w-4" />
                        Create Package
                    </Button>
                </Link>
            </div>

            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                <Card v-for="pkg in props.packages.data" :key="pkg.id">
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-lg font-medium">{{ pkg.name }}</CardTitle>
                        <div class="flex items-center gap-2">
                            <Badge :variant="pkg.is_active ? 'success' : 'secondary'">
                                {{ pkg.is_active ? 'Active' : 'Inactive' }}
                            </Badge>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span class="text-sm text-muted-foreground">Price:</span>
                                <span class="font-semibold">{{ formatPrice(pkg.price, pkg.currency) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-muted-foreground">Max Uploads:</span>
                                <span class="text-sm">{{ pkg.max_uploads || 'Unlimited' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-muted-foreground">Storage Days:</span>
                                <span class="text-sm">{{ pkg.storage_days }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-muted-foreground">Upload Days:</span>
                                <span class="text-sm">{{ pkg.upload_days }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-muted-foreground">Quality:</span>
                                <Badge :variant="pkg.quality === 'high' ? 'default' : 'secondary'">
                                    {{ pkg.quality }}
                                </Badge>
                            </div>
                        </div>
                        
                        <div class="mt-4 flex gap-2">
                            <Link :href="`/admin/packages/${pkg.id}`">
                                <Button variant="outline" size="sm">
                                    <Eye class="h-4 w-4" />
                                </Button>
                            </Link>
                            <Link :href="`/admin/packages/${pkg.id}/edit`">
                                <Button variant="outline" size="sm">
                                    <Edit class="h-4 w-4" />
                                </Button>
                            </Link>
                            <Button 
                                variant="destructive" 
                                size="sm"
                                @click="deletePackage(pkg.id, pkg.name)"
                            >
                                <Trash2 class="h-4 w-4" />
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <div v-if="props.packages.data.length === 0" class="text-center py-8">
                <p class="text-muted-foreground">No packages found</p>
            </div>
        </div>
    </AppLayout>
</template>