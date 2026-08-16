<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { reactive } from 'vue';

interface VehicleByBrand {
    brand: string;
    total: number | string;
}

interface PersonByGender {
    gender: string;
    total: number | string;
    average_age: number | string | null;
}

interface PersonWithVehicles {
    id: number;
    name: string;
    gender: string | null;
    vehicles_count: number;
}

interface Vehicle {
    id: number;
    plate: string;
    brand: string;
    model: string;
    year: number;
    color: string | null;
}

interface VehiclesByPerson {
    id: number;
    name: string;
    gender: string | null;
    vehicles: Vehicle[];
}

interface BrandByGender {
    brand: string;
    gender: string;
    total: number | string;
}

interface RevisionReport {
    id: number;
    maintenance_type: string;
    revision_date: string;
    mileage: number;
    description: string;
    cost: number | string | null;
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

interface RevisionFilters {
    start_date: string | null;
    end_date: string | null;
}

interface PersonByRevisionCount {
    id: number;
    name: string;
    gender: string | null;
    total: number | string;
}

const {
    totalPeople,
    totalVehicles,
    totalRevisions,
    peopleByGender,
    vehiclesByBrand,
    peopleWithVehicles,
    vehiclesByPerson,
    peopleWithMostVehiclesByGender,
    brandsByGender,
    revisionsInPeriod,
    revisionFilters,
    revisionsByBrand,
    peopleByRevisionCount,
} = defineProps<{
    totalPeople: number;
    totalVehicles: number;
    totalRevisions: number;
    peopleByGender: PersonByGender[];
    vehiclesByBrand: VehicleByBrand[];
    peopleWithVehicles: PersonWithVehicles[];
    vehiclesByPerson: VehiclesByPerson[];
    peopleWithMostVehiclesByGender: PersonWithVehicles[];
    brandsByGender: BrandByGender[];
    revisionsInPeriod: RevisionReport[];
    revisionFilters: RevisionFilters;
    revisionsByBrand: VehicleByBrand[];
    peopleByRevisionCount: PersonByRevisionCount[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Relatórios',
        href: '/reports',
    },
];

const revisionFilterForm = reactive({
    start_date: revisionFilters.start_date ?? '',
    end_date: revisionFilters.end_date ?? '',
});

function filterRevisions() {
    router.get('/reports', revisionFilterForm, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
}

function clearRevisionFilters() {
    revisionFilterForm.start_date = '';
    revisionFilterForm.end_date = '';

    filterRevisions();
}

function formatDate(date: string | null) {
    if (!date) {
        return 'Não informado';
    }

    const [year, month, day] = date.substring(0, 10).split('-');

    return `${day}/${month}/${year}`;
}

function formatMaintenanceType(type: string) {
    return type === 'preventive' ? 'Preventiva' : 'Corretiva';
}

function formatCost(cost: number | string | null) {
    if (cost === null || cost === '') {
        return 'Não informado';
    }

    return Number(cost).toLocaleString('pt-BR', {
        style: 'currency',
        currency: 'BRL',
    });
}

//GRAFICOS ------ BARRA
const maxVehiclesByBrand = Math.max(
    ...vehiclesByBrand.map((item) => Number(item.total)),
    1,
);
const maxPeopleWithVehicles = Math.max(
    ...peopleWithVehicles.map((person) => Number(person.vehicles_count)),
    1,
);

const maxPeopleByGender = Math.max(
    ...peopleByGender.map((item) => Number(item.total)),
    1,
);

const maxVehiclesByPerson = Math.max(
    ...vehiclesByPerson.map((person) => person.vehicles.length),
    1,
);

const maxMostVehiclesByGender = Math.max(
    ...peopleWithMostVehiclesByGender.map((person) =>
        Number(person.vehicles_count),
    ),
    1,
);

const maxBrandsByGender = Math.max(
    ...brandsByGender.map((item) => Number(item.total)),
    1,
);

const maxRevisionsByBrand = Math.max(
    ...revisionsByBrand.map((item) => Number(item.total)),
    1,
);

const maxPeopleByRevisionCount = Math.max(
    ...peopleByRevisionCount.map((item) => Number(item.total)),
    1,
);

</script>

<template>

    <Head title="Relatórios" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 p-6">
            <div>
                <h1 class="text-2xl font-semibold">Relatórios</h1>

                <p class="text-muted-foreground">
                    Consulte os principais indicadores do sistema.
                </p>
            </div>
            <form class="grid gap-4 rounded-xl border p-5 md:grid-cols-3" @submit.prevent="filterRevisions">
                <div>
                    <label for="start_date" class="mb-2 block text-sm font-medium">
                        Data inicial
                    </label>

                    <input id="start_date" v-model="revisionFilterForm.start_date" type="date"
                        class="w-full rounded-md border bg-background px-3 py-2" />
                </div>

                <div>
                    <label for="end_date" class="mb-2 block text-sm font-medium">
                        Data final
                    </label>

                    <input id="end_date" v-model="revisionFilterForm.end_date" type="date"
                        class="w-full rounded-md border bg-background px-3 py-2" />
                </div>

                <div class="flex items-end gap-3">
                    <button type="submit" class="rounded-md bg-primary px-4 py-2 text-primary-foreground">
                        Filtrar
                    </button>

                    <button type="button" class="rounded-md border px-4 py-2" @click="clearRevisionFilters">
                        Limpar
                    </button>
                </div>
            </form>
            <section class="rounded-xl border">
                <div class="flex items-center justify-between border-b p-5">
                    <div>
                        <h2 class="font-semibold">
                            Revisões no período
                        </h2>

                        <p class="mt-1 text-sm text-muted-foreground">
                            {{ revisionsInPeriod.length }} revisão(ões) encontrada(s).
                        </p>
                    </div>
                </div>

                <p v-if="revisionsInPeriod.length === 0" class="p-5 text-muted-foreground">
                    Nenhuma revisão encontrada para o período informado.
                </p>

                <div v-else class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="border-b bg-muted/50">
                            <tr>
                                <th class="px-5 py-3">Data</th>
                                <th class="px-5 py-3">Pessoa</th>
                                <th class="px-5 py-3">Veículo</th>
                                <th class="px-5 py-3">Tipo</th>
                                <th class="px-5 py-3">Quilometragem</th>
                                <th class="px-5 py-3">Custo</th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr v-for="revision in revisionsInPeriod" :key="revision.id" class="border-b last:border-0">
                                <td class="px-5 py-3">
                                    {{ formatDate(revision.revision_date) }}
                                </td>

                                <td class="px-5 py-3">
                                    {{ revision.vehicle.person?.name ?? 'Não informado' }}
                                </td>

                                <td class="px-5 py-3">
                                    {{ revision.vehicle.plate }}
                                    -
                                    {{ revision.vehicle.brand }}
                                    {{ revision.vehicle.model }}
                                </td>

                                <td class="px-5 py-3">
                                    {{ formatMaintenanceType(revision.maintenance_type) }}
                                </td>

                                <td class="px-5 py-3">
                                    {{ revision.mileage.toLocaleString('pt-BR') }} km
                                </td>

                                <td class="px-5 py-3">
                                    {{ formatCost(revision.cost) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
            <section class="rounded-xl border">
                <div class="border-b p-5">
                    <h2 class="font-semibold">
                        Marcas com mais revisões
                    </h2>
                </div>

                <div v-if="revisionsByBrand.length > 0" class="space-y-5 p-5">
                    <div v-for="item in revisionsByBrand" :key="item.brand">
                        <div class="mb-1 flex justify-between text-sm">
                            <span>{{ item.brand }}</span>
                            <span>{{ item.total }}</span>
                        </div>

                        <div class="h-3 overflow-hidden rounded-full bg-muted">
                            <div class="h-full rounded-full bg-primary transition-all" :style="{
                                width: `${(Number(item.total) / maxRevisionsByBrand) * 100}%`,
                            }"></div>
                        </div>
                    </div>
                </div>

                <p v-else class="p-5 text-muted-foreground">
                    Nenhuma revisão encontrada no período.
                </p>
            </section>
            <section class="rounded-xl border">
                <div class="border-b p-5">
                    <h2 class="font-semibold">
                        Pessoas com mais revisões
                    </h2>
                </div>

                <div v-if="peopleByRevisionCount.length > 0" class="space-y-5 p-5">
                    <div v-for="person in peopleByRevisionCount" :key="person.id">
                        <div class="mb-1 flex justify-between text-sm">
                            <span>
                                {{ person.name }}
                                <span v-if="person.gender" class="text-muted-foreground">
                                    ({{ person.gender }})
                                </span>
                            </span>

                            <span>{{ person.total }}</span>
                        </div>

                        <div class="h-3 overflow-hidden rounded-full bg-muted">
                            <div class="h-full rounded-full bg-primary transition-all" :style="{
                                width: `${(Number(person.total) / maxPeopleByRevisionCount) * 100}%`,
                            }"></div>
                        </div>
                    </div>
                </div>

                <p v-else class="p-5 text-muted-foreground">
                    Nenhuma pessoa possui revisões no período.
                </p>
            </section>
            <div class="grid gap-4 md:grid-cols-4">
                <div class="rounded-xl border p-5">
                    <p class="text-sm text-muted-foreground">Pessoas</p>
                    <p class="mt-2 text-3xl font-semibold">
                        {{ totalPeople }}
                    </p>
                </div>

                <div class="rounded-xl border p-5">
                    <p class="text-sm text-muted-foreground">Veículos</p>
                    <p class="mt-2 text-3xl font-semibold">
                        {{ totalVehicles }}
                    </p>
                </div>

                <div class="rounded-xl border p-5">
                    <p class="text-sm text-muted-foreground">Revisões</p>
                    <p class="mt-2 text-3xl font-semibold">
                        {{ totalRevisions }}
                    </p>
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-2">
                <section class="rounded-xl border">
                    <div class="border-b p-5">
                        <h2 class="font-semibold">Veículos por marca</h2>
                    </div>
                    <div v-if="vehiclesByBrand.length > 0" class="space-y-4 border-b p-5">
                        <div v-for="item in vehiclesByBrand" :key="`chart-${item.brand}`">
                            <div class="mb-1 flex justify-between text-sm">
                                <span>{{ item.brand }}</span>
                                <span>{{ item.total }}</span>
                            </div>

                            <div class="h-3 overflow-hidden rounded-full bg-muted">
                                <div class="h-full rounded-full bg-primary transition-all" :style="{
                                    width: `${(Number(item.total) / maxVehiclesByBrand) * 100}%`,
                                }"></div>
                            </div>
                        </div>
                    </div>
                    <div v-if="vehiclesByBrand.length === 0" class="p-5 text-muted-foreground">
                        Nenhum veículo cadastrado.
                    </div>

