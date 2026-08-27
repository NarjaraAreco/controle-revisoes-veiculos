<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { computed } from 'vue';
import {
    blockDecimalKeys,
    blockNonNumericKeys,
    formatCost,
    formatMileage,
    normalizeCost,
    normalizeMileage,
    showClientErrors,
    validateRevision,
} from '@/lib/clientValidation';

interface Vehicle {
    id: number;
    plate: string;
    brand: string;
    model: string;
    person: {
        name: string;
    } | null;
}

interface Revision {
    id: number;
    vehicle_id: number;
    maintenance_type: 'preventive' | 'corrective';
    revision_date: string;
    mileage: number;
    description: string;
    cost: string | number | null;
    next_revision_date: string | null;
}

const { vehicles, revision } = defineProps<{
    vehicles: Vehicle[];
    revision: Revision;
}>();

const today = new Date().toISOString().slice(0, 10);

function formatDateForInput(date: string | null) {
    return date ? date.substring(0, 10) : '';
}

const form = useForm({
    vehicle_id: String(revision.vehicle_id),
    maintenance_type: revision.maintenance_type,
    revision_date: formatDateForInput(revision.revision_date),
    mileage: formatMileage(revision.mileage),
    description: revision.description,
    cost: revision.cost ? formatCost(revision.cost) : '',
    next_revision_date: formatDateForInput(
        revision.next_revision_date,
    ),
});

function maskMileage() {
    form.mileage = formatMileage(form.mileage);
}

function maskCost() {
    form.cost = formatCost(form.cost);
}

const selectedVehicle = computed(() =>
    vehicles.find((vehicle) => String(vehicle.id) === form.vehicle_id),
);

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Revisões',
        href: '/revisions',
    },
    {
        title: 'Nova revisão',
        href: '/revisions/create',
    },
];

function submit() {
    const errors = validateRevision(form);
    if (Object.keys(errors).length > 0) {
        showClientErrors(errors);
        return;
    }

    form.transform((data) => ({
        ...data,
        mileage: normalizeMileage(data.mileage),
        cost: normalizeCost(data.cost),
    })).put(`/revisions/${revision.id}`);
}

</script>

<template>

    <Head title="Nova revisão" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 p-6">
            <div>
                <h1 class="text-2xl font-semibold">Editar revisão</h1>

                <p class="text-muted-foreground">
                    Edite uma revisão realizada em um veículo.
                </p>
            </div>

            <form class="grid gap-6 rounded-lg border p-6 md:grid-cols-2" @submit.prevent="submit">
                <div class="md:col-span-2">
                    <label for="vehicle_id" class="mb-2 block text-sm font-medium">
                        Veículo
                    </label>

                    <select id="vehicle_id" v-model="form.vehicle_id" required
                        class="w-full rounded-md border bg-background px-3 py-2">
                        <option value="">Selecione o veículo</option>

                        <option v-for="vehicle in vehicles" :key="vehicle.id" :value="String(vehicle.id)">
                            {{ vehicle.plate }} -
                            {{ vehicle.brand }}
                            {{ vehicle.model }}
                            -
                            {{ vehicle.person?.name || 'Sem proprietário' }}
                        </option>
                    </select>

                    <p v-if="selectedVehicle" class="mt-1 text-sm text-muted-foreground">
                        Proprietário: {{ selectedVehicle.person?.name || 'Sem proprietário' }}
                    </p>

                    <p v-if="form.errors.vehicle_id" class="mt-1 text-sm text-red-500">
                        {{ form.errors.vehicle_id }}
                    </p>
                </div>
                <div class="md:col-span-2">
                    <label for="maintenance_type" class="mb-2 block text-sm font-medium">
                        Tipo de manutenção
                    </label>

                    <select id="maintenance_type" v-model="form.maintenance_type" required
                        class="w-full rounded-md border bg-background px-3 py-2">
                        <option value="">Selecione o tipo de manutenção</option>
                        <option value="preventive">Preventiva</option>
                        <option value="corrective">Corretiva</option>
                    </select>

                    <p v-if="form.errors.maintenance_type" class="mt-1 text-sm text-red-500">
                        {{ form.errors.maintenance_type }}
                    </p>
                </div>
                <div>
                    <label for="revision_date" class="mb-2 block text-sm font-medium">
                        Data da revisão
                    </label>

                    <input id="revision_date" v-model="form.revision_date" type="date" required :max="today"
                        class="w-full rounded-md border bg-background px-3 py-2" />

                    <p v-if="form.errors.revision_date" class="mt-1 text-sm text-red-500">
                        {{ form.errors.revision_date }}
                    </p>
                </div>

                <div>
                    <label for="mileage" class="mb-2 block text-sm font-medium">
                        Quilometragem
                    </label>

                    <input id="mileage" v-model="form.mileage" type="text" min="0" maxlength="13" required pattern="(?:[0-9]{1,3}(?:\.[0-9]{3})*|[0-9]{1,10})" placeholder="Ex.: 45.000"
                        inputmode="numeric" @keydown="blockNonNumericKeys" @input="maskMileage"
                        class="w-full rounded-md border bg-background px-3 py-2" />

                    <p v-if="form.errors.mileage" class="mt-1 text-sm text-red-500">
                        {{ form.errors.mileage }}
                    </p>
                </div>

                <div class="md:col-span-2">
                    <label for="description" class="mb-2 block text-sm font-medium">
                        Descrição dos serviços
                    </label>

                    <textarea id="description" v-model="form.description" required maxlength="2000" rows="5"
                        placeholder="Descreva os serviços realizados..."
                        class="w-full rounded-md border bg-background px-3 py-2"></textarea>

                    <p v-if="form.errors.description" class="mt-1 text-sm text-red-500">
                        {{ form.errors.description }}
                    </p>
                </div>

                <div>
                    <label for="cost" class="mb-2 block text-sm font-medium">
                        Custo
                    </label>

                    <input id="cost" v-model="form.cost" type="text" inputmode="decimal" maxlength="13" pattern="(?:[0-9]{1,3}(?:\.[0-9]{3})*|[0-9]{1,8})(,[0-9]{1,2})?" placeholder="Ex.: 350,00"
                        @keydown="blockDecimalKeys" @input="maskCost"
                        class="w-full rounded-md border bg-background px-3 py-2" />

                    <p v-if="form.errors.cost" class="mt-1 text-sm text-red-500">
                        {{ form.errors.cost }}
                    </p>
                </div>

                <div>
                    <label for="next_revision_date" class="mb-2 block text-sm font-medium">
                        Próxima revisão
                    </label>

                    <input id="next_revision_date" v-model="form.next_revision_date" type="date"
                        :min="form.revision_date" class="w-full rounded-md border bg-background px-3 py-2" />

                    <p v-if="form.errors.next_revision_date" class="mt-1 text-sm text-red-500">
                        {{ form.errors.next_revision_date }}
                    </p>
                </div>

                <div class="flex gap-3 md:col-span-2">
                    <Link href="/revisions" class="rounded-md border px-4 py-2">
                        Cancelar
                    </Link>

                    <button type="submit" :disabled="form.processing"
                        class="rounded-md bg-primary px-4 py-2 text-primary-foreground disabled:opacity-50">
                        {{
                            form.processing
                                ? 'Salvando...'
                                : 'Atualizar revisão'
                        }}
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
