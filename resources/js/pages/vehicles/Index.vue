<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';

interface Vehicle {
    id: number;
    plate: string;
    brand: string;
    model: string;
    year: number;
    color: string | null;
    person: {
        name: string;
    } | null;
}

defineProps<{
    vehicles: Vehicle[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Veículos',
        href: '/vehicles',
    },
];
</script>

<template>
    <Head title="Veículos" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold">Veículos</h1>
                    <p class="text-muted-foreground">
                        Veículos cadastrados no sistema.
                    </p>
                </div>

                <Link
                    href="/vehicles/create"
                    class="rounded-md bg-primary px-4 py-2 text-primary-foreground"
                >
                    Novo veículo
                </Link>
            </div>

            <div v-if="vehicles.length === 0" class="rounded-lg border p-6">
                Nenhum veículo cadastrado.
            </div>

            <div v-else class="overflow-x-auto rounded-lg border">
                <table class="w-full text-left text-sm">
                    <thead class="border-b bg-muted/50">
                        <tr>
                            <th class="px-4 py-3">Placa</th>
                            <th class="px-4 py-3">Marca</th>
                            <th class="px-4 py-3">Modelo</th>
                            <th class="px-4 py-3">Ano</th>
                            <th class="px-4 py-3">Cor</th>
                            <th class="px-4 py-3">Proprietário</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr
                            v-for="vehicle in vehicles"
                            :key="vehicle.id"
                            class="border-b last:border-0"
                        >
                            <td class="px-4 py-3">{{ vehicle.plate }}</td>
                            <td class="px-4 py-3">{{ vehicle.brand }}</td>
                            <td class="px-4 py-3">{{ vehicle.model }}</td>
                            <td class="px-4 py-3">{{ vehicle.year }}</td>
                            <td class="px-4 py-3">
                                {{ vehicle.color || 'Não informado' }}
                            </td>
                            <td class="px-4 py-3">
                                {{
                                    vehicle.person?.name || 'Não informado'
                                }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AppLayout>
</template>