                    <div v-else class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="border-b bg-muted/50">
                                <tr>
                                    <th class="px-5 py-3">Marca</th>
                                    <th class="px-5 py-3">Quantidade</th>
                                </tr>
                            </thead>

                            <tbody>
                                <tr v-for="item in vehiclesByBrand" :key="item.brand" class="border-b last:border-0">
                                    <td class="px-5 py-3">
                                        {{ item.brand }}
                                    </td>
                                    <td class="px-5 py-3">
                                        {{ item.total }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </section>

                <section class="rounded-xl border">
                    <div class="border-b p-5">
                        <h2 class="font-semibold">Pessoas com mais veículos</h2>
                    </div>
                    <div v-if="peopleWithVehicles.length > 0" class="space-y-4 border-b p-5">
                        <div v-for="person in peopleWithVehicles" :key="`chart-person-${person.id}`">
                            <div class="mb-1 flex justify-between text-sm">
                                <span>{{ person.name }}</span>
                                <span>{{ person.vehicles_count }}</span>
                            </div>

                            <div class="h-3 overflow-hidden rounded-full bg-muted">
                                <div class="h-full rounded-full bg-primary transition-all" :style="{
                                    width: `${(Number(person.vehicles_count) / maxPeopleWithVehicles) * 100}%`,
                                }"></div>
                            </div>
                        </div>
                    </div>
                    <div v-if="peopleWithVehicles.length === 0" class="p-5 text-muted-foreground">
                        Nenhuma pessoa cadastrada.
                    </div>

