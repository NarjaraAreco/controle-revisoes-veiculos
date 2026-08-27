<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { computed, ref } from 'vue';

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

const props = defineProps<{
    vehicles: Vehicle[];
}>();

type SortKey = 'plate' | 'brand' | 'model' | 'year' | 'person';
const sortKey = ref<SortKey>('plate');
const sortDirection = ref<'asc' | 'desc'>('asc');

function sortBy(key: SortKey) {
    if (sortKey.value === key) {
        sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
        return;
    }

    sortKey.value = key;
    sortDirection.value = 'asc';
}

const sortedVehicles = computed(() => [...props.vehicles].sort((left, right) => {
    const leftValue = String(sortKey.value === 'person' ? left.person?.name : left[sortKey.value] ?? '').toLocaleLowerCase();
    const rightValue = String(sortKey.value === 'person' ? right.person?.name : right[sortKey.value] ?? '').toLocaleLowerCase();
    const result = sortKey.value === 'year'
        ? Number(left.year) - Number(right.year)
        : leftValue.localeCompare(rightValue, 'pt-BR');
    return sortDirection.value === 'asc' ? result : -result;
}));

const deleteForm = useForm({});

function deleteVehicle(id: number) {
    if (!window.confirm('Deseja realmente excluir este veículo?')) {
        return;
    }

    deleteForm.delete(`/vehicles/${id}`);
}

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

                <Link href="/vehicles/create" class="rounded-md bg-primary px-4 py-2 text-primary-foreground">
                    Novo veículo
                </Link>
            </div>

            <div v-if="vehicles.length === 0" class="rounded-lg border p-6">
                Nenhum veículo cadastrado.
            </div>

            <div v-else class="max-h-[32rem] overflow-auto rounded-lg border">
                <table class="w-full min-w-[950px] text-left text-sm">
                    <thead class="sticky top-0 z-10 border-b bg-muted/95">
                        <tr>
                            <th class="px-4 py-3"><button type="button" @click="sortBy('plate')">Placa ↕</button></th>
                            <th class="px-4 py-3"><button type="button" @click="sortBy('brand')">Marca ↕</button></th>
                            <th class="px-4 py-3"><button type="button" @click="sortBy('model')">Modelo ↕</button></th>
                            <th class="px-4 py-3"><button type="button" @click="sortBy('year')">Ano ↕</button></th>
                            <th class="px-4 py-3">Cor</th>
                            <th class="px-4 py-3"><button type="button" @click="sortBy('person')">Proprietário ↕</button></th>
                            <th class="px-4 py-3">Ações</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr v-for="vehicle in sortedVehicles" :key="vehicle.id" class="border-b last:border-0">
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
                            <td class="px-4 py-3">
                                <div class="flex gap-2">
                                    <Link :href="`/vehicles/${vehicle.id}/edit`"
                                        class="cursor-pointer rounded-md border px-3 py-1">
                                        Editar
                                    </Link>

                                    <button type="button" :disabled="deleteForm.processing"
                                        class="cursor-pointer rounded-md border border-red-500 px-3 py-1 text-red-500 disabled:cursor-not-allowed disabled:opacity-50"
                                        @click="deleteVehicle(vehicle.id)">
                                        Excluir
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AppLayout>
</template>
