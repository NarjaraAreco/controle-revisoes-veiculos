<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';

interface Revision {
    id: number;
    maintenance_type: 'preventive' | 'corrective';
    revision_date: string;
    mileage: number;
    description: string;
    cost: string | number | null;
    next_revision_date: string | null;
    vehicle: {
        plate: string;
        brand: string;
        model: string;
        person: {
            name: string;
        } | null;
    };
}

const { revisions } = defineProps<{
    revisions: Revision[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Revisões',
        href: '/revisions',
    },
];

function formatDate(date: string | null) {
    if (!date) {
        return 'Não informado';
    }

    const [year, month, day] = date.substring(0, 10).split('-');

    return `${day}/${month}/${year}`;
}

function formatMaintenanceType(type: Revision['maintenance_type']) {
    return type === 'preventive' ? 'Preventiva' : 'Corretiva';
}

function formatCost(cost: string | number | null) {
    if (cost === null || cost === '') {
        return 'Não informado';
    }

    return Number(cost).toLocaleString('pt-BR', {
        style: 'currency',
        currency: 'BRL',
    });
}

const deleteForm = useForm({});

function deleteRevision(id: number) {
    if (!window.confirm('Deseja realmente excluir esta revisão?')) {
        return;
    }

    deleteForm.delete(`/revisions/${id}`);
}
</script>

<template>

    <Head title="Revisões" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold">Revisões</h1>

                    <p class="text-muted-foreground">
                        Histórico de manutenções dos veículos.
                    </p>
                </div>

                <Link href="/revisions/create" class="rounded-md bg-primary px-4 py-2 text-primary-foreground">
                    Nova revisão
                </Link>
            </div>

            <div v-if="revisions.length === 0" class="rounded-lg border p-6">
                Nenhuma revisão cadastrada.
            </div>

            <div v-else class="overflow-x-auto rounded-lg border">
                <table class="w-full text-left text-sm">
                    <thead class="border-b bg-muted/50">
                        <tr>
                            <th class="px-4 py-3">Tipo</th>
                            <th class="px-4 py-3">Data</th>
                            <th class="px-4 py-3">Veículo</th>
                            <th class="px-4 py-3">Quilometragem</th>
                            <th class="px-4 py-3">Custo</th>
                            <th class="px-4 py-3">Próxima revisão</th>
                            <th class="px-4 py-3">Ações</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr v-for="revision in revisions" :key="revision.id" class="border-b last:border-0">
                            <td class="px-4 py-3">
                                {{ formatMaintenanceType(revision.maintenance_type) }}
                            </td>

                            <td class="px-4 py-3">
                                {{ formatDate(revision.revision_date) }}
                            </td>

                            <td class="px-4 py-3">
                                <div>
                                    {{ revision.vehicle.plate }}
                                </div>

                                <div class="text-muted-foreground">
                                    {{ revision.vehicle.brand }}
                                    {{ revision.vehicle.model }}
                                </div>
                            </td>

                            <td class="px-4 py-3">
                                {{ revision.mileage.toLocaleString('pt-BR') }} km
                            </td>

                            <td class="px-4 py-3">
                                {{ formatCost(revision.cost) }}
                            </td>

                            <td class="px-4 py-3">
                                {{ formatDate(revision.next_revision_date) }}
                            </td>
                            <td class="px-4 py-3"> 
                                <div class="flex gap-2">
                                    <Link :href="`/revisions/${revision.id}/edit`" class="rounded-md border px-3 py-1">
                                        Editar
                                    </Link>

                                    <button type="button" :disabled="deleteForm.processing"
                                        class="cursor-pointer rounded-md border border-red-500 px-3 py-1 text-red-500 disabled:cursor-not-allowed disabled:opacity-50"
                                        @click="deleteRevision(revision.id)">
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