                    <div v-else class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="border-b bg-muted/50">
                                <tr>
                                    <th class="px-5 py-3">Pessoa</th>
                                    <th class="px-5 py-3">Veículos</th>
                                </tr>
                            </thead>

                            <tbody>
                                <tr v-for="person in peopleWithVehicles" :key="person.id"
                                    class="border-b last:border-0">
                                    <td class="px-5 py-3">
                                        {{ person.name }}
                                    </td>
                                    <td class="px-5 py-3">
                                        {{ person.vehicles_count }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
            <section class="rounded-xl border">
                <div class="border-b p-5">
                    <h2 class="font-semibold">
                        Pessoas por gênero e média de idade
                    </h2>
                </div>

                <div v-if="peopleByGender.length > 0" class="grid gap-6 p-5 md:grid-cols-2">
                    <div v-for="item in peopleByGender" :key="item.gender" class="space-y-3">
                        <div class="flex justify-between text-sm">
                            <span>{{ item.gender }}</span>
                            <span>{{ item.total }} pessoa(s)</span>
                        </div>

                        <div class="h-3 overflow-hidden rounded-full bg-muted">
                            <div class="h-full rounded-full bg-primary transition-all" :style="{
                                width: `${(Number(item.total) / maxPeopleByGender) * 100}%`,
                            }"></div>
                        </div>

