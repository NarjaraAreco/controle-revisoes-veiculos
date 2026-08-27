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

interface VehiclesByGender {
    gender: string;
    total: number | string;
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
    vehiclesByGender,
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
    vehiclesByGender: VehiclesByGender[];
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

type SortDirection = 'asc' | 'desc';

interface SortState<Key extends string> {
    key: Key | null;
    direction: SortDirection;
}

type PersonSortKey = 'name' | 'cpf' | 'gender' | 'birth_date' | 'contact' | 'city';
type VehicleSortKey = 'plate' | 'brand' | 'model' | 'year' | 'person';
type RevisionSortKey = 'revision_date' | 'person' | 'vehicle' | 'maintenance_type' | 'mileage' | 'cost';

const peopleSort = reactive<SortState<PersonSortKey>>({ key: null, direction: 'asc' });
const vehiclesSort = reactive<SortState<VehicleSortKey>>({ key: null, direction: 'asc' });
const revisionsSort = reactive<SortState<RevisionSortKey>>({ key: null, direction: 'asc' });

function toggleSort<Key extends string>(state: SortState<Key>, key: Key) {
    if (state.key === key) {
        state.direction = state.direction === 'asc' ? 'desc' : 'asc';

        return;
    }

    state.key = key;
    state.direction = 'asc';
}

function sortIndicator<Key extends string>(state: SortState<Key>, key: Key): string {
    return state.key !== key ? '↕' : state.direction === 'asc' ? '↑' : '↓';
}

function compareSortValues(left: string | number | null | undefined, right: string | number | null | undefined): number {
    if (left === null || left === undefined || left === '') {
        return right === null || right === undefined || right === '' ? 0 : 1;
    }

    if (right === null || right === undefined || right === '') {
        return -1;
    }

    if (typeof left === 'number' && typeof right === 'number') {
        return left - right;
    }

    return String(left).localeCompare(String(right), 'pt-BR', {
        numeric: true,
        sensitivity: 'base',
    });
}

function sortedItems<T, Key extends string>(
    items: T[],
    state: SortState<Key>,
    getValue: (item: T, key: Key) => string | number | null | undefined,
): T[] {
    if (!state.key) {
        return items;
    }

    return [...items].sort((left, right) => {
        const result = compareSortValues(getValue(left, state.key as Key), getValue(right, state.key as Key));

        return state.direction === 'asc' ? result : -result;
    });
}

const sortedPeople = computed(() => sortedItems(allPeople, peopleSort, (person, key) => {
    switch (key) {
        case 'cpf':
            return person.cpf;
        case 'gender':
            return person.gender;
        case 'birth_date':
            return person.birth_date;
        case 'contact':
            return `${person.phone ?? ''} ${person.email ?? ''}`;
        case 'city':
            return `${person.city ?? ''} ${person.state ?? ''}`;
        default:
            return person.name;
    }
}));

const sortedVehicles = computed(() => sortedItems(allVehicles, vehiclesSort, (vehicle, key) => {
    switch (key) {
        case 'brand':
            return vehicle.brand;
        case 'model':
            return vehicle.model;
        case 'year':
            return vehicle.year;
        case 'person':
            return vehicle.person?.name;
        default:
            return vehicle.plate;
    }
}));

const sortedRevisions = computed(() => sortedItems(revisionsInPeriod, revisionsSort, (revision, key) => {
    switch (key) {
        case 'person':
            return revision.vehicle.person?.name;
        case 'vehicle':
            return `${revision.vehicle.plate} ${revision.vehicle.brand} ${revision.vehicle.model}`;
        case 'maintenance_type':
            return formatMaintenanceType(revision.maintenance_type);
        case 'mileage':
            return revision.mileage;
        case 'cost':
            return revision.cost === null ? null : Number(revision.cost);
        default:
            return revision.revision_date;
    }
}));

//GRAFICOS ------ BARRA
const maxPeopleByGender = Math.max(
    ...peopleByGender.map((item) => Number(item.total)),
    1,
);

const maxVehiclesByPerson = Math.max(
    ...vehiclesByPerson.map((person) => person.vehicles.length),
    1,
);

const maxBrandsByGender = Math.max(
    ...brandsByGender.map((item) => Number(item.total)),
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

interface ChartSegment {
    label: string;
    value: number;
    percent: number;
    color: string;
}

const chartColors = [
    'var(--chart-1)',
    'var(--chart-2)',
    'var(--chart-3)',
    'var(--chart-4)',
    'var(--chart-5)',
    'var(--chart-6)',
    'var(--chart-7)',
    'var(--chart-8)',
    'var(--chart-9)',
    'var(--chart-10)',
];

function chartSegments(items: Array<{ label: string; value: number }>): ChartSegment[] {
    const total = items.reduce((sum, item) => sum + item.value, 0);

    if (!total) {
        return [];
    }

    return items.map((item, index) => ({
        ...item,
        percent: (item.value / total) * 100,
        color: chartColors[index % chartColors.length],
    }));
}

function pieGradient(segments: ChartSegment[]): string {
    let cursor = 0;

    return segments.length
        ? `conic-gradient(${segments.map((segment) => {
            const start = cursor;
            cursor += segment.percent;
            return `${segment.color} ${start}% ${cursor}%`;
        }).join(', ')})`
        : 'var(--muted)';
}

const revisionsByBrandChart = computed(() => chartSegments(
    revisionsByBrand.map((item) => ({
        label: item.brand,
        value: Number(item.total),
    })),
));

const peopleByRevisionChart = computed(() => chartSegments(
    peopleByRevisionCount.map((person) => ({
        label: person.name,
        value: Number(person.total),
    })),
));

const vehiclesByBrandChart = computed(() => chartSegments(
    vehiclesByBrand.map((item) => ({
        label: item.brand,
        value: Number(item.total),
    })),
));

const peopleWithVehiclesChart = computed(() => chartSegments(
    peopleWithVehicles.filter((person) => Number(person.vehicles_count) > 0).map((person) => ({
        label: person.name,
        value: Number(person.vehicles_count),
    })),
));

const vehiclesByGenderChart = computed(() => chartSegments(
    vehiclesByGender.map((item) => ({
        label: item.gender,
        value: Number(item.total),
    })),
));

const totalVehiclesByGender = computed(() => vehiclesByGenderChart.value
    .reduce((total, item) => total + item.value, 0));

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

                <div v-else class="max-h-[32rem] overflow-auto">
                    <table class="w-full min-w-[900px] text-left text-sm">
                        <thead class="sticky top-0 z-10 border-b bg-muted/95">
                            <tr>
                                <th class="px-5 py-3">
                                    <button type="button" class="inline-flex items-center gap-1 hover:text-primary" @click="toggleSort(revisionsSort, 'revision_date')">
                                        Data <span aria-hidden="true">{{ sortIndicator(revisionsSort, 'revision_date') }}</span>
                                    </button>
                                </th>
                                <th class="px-5 py-3">
                                    <button type="button" class="inline-flex items-center gap-1 hover:text-primary" @click="toggleSort(revisionsSort, 'person')">
                                        Pessoa <span aria-hidden="true">{{ sortIndicator(revisionsSort, 'person') }}</span>
                                    </button>
                                </th>
                                <th class="px-5 py-3">
                                    <button type="button" class="inline-flex items-center gap-1 hover:text-primary" @click="toggleSort(revisionsSort, 'vehicle')">
                                        Veículo <span aria-hidden="true">{{ sortIndicator(revisionsSort, 'vehicle') }}</span>
                                    </button>
                                </th>
                                <th class="px-5 py-3">
                                    <button type="button" class="inline-flex items-center gap-1 hover:text-primary" @click="toggleSort(revisionsSort, 'maintenance_type')">
                                        Tipo <span aria-hidden="true">{{ sortIndicator(revisionsSort, 'maintenance_type') }}</span>
                                    </button>
                                </th>
                                <th class="px-5 py-3">
                                    <button type="button" class="inline-flex items-center gap-1 hover:text-primary" @click="toggleSort(revisionsSort, 'mileage')">
                                        Quilometragem <span aria-hidden="true">{{ sortIndicator(revisionsSort, 'mileage') }}</span>
                                    </button>
                                </th>
                                <th class="px-5 py-3">
                                    <button type="button" class="inline-flex items-center gap-1 hover:text-primary" @click="toggleSort(revisionsSort, 'cost')">
                                        Custo <span aria-hidden="true">{{ sortIndicator(revisionsSort, 'cost') }}</span>
                                    </button>
                                </th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr v-for="revision in sortedRevisions" :key="revision.id" class="border-b last:border-0">
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
            <div class="grid gap-4 lg:grid-cols-2">
                <section class="rounded-xl border">
                    <div class="border-b p-5">
                        <h2 class="font-semibold">Marcas com mais revisões</h2>
                    </div>

                    <div v-if="revisionsByBrandChart.length > 0" class="grid gap-6 p-5 sm:grid-cols-[minmax(12rem,16rem)_1fr] sm:items-center">
                        <div class="mx-auto aspect-square w-full max-w-56 rounded-full" role="img" aria-label="Gráfico de pizza de revisões por marca"
                            :style="{ background: pieGradient(revisionsByBrandChart) }" />
                        <div class="space-y-2">
                            <div v-for="item in revisionsByBrandChart" :key="item.label" class="flex items-center justify-between gap-3 text-sm">
                                <span class="flex min-w-0 items-center gap-2">
                                    <span class="size-3 shrink-0 rounded-full" :style="{ backgroundColor: item.color }" />
                                    <span class="truncate">{{ item.label }}</span>
                                </span>
                                <span class="font-medium">{{ item.value }} ({{ item.percent.toFixed(1) }}%)</span>
                            </div>
                        </div>
                    </div>

                    <p v-else class="p-5 text-muted-foreground">Nenhuma revisão encontrada no período.</p>
                </section>

                <section class="rounded-xl border">
                    <div class="border-b p-5">
                        <h2 class="font-semibold">Pessoas com mais revisões</h2>
                    </div>

                    <div v-if="peopleByRevisionChart.length > 0" class="grid gap-6 p-5 sm:grid-cols-[minmax(12rem,16rem)_1fr] sm:items-center">
                        <div class="mx-auto aspect-square w-full max-w-56 rounded-full" role="img" aria-label="Gráfico de pizza de pessoas com mais revisões"
                            :style="{ background: pieGradient(peopleByRevisionChart) }" />
                        <div class="max-h-56 space-y-2 overflow-auto pr-1">
                            <div v-for="item in peopleByRevisionChart" :key="`chart-revision-person-${item.label}`" class="flex items-center justify-between gap-3 text-sm">
                                <span class="flex min-w-0 items-center gap-2">
                                    <span class="size-3 shrink-0 rounded-full" :style="{ backgroundColor: item.color }" />
                                    <span class="truncate">{{ item.label }}</span>
                                </span>
                                <span class="font-medium">{{ item.value }} ({{ item.percent.toFixed(1) }}%)</span>
                            </div>
                        </div>
                    </div>

                    <p v-else class="p-5 text-muted-foreground">Nenhuma pessoa possui revisões no período.</p>
                </section>
            </div>
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
                        <h3 class="font-semibold">Dias até a próxima revisão</h3>
                        <span class="text-xs text-muted-foreground">Quanto menor, mais próxima</span>
                    </div>

                    <div class="max-h-[28rem] space-y-3 overflow-auto pr-1">
                        <div v-for="item in nextRevisions" :key="`chart-next-${item.person_id}`" class="grid grid-cols-[minmax(7rem,11rem)_1fr_auto] items-center gap-3">
                            <div class="min-w-0">
                                <p class="truncate text-sm font-medium">{{ item.name }}</p>
                                <p class="text-xs text-muted-foreground">{{ formatDate(item.next_revision_date) }}</p>
                            </div>
                            <div class="h-3 overflow-hidden rounded-full bg-muted" role="img" :aria-label="`${item.name}: ${daysUntil(item.next_revision_date)} dias até a revisão`">
                                <div class="h-full rounded-full bg-primary transition-all"
                                    :style="{ width: `${(daysUntil(item.next_revision_date) / maxNextRevisionDays) * 100}%`, minWidth: '8px' }" />
                            </div>
                            <span class="whitespace-nowrap text-sm font-medium">{{ daysUntil(item.next_revision_date) }} d</span>
                        </div>
                    </div>
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
            <div class="grid gap-6 lg:grid-cols-2">
                <section class="rounded-xl border">
                    <div class="border-b p-5">
                        <h2 class="font-semibold">Veículos por marca</h2>
                    </div>
                    <div v-if="vehiclesByBrandChart.length > 0" class="grid gap-6 p-5 sm:grid-cols-[minmax(12rem,16rem)_1fr] sm:items-center">
                        <div class="mx-auto aspect-square w-full max-w-56 rounded-full" role="img" aria-label="Gráfico de pizza de veículos por marca"
                            :style="{ background: pieGradient(vehiclesByBrandChart) }" />
                        <div class="space-y-2">
                            <div v-for="item in vehiclesByBrandChart" :key="`chart-${item.label}`" class="flex items-center justify-between gap-3 text-sm">
                                <span class="flex min-w-0 items-center gap-2">
                                    <span class="size-3 shrink-0 rounded-full" :style="{ backgroundColor: item.color }" />
                                    <span class="truncate">{{ item.label }}</span>
                                </span>
                                <span class="font-medium">{{ item.value }} ({{ item.percent.toFixed(1) }}%)</span>
                            </div>
                        </div>
                    </div>
                    <div v-if="vehiclesByBrand.length === 0" class="p-5 text-muted-foreground">
                        Nenhum veículo cadastrado.
                    </div>
                </section>

                <section class="rounded-xl border">
                    <div class="border-b p-5">
                        <h2 class="font-semibold">Pessoas com mais veículos</h2>
                    </div>
                    <div v-if="peopleWithVehiclesChart.length > 0" class="grid gap-6 p-5 sm:grid-cols-[minmax(12rem,16rem)_1fr] sm:items-center">
                        <div class="mx-auto aspect-square w-full max-w-56 rounded-full" role="img" aria-label="Gráfico de pizza de pessoas com mais veículos"
                            :style="{ background: pieGradient(peopleWithVehiclesChart) }" />
                        <div class="space-y-2">
                            <div v-for="item in peopleWithVehiclesChart" :key="`chart-person-${item.label}`" class="flex items-center justify-between gap-3 text-sm">
                                <span class="flex min-w-0 items-center gap-2">
                                    <span class="size-3 shrink-0 rounded-full" :style="{ backgroundColor: item.color }" />
                                    <span class="truncate">{{ item.label }}</span>
                                </span>
                                <span class="font-medium">{{ item.value }} ({{ item.percent.toFixed(1) }}%)</span>
                            </div>
                        </div>
                    </div>
                    <div v-if="peopleWithVehiclesChart.length === 0" class="p-5 text-muted-foreground">
                        Nenhuma pessoa cadastrada.
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

                    <div class="flex h-64 items-end gap-1 overflow-x-auto border-b border-l px-3 pb-2 pt-8">
                        <div v-for="item in peopleByCity" :key="item.city" class="flex min-w-14 flex-1 flex-col items-center justify-end gap-2">
                            <span class="text-sm font-medium">{{ item.total }}</span>
                            <div class="w-full max-w-10 rounded-t-md bg-primary" role="img" :aria-label="`${item.city}: ${item.total} pessoa(s)`"
                                :style="{ height: `${(Number(item.total) / maxPeopleByCity) * 100}%`, minHeight: '8px' }" />
                            <span class="max-w-20 truncate text-center text-xs text-muted-foreground">{{ item.city }}</span>
                        </div>
                    </div>
                </div>

                <p v-if="allPeople.length === 0" class="p-5 text-muted-foreground">
                    Nenhuma pessoa cadastrada.
                </p>

                <div v-else class="max-h-[22rem] overflow-auto">
                    <table class="w-full min-w-[900px] text-left text-sm">
                        <thead class="sticky top-0 z-10 border-b bg-muted/95">
                            <tr>
                                <th class="px-5 py-3">
                                    <button type="button" class="inline-flex items-center gap-1 hover:text-primary" @click="toggleSort(peopleSort, 'name')">
                                        Nome <span aria-hidden="true">{{ sortIndicator(peopleSort, 'name') }}</span>
                                    </button>
                                </th>
                                <th class="px-5 py-3">
                                    <button type="button" class="inline-flex items-center gap-1 hover:text-primary" @click="toggleSort(peopleSort, 'cpf')">
                                        CPF <span aria-hidden="true">{{ sortIndicator(peopleSort, 'cpf') }}</span>
                                    </button>
                                </th>
                                <th class="px-5 py-3">
                                    <button type="button" class="inline-flex items-center gap-1 hover:text-primary" @click="toggleSort(peopleSort, 'gender')">
                                        Gênero <span aria-hidden="true">{{ sortIndicator(peopleSort, 'gender') }}</span>
                                    </button>
                                </th>
                                <th class="px-5 py-3">
                                    <button type="button" class="inline-flex items-center gap-1 hover:text-primary" @click="toggleSort(peopleSort, 'birth_date')">
                                        Nascimento <span aria-hidden="true">{{ sortIndicator(peopleSort, 'birth_date') }}</span>
                                    </button>
                                </th>
                                <th class="px-5 py-3">
                                    <button type="button" class="inline-flex items-center gap-1 hover:text-primary" @click="toggleSort(peopleSort, 'contact')">
                                        Contato <span aria-hidden="true">{{ sortIndicator(peopleSort, 'contact') }}</span>
                                    </button>
                                </th>
                                <th class="px-5 py-3">
                                    <button type="button" class="inline-flex items-center gap-1 hover:text-primary" @click="toggleSort(peopleSort, 'city')">
                                        Cidade/UF <span aria-hidden="true">{{ sortIndicator(peopleSort, 'city') }}</span>
                                    </button>
                                </th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr
                                v-for="person in sortedPeople"
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
                            <div class="h-full rounded-full bg-gradient-to-r from-primary to-blue-400 transition-all" role="progressbar" :style="{
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

                <div v-if="vehiclesByGenderChart.length > 0" class="space-y-4 p-5">
                    <div class="flex h-12 w-full overflow-hidden rounded-md bg-muted" role="img" aria-label="Comparação de veículos por gênero">
                        <div v-for="item in vehiclesByGenderChart" :key="item.label" class="flex items-center justify-center px-3 text-sm font-medium text-primary-foreground transition-all"
                            :style="{ width: `${item.percent}%`, backgroundColor: item.color, minWidth: item.percent > 0 ? '4rem' : '0' }">
                            <span class="truncate">{{ item.label }}: {{ item.value }}</span>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-x-6 gap-y-2 text-sm">
                        <div v-for="item in vehiclesByGenderChart" :key="`legend-${item.label}`" class="flex items-center gap-2">
                            <span class="size-3 rounded-full" :style="{ backgroundColor: item.color }" />
                            <span>{{ item.label }}: {{ item.value }} veículo(s) ({{ item.percent.toFixed(1) }}%)</span>
                        </div>
                    </div>

                    <p class="text-sm text-muted-foreground">
                        Total: {{ totalVehiclesByGender }} veículo(s). O maior segmento indica o gênero com mais veículos.
                    </p>
                </div>

                <p v-else class="p-5 text-muted-foreground">
                    Não existem veículos vinculados a pessoas com gênero informado.
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
            <section class="rounded-xl border">
                <div class="border-b p-5">
                    <h2 class="font-semibold">Todos os veículos</h2>
                    <p class="mt-1 text-sm text-muted-foreground">
                        Lista completa dos veículos cadastrados no sistema.
                    </p>
                </div>

                <div v-if="allVehicles.length" class="max-h-[22rem] overflow-auto">
                    <table class="w-full min-w-[800px] text-left text-sm">
                        <thead class="sticky top-0 z-10 border-b bg-muted/95">
                            <tr>
                                <th class="px-5 py-3">
                                    <button type="button" class="inline-flex items-center gap-1 hover:text-primary" @click="toggleSort(vehiclesSort, 'plate')">
                                        Placa <span aria-hidden="true">{{ sortIndicator(vehiclesSort, 'plate') }}</span>
                                    </button>
                                </th>
                                <th class="px-5 py-3">
                                    <button type="button" class="inline-flex items-center gap-1 hover:text-primary" @click="toggleSort(vehiclesSort, 'brand')">
                                        Marca <span aria-hidden="true">{{ sortIndicator(vehiclesSort, 'brand') }}</span>
                                    </button>
                                </th>
                                <th class="px-5 py-3">
                                    <button type="button" class="inline-flex items-center gap-1 hover:text-primary" @click="toggleSort(vehiclesSort, 'model')">
                                        Modelo <span aria-hidden="true">{{ sortIndicator(vehiclesSort, 'model') }}</span>
                                    </button>
                                </th>
                                <th class="px-5 py-3">
                                    <button type="button" class="inline-flex items-center gap-1 hover:text-primary" @click="toggleSort(vehiclesSort, 'year')">
                                        Ano <span aria-hidden="true">{{ sortIndicator(vehiclesSort, 'year') }}</span>
                                    </button>
                                </th>
                                <th class="px-5 py-3">
                                    <button type="button" class="inline-flex items-center gap-1 hover:text-primary" @click="toggleSort(vehiclesSort, 'person')">
                                        Proprietário <span aria-hidden="true">{{ sortIndicator(vehiclesSort, 'person') }}</span>
                                    </button>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="vehicle in sortedVehicles" :key="vehicle.id" class="border-b last:border-0">
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
                    <h2 class="font-semibold">Veículos por ano</h2>
                    <p class="mt-1 text-sm text-muted-foreground">
                        Quantidade de veículos agrupada por ano.
                    </p>
                </div>

                <div v-if="vehiclesByYear.length" class="p-5">
                    <div class="mb-4 flex items-center justify-between">
                        <h3 class="font-semibold">Distribuição anual</h3>
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

                <p v-else class="p-5 text-muted-foreground">
                    Nenhum veículo cadastrado para agrupar por ano.
                </p>
            </section>
            <section class="rounded-xl border">
                <div class="border-b p-5">
                    <h2 class="font-semibold">Todos os veículos por pessoa</h2>

                    <p class="mt-1 text-sm text-muted-foreground">
                        Proprietários em ordem alfabética.
                    </p>
                </div>

                <div v-if="vehiclesByPerson.length > 0" class="max-h-[28rem] space-y-3 overflow-auto p-5">
                    <div v-for="person in vehiclesByPerson" :key="person.id" class="grid grid-cols-[minmax(7rem,11rem)_1fr_auto] items-center gap-3">
                        <span class="truncate text-sm font-medium">{{ person.name }}</span>
                        <div class="h-3 overflow-hidden rounded-full bg-muted" role="img" :aria-label="`${person.name}: ${person.vehicles.length} veículo(s)`">
                            <div class="h-full rounded-full bg-primary transition-all"
                                :style="{ width: `${(person.vehicles.length / maxVehiclesByPerson) * 100}%`, minWidth: '8px' }" />
                        </div>
                        <span class="whitespace-nowrap text-sm text-muted-foreground">{{ person.vehicles.length }}</span>
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
