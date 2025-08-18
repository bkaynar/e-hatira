<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import Swal from 'sweetalert2';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
// Using native HTML elements instead of UI components

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
        title: 'Create Package',
        href: '/admin/packages/create',
    },
];

const form = useForm({
    name: '',
    slug: '',
    price: 0,
    currency: 'TRY',
    max_uploads: undefined as number | undefined,
    storage_days: 30,
    upload_days: 7,
    quality: 'normal' as 'normal' | 'high',
    advanced_customization: false,
    features: [] as string[],
    is_active: true,
});

const generateSlug = () => {
    form.slug = form.name.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '');
};

const submit = () => {
    form.post('/admin/packages', {
        onSuccess: () => {
            Swal.fire({
                title: 'Success!',
                text: 'Package created successfully.',
                icon: 'success',
                timer: 2000,
                showConfirmButton: false
            });
        },
        onError: () => {
            Swal.fire({
                title: 'Error!',
                text: 'Something went wrong while creating the package.',
                icon: 'error'
            });
        }
    });
};
</script>

<template>
    <Head title="Create Package" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div>
                <h1 class="text-2xl font-bold text-foreground">Create Package</h1>
                <p class="text-muted-foreground">Create a new subscription package</p>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Package Information</CardTitle>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submit" class="space-y-6">
                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="name">Package Name</Label>
                                <Input 
                                    id="name"
                                    v-model="form.name"
                                    @input="generateSlug"
                                    placeholder="e.g., Free, Plus, Pro"
                                />
                                <div v-if="form.errors.name" class="text-sm text-red-600">{{ form.errors.name }}</div>
                            </div>

                            <div class="space-y-2">
                                <Label for="slug">Slug</Label>
                                <Input 
                                    id="slug"
                                    v-model="form.slug"
                                    placeholder="e.g., free, plus, pro"
                                />
                                <div v-if="form.errors.slug" class="text-sm text-red-600">{{ form.errors.slug }}</div>
                            </div>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="price">Price</Label>
                                <Input 
                                    id="price"
                                    v-model="form.price"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                />
                                <div v-if="form.errors.price" class="text-sm text-red-600">{{ form.errors.price }}</div>
                            </div>

                            <div class="space-y-2">
                                <Label for="currency">Currency</Label>
                                <select 
                                    id="currency"
                                    v-model="form.currency"
                                    class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus:border-ring focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2"
                                >
                                    <option value="TRY">TRY</option>
                                    <option value="USD">USD</option>
                                    <option value="EUR">EUR</option>
                                </select>
                                <div v-if="form.errors.currency" class="text-sm text-red-600">{{ form.errors.currency }}</div>
                            </div>
                        </div>

                        <div class="grid gap-4 md:grid-cols-3">
                            <div class="space-y-2">
                                <Label for="max_uploads">Max Uploads (leave empty for unlimited)</Label>
                                <Input 
                                    id="max_uploads"
                                    v-model="form.max_uploads"
                                    type="number"
                                    min="1"
                                    placeholder="Unlimited"
                                />
                                <div v-if="form.errors.max_uploads" class="text-sm text-red-600">{{ form.errors.max_uploads }}</div>
                            </div>

                            <div class="space-y-2">
                                <Label for="storage_days">Storage Days</Label>
                                <Input 
                                    id="storage_days"
                                    v-model="form.storage_days"
                                    type="number"
                                    min="1"
                                />
                                <div v-if="form.errors.storage_days" class="text-sm text-red-600">{{ form.errors.storage_days }}</div>
                            </div>

                            <div class="space-y-2">
                                <Label for="upload_days">Upload Days</Label>
                                <Input 
                                    id="upload_days"
                                    v-model="form.upload_days"
                                    type="number"
                                    min="1"
                                />
                                <div v-if="form.errors.upload_days" class="text-sm text-red-600">{{ form.errors.upload_days }}</div>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label for="quality">Quality</Label>
                            <select 
                                id="quality"
                                v-model="form.quality"
                                class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus:border-ring focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2"
                            >
                                <option value="normal">Normal</option>
                                <option value="high">High</option>
                            </select>
                            <div v-if="form.errors.quality" class="text-sm text-red-600">{{ form.errors.quality }}</div>
                        </div>

                        <div class="flex items-center space-x-2">
                            <input
                                id="advanced_customization"
                                type="checkbox"
                                v-model="form.advanced_customization"
                                class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary"
                            />
                            <Label for="advanced_customization">Advanced Customization</Label>
                        </div>

                        <div class="flex items-center space-x-2">
                            <input
                                id="is_active"
                                type="checkbox"
                                v-model="form.is_active"
                                class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary"
                            />
                            <Label for="is_active">Active</Label>
                        </div>

                        <div class="flex gap-4">
                            <Button type="submit" :disabled="form.processing">
                                {{ form.processing ? 'Creating...' : 'Create Package' }}
                            </Button>
                            <Button type="button" variant="outline" @click="$inertia.visit('/admin/packages')">
                                Cancel
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>