                        <p class="text-sm text-muted-foreground">
                            Média de idade:
                            {{
                                item.average_age === null
                                    ? 'Não informado'
                                    : `${Number(item.average_age).toFixed(1)} anos`
                            }}
                        </p>
                    </div>
                </div>

                <p v-else class="p-5 text-muted-foreground">
                    Não existem dados de gênero cadastrados.
                </p>
            </section>
            <section class="rounded-xl border">
                <div class="border-b p-5">
                    <h2 class="font-semibold">
                        Quem tem mais veículos por gênero
                    </h2>
                </div>

                <div v-if="peopleWithMostVehiclesByGender.length > 0" class="grid gap-6 p-5 md:grid-cols-2">
                    <div v-for="person in peopleWithMostVehiclesByGender" :key="person.id" class="space-y-3">
                        <div class="flex justify-between text-sm">
                            <span>
                                {{ person.gender }}
                            </span>

                            <span>
                                {{ person.vehicles_count }} veículo(s)
                            </span>
                        </div>

                        <p class="font-semibold">
                            {{ person.name }}
                        </p>

                        <div class="h-3 overflow-hidden rounded-full bg-muted">
                            <div class="h-full rounded-full bg-primary transition-all" :style="{
                                width: `${(Number(person.vehicles_count) / maxMostVehiclesByGender) * 100}%`,
                            }"></div>
                        </div>
                    </div>
                </div>

                <p v-else class="p-5 text-muted-foreground">
                    Não existem dados suficientes para esse relatório.
                </p>
            </section>
            <section class="rounded-xl border">
                <div class="border-b p-5">
                    <h2 class="font-semibold">Todos os veículos por pessoa</h2>

                    <p class="mt-1 text-sm text-muted-foreground">
                        Proprietários em ordem alfabética.
                    </p>
                </div>

                <div v-if="vehiclesByPerson.length > 0" class="divide-y">
                    <div v-for="person in vehiclesByPerson" :key="person.id" class="space-y-4 p-5">
                        <div class="flex justify-between">
                            <h3 class="font-semibold">
                                {{ person.name }}
                            </h3>

                            <span class="text-sm text-muted-foreground">
                                {{ person.vehicles.length }} veículo(s)
                            </span>
                        </div>

                        <div class="h-3 overflow-hidden rounded-full bg-muted">
                            <div class="h-full rounded-full bg-primary transition-all" :style="{
                                width: `${(person.vehicles.length / maxVehiclesByPerson) * 100}%`,
                            }"></div>
                        </div>

                        <div class="overflow-x-auto rounded-lg border">
                            <table class="w-full text-left text-sm">
                                <thead class="border-b bg-muted/50">
                                    <tr>
                                        <th class="px-4 py-3">Placa</th>
                                        <th class="px-4 py-3">Marca</th>
                                        <th class="px-4 py-3">Modelo</th>
                                        <th class="px-4 py-3">Ano</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    <tr v-for="vehicle in person.vehicles" :key="vehicle.id"
                                        class="border-b last:border-0">
                                        <td class="px-4 py-3">
                                            {{ vehicle.plate }}
                                        </td>
                                        <td class="px-4 py-3">
                                            {{ vehicle.brand }}
                                        </td>
                                        <td class="px-4 py-3">
                                            {{ vehicle.model }}
                                        </td>
                                        <td class="px-4 py-3">
                                            {{ vehicle.year }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <p v-else class="p-5 text-muted-foreground">
                    Nenhum veículo cadastrado.
                </p>
            </section>
            <section class="rounded-xl border">
                <div class="border-b p-5">
                    <h2 class="font-semibold">
                        Marcas de veículos por gênero
                    </h2>
                </div>

                <div v-if="brandsByGender.length > 0" class="space-y-5 p-5">
                    <div v-for="item in brandsByGender" :key="`${item.brand}-${item.gender}`">
                        <div class="mb-1 flex justify-between text-sm">
                            <span>
                                {{ item.brand }} - {{ item.gender }}
                            </span>

                            <span>{{ item.total }}</span>
                        </div>

                        <div class="h-3 overflow-hidden rounded-full bg-muted">
                            <div class="h-full rounded-full bg-primary transition-all" :style="{
                                width: `${(Number(item.total) / maxBrandsByGender) * 100}%`,
                            }"></div>
                        </div>
                    </div>
                </div>

                <p v-else class="p-5 text-muted-foreground">
                    Não existem dados de marcas por gênero.
                </p>
            </section>
        </div>
    </AppLayout>
</template>
