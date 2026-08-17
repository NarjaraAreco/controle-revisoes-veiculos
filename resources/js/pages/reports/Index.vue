<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { computed, reactive } from 'vue';

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

interface PersonReport {
    id: number;
    name: string;
    cpf: string;
    birth_date: string | null;
    gender: string | null;
    email: string | null;
    phone: string | null;
    city: string | null;
    state: string | null;
}

interface CityReport {
    city: string;
    total: number | string;
}

interface Vehicle {
    id: number;
    plate: string;
    brand: string;
    model: string;
    year: number;
    color: string | null;
}

interface VehicleReport {
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

interface YearReport {
    year: number;
    total: number | string;
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

interface RevisionByMonth {
    month: string;
    total: number;
}

interface PersonByRevisionCount {
    id: number;
    name: string;
    gender: string | null;
    total: number | string;
}

interface AverageRevisionInterval {
    person_id: number;
    name: string;
    average_days: number | string;
}

interface NextRevision {
    person_id: number;
    name: string;
    last_revision_date: string;
    average_days: number | string;
    next_revision_date: string;
}

const {
    allPeople,
    peopleByCity,
    peopleByGender,
    vehiclesByBrand,
    peopleWithVehicles,
    vehiclesByPerson,
    allVehicles,
    vehiclesByYear,
    peopleWithMostVehiclesByGender,
    brandsByGender,
    revisionsInPeriod,
    revisionsByMonth,
    revisionFilters,
    revisionsByBrand,
    peopleByRevisionCount,
    averageRevisionIntervals,
    nextRevisions,
} = defineProps<{
    allPeople: PersonReport[];
    peopleByCity: CityReport[];
    peopleByGender: PersonByGender[];
    vehiclesByBrand: VehicleByBrand[];
    peopleWithVehicles: PersonWithVehicles[];
    vehiclesByPerson: VehiclesByPerson[];
    allVehicles: VehicleReport[];
    vehiclesByYear: YearReport[];
    peopleWithMostVehiclesByGender: PersonWithVehicles[];
    brandsByGender: BrandByGender[];
    revisionsInPeriod: RevisionReport[];
    revisionsByMonth: RevisionByMonth[];
    revisionFilters: RevisionFilters;
    revisionsByBrand: VehicleByBrand[];
    peopleByRevisionCount: PersonByRevisionCount[];
    averageRevisionIntervals: AverageRevisionInterval[];
    nextRevisions: NextRevision[];
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

const maxAverageRevisionInterval = Math.max(
    ...averageRevisionIntervals.map((item) => Number(item.average_days)),
    1,
);

const maxPeopleByCity = computed(() => Math.max(
    1,
    ...peopleByCity.map((item) => Number(item.total)),
));

const maxVehiclesByYear = computed(() => Math.max(
    1,
    ...vehiclesByYear.map((item) => Number(item.total)),
));

const maxRevisionsByMonth = computed(() => Math.max(
    1,
    ...revisionsByMonth.map((item) => Number(item.total)),
));

const maxNextRevisionDays = computed(() => Math.max(
    1,
    ...nextRevisions.map((item) => daysUntil(item.next_revision_date)),
));

function daysUntil(date: string): number {
    const today = new Date();
    const target = new Date(`${date.substring(0, 10)}T00:00:00`);
    const difference = target.getTime() - new Date(
        today.getFullYear(),
        today.getMonth(),
        today.getDate(),
    ).getTime();

    return Math.max(0, Math.ceil(difference / 86400000));
}

function formatMonth(month: string): string {
    const [year, monthNumber] = month.split('-').map(Number);

    return new Intl.DateTimeFormat('pt-BR', {
        month: 'short',
        year: '2-digit',
    }).format(new Date(year, monthNumber - 1, 1));
}

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
            <details open class="group space-y-4">
                <summary class="flex cursor-pointer list-none items-center justify-between rounded-xl border p-4 font-semibold">
                    <span>Revisões</span>
                    <span class="text-xl transition-transform group-open:rotate-90">&gt;</span>
                </summary>
            <section class="space-y-4">
                <div class="border-b pb-2">
                    <h2 class="text-xl font-semibold">Revisões</h2>
                    <p class="text-sm text-muted-foreground">
                        Consulte as revisões por período, marca e pessoa.
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

                <div v-if="revisionsByMonth.length" class="border-b p-5">
                    <div class="mb-4 flex items-center justify-between">
                        <h3 class="font-semibold">Revisões por mês</h3>
                        <span class="text-xs text-muted-foreground">Quantidade</span>
                    </div>

                    <div class="flex h-48 items-end gap-3 overflow-x-auto border-b border-l px-4 pb-2 pt-6">
                        <div
                            v-for="item in revisionsByMonth"
                            :key="item.month"
                            class="flex min-w-16 flex-1 flex-col items-center justify-end gap-2"
                        >
                            <span class="text-sm font-semibold">{{ item.total }}</span>
                            <div
                                class="w-full max-w-12 rounded-t-md bg-primary"
                                :style="{ height: `${(Number(item.total) / maxRevisionsByMonth) * 100}%`, minHeight: '6px' }"
                            />
                            <span class="text-xs text-muted-foreground">{{ formatMonth(item.month) }}</span>
                        </div>
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
            <section class="rounded-xl border">
                <div class="border-b p-5">
                    <h2 class="font-semibold">
                        Média de tempo entre revisões
                    </h2>
                    <p class="mt-1 text-sm text-muted-foreground">
                        Intervalo médio entre revisões consecutivas da mesma pessoa.
                    </p>
                </div>

                <div v-if="averageRevisionIntervals.length > 0" class="space-y-5 p-5">
                    <div v-for="item in averageRevisionIntervals" :key="item.person_id">
                        <div class="mb-1 flex justify-between text-sm">
                            <span>{{ item.name }}</span>
                            <span>{{ Number(item.average_days).toFixed(1) }} dias</span>
                        </div>

                        <div class="h-3 overflow-hidden rounded-full bg-muted">
                            <div
                                class="h-full rounded-full bg-primary transition-all"
                                :style="{
                                    width: `${(Number(item.average_days) / maxAverageRevisionInterval) * 100}%`,
                                }"
                            ></div>
                        </div>
                    </div>
                </div>

                <p v-else class="p-5 text-muted-foreground">
                    Não há pessoas com duas ou mais revisões no período.
                </p>
            </section>
            <section class="rounded-xl border">
                <div class="border-b p-5">
                    <h2 class="font-semibold">
                        Próximas revisões previstas
                    </h2>
                    <p class="mt-1 text-sm text-muted-foreground">
                        Previsão baseada na média de intervalo e na última revisão.
                    </p>
                </div>

                <div v-if="nextRevisions.length > 0" class="border-b p-5">
                    <div class="mb-4 flex items-center justify-between">
                        <h3 class="font-semibold">Distância até a próxima revisão</h3>
                        <span class="text-xs text-muted-foreground">Dias previstos</span>
                    </div>

                    <div class="space-y-4">
                        <div v-for="item in nextRevisions" :key="`chart-next-${item.person_id}`">
                            <div class="mb-1 flex justify-between gap-4 text-sm">
                                <span class="truncate">{{ item.name }}</span>
                                <span>{{ daysUntil(item.next_revision_date) }} dias</span>
                            </div>
                            <div class="h-3 overflow-hidden rounded-full bg-muted">
                                <div
                                    class="h-full rounded-full bg-primary"
                                    :style="{ width: `${(daysUntil(item.next_revision_date) / maxNextRevisionDays) * 100}%` }"
                                />
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="nextRevisions.length > 0" class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="border-b bg-muted/50">
                            <tr>
                                <th class="px-5 py-3">Pessoa</th>
                                <th class="px-5 py-3">Última revisão</th>
                                <th class="px-5 py-3">Média</th>
                                <th class="px-5 py-3">Próxima revisão prevista</th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr
                                v-for="item in nextRevisions"
                                :key="item.person_id"
                                class="border-b last:border-0"
                            >
                                <td class="px-5 py-3">{{ item.name }}</td>
                                <td class="px-5 py-3">
                                    {{ formatDate(item.last_revision_date) }}
                                </td>
                                <td class="px-5 py-3">
                                    {{ Number(item.average_days).toFixed(1) }} dias
                                </td>
                                <td class="px-5 py-3 font-semibold">
                                    {{ formatDate(item.next_revision_date) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <p v-else class="p-5 text-muted-foreground">
                    Não há dados suficientes para prever próximas revisões.
                </p>
            </section>
            </section>
            </details>
            <details open class="group space-y-4">
                <summary class="flex cursor-pointer list-none items-center justify-between rounded-xl border p-4 font-semibold">
                    <span>Indicadores de veículos e pessoas</span>
                    <span class="text-xl transition-transform group-open:rotate-90">&gt;</span>
                </summary>
            <section class="space-y-4">
                <div class="border-b pb-2">
                    <h2 class="text-xl font-semibold">Indicadores de veículos e pessoas</h2>
                    <p class="text-sm text-muted-foreground">
                        Comparativo geral entre proprietários, veículos e marcas.
                    </p>
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
            </section>
            </details>
            <details open class="group space-y-4">
                <summary class="flex cursor-pointer list-none items-center justify-between rounded-xl border p-4 font-semibold">
                    <span>Pessoas</span>
                    <span class="text-xl transition-transform group-open:rotate-90">&gt;</span>
                </summary>
            <section class="space-y-4">
                <div class="border-b pb-2">
                    <h2 class="text-xl font-semibold">Pessoas</h2>
                    <p class="text-sm text-muted-foreground">
                        Indicadores relacionados aos proprietários cadastrados.
                    </p>
                </div>
            <section class="rounded-xl border">
                <div class="border-b p-5">
                    <h2 class="font-semibold">Todas as pessoas</h2>
                    <p class="mt-1 text-sm text-muted-foreground">
                        Pessoas cadastradas em ordem alfabética.
                    </p>
                </div>

                <div v-if="peopleByCity.length" class="border-b p-5">
                    <div class="mb-4 flex items-center justify-between">
                        <h3 class="font-semibold">Pessoas por cidade</h3>
                        <span class="text-xs text-muted-foreground">Quantidade</span>
                    </div>

                    <div class="space-y-4">
                        <div v-for="item in peopleByCity" :key="item.city">
                            <div class="mb-1 flex justify-between gap-4 text-sm">
                                <span class="truncate">{{ item.city }}</span>
                                <span>{{ item.total }}</span>
                            </div>
                            <div class="h-3 overflow-hidden rounded-full bg-muted">
                                <div
                                    class="h-full rounded-full bg-primary"
                                    :style="{ width: `${(Number(item.total) / maxPeopleByCity) * 100}%` }"
                                />
                            </div>
                        </div>
                    </div>
                </div>

                <p v-if="allPeople.length === 0" class="p-5 text-muted-foreground">
                    Nenhuma pessoa cadastrada.
                </p>

                <div v-else class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="border-b bg-muted/50">
                            <tr>
                                <th class="px-5 py-3">Nome</th>
                                <th class="px-5 py-3">CPF</th>
                                <th class="px-5 py-3">Gênero</th>
                                <th class="px-5 py-3">Nascimento</th>
                                <th class="px-5 py-3">Contato</th>
                                <th class="px-5 py-3">Cidade/UF</th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr
                                v-for="person in allPeople"
                                :key="person.id"
                                class="border-b last:border-0"
                            >
                                <td class="px-5 py-3">{{ person.name }}</td>
                                <td class="px-5 py-3">{{ person.cpf }}</td>
                                <td class="px-5 py-3">
                                    {{ person.gender ?? 'Não informado' }}
                                </td>
                                <td class="px-5 py-3">
                                    {{ formatDate(person.birth_date) }}
                                </td>
                                <td class="px-5 py-3">
                                    <div>{{ person.phone ?? 'Não informado' }}</div>
                                    <div class="text-muted-foreground">
                                        {{ person.email ?? 'Não informado' }}
                                    </div>
                                </td>
                                <td class="px-5 py-3">
                                    {{ person.city ? `${person.city}/${person.state ?? ''}` : 'Não informado' }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
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
            </section>
            </details>
            <details open class="group space-y-4">
                <summary class="flex cursor-pointer list-none items-center justify-between rounded-xl border p-4 font-semibold">
                    <span>Veículos</span>
                    <span class="text-xl transition-transform group-open:rotate-90">&gt;</span>
                </summary>
            <section class="space-y-4">
                <div class="border-b pb-2">
                    <h2 class="text-xl font-semibold">Veículos</h2>
                    <p class="text-sm text-muted-foreground">
                        Informações sobre os veículos e suas marcas.
                    </p>
                </div>
            <section class="rounded-xl border">
                <div class="border-b p-5">
                    <h2 class="font-semibold">Todos os veículos</h2>
                    <p class="mt-1 text-sm text-muted-foreground">
                        Lista completa dos veículos cadastrados no sistema.
                    </p>
                </div>

                <div v-if="vehiclesByYear.length" class="border-b p-5">
                    <div class="mb-4 flex items-center justify-between">
                        <h3 class="font-semibold">Veículos por ano</h3>
                        <span class="text-xs text-muted-foreground">Quantidade</span>
                    </div>

                    <div class="flex h-48 items-end gap-3 overflow-x-auto border-b border-l px-4 pb-2 pt-6">
                        <div
                            v-for="item in vehiclesByYear"
                            :key="item.year"
                            class="flex min-w-16 flex-1 flex-col items-center justify-end gap-2"
                        >
                            <span class="text-sm font-semibold">{{ item.total }}</span>
                            <div
                                class="w-full max-w-12 rounded-t-md bg-primary"
                                :style="{ height: `${(Number(item.total) / maxVehiclesByYear) * 100}%`, minHeight: '6px' }"
                            />
                            <span class="text-xs text-muted-foreground">{{ item.year }}</span>
                        </div>
                    </div>
                </div>

                <div v-if="allVehicles.length" class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="border-b bg-muted/50">
                            <tr>
                                <th class="px-5 py-3">Placa</th>
                                <th class="px-5 py-3">Marca</th>
                                <th class="px-5 py-3">Modelo</th>
                                <th class="px-5 py-3">Ano</th>
                                <th class="px-5 py-3">Proprietário</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="vehicle in allVehicles" :key="vehicle.id" class="border-b last:border-0">
                                <td class="px-5 py-3">{{ vehicle.plate }}</td>
                                <td class="px-5 py-3">{{ vehicle.brand }}</td>
                                <td class="px-5 py-3">{{ vehicle.model }}</td>
                                <td class="px-5 py-3">{{ vehicle.year }}</td>
                                <td class="px-5 py-3">{{ vehicle.person?.name ?? 'Não informado' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <p v-else class="p-5 text-muted-foreground">
                    Nenhum veículo cadastrado.
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
            </section>
            </details>
        </div>
    </AppLayout>
</template>
