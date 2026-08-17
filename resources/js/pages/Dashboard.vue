<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

interface VehicleByBrand {
    brand: string;
    total: number;
}

interface PersonWithVehicles {
    id: number;
    name: string;
    total: number;
}

interface RevisionByMonth {
    month: string;
    total: number;
}

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: '/dashboard',
            },
        ],
    },
});

const props = defineProps<{
    totalPeople: number;
    totalVehicles: number;
    totalRevisions: number;
    vehiclesByBrand: VehicleByBrand[];
    peopleWithVehicles: PersonWithVehicles[];
    revisionsByMonth: RevisionByMonth[];
}>();

const maxVehiclesByBrand = computed(() => Math.max(
    1,
    ...props.vehiclesByBrand.map((item) => item.total),
));

const maxPeopleWithVehicles = computed(() => Math.max(
    1,
    ...props.peopleWithVehicles.map((item) => item.total),
));

const maxRevisionsByMonth = computed(() => Math.max(
    1,
    ...props.revisionsByMonth.map((item) => item.total),
));

function formatMonth(month: string): string {
    const [year, monthNumber] = month.split('-').map(Number);

    return new Intl.DateTimeFormat('pt-BR', {
        month: 'short',
        year: '2-digit',
    }).format(new Date(year, monthNumber - 1, 1));
}

</script>

<template>
    <Head title="Dashboard" />

    <div class="flex flex-1 flex-col gap-6 p-6">
        <div>
            <h1 class="text-2xl font-semibold">Controle de Revisões</h1>
            <p class="text-muted-foreground">
                Acesse rapidamente os módulos do sistema.
            </p>
        </div>

        <div>
            <h2 class="mb-3 text-lg font-semibold">Resumo geral</h2>
            <div class="grid gap-4 md:grid-cols-3">
                <div class="rounded-xl border p-5">
                    <p class="text-sm text-muted-foreground">Pessoas</p>
                    <p class="mt-2 text-3xl font-semibold">{{ totalPeople }}</p>
                </div>

                <div class="rounded-xl border p-5">
                    <p class="text-sm text-muted-foreground">Veículos</p>
                    <p class="mt-2 text-3xl font-semibold">{{ totalVehicles }}</p>
                </div>

                <div class="rounded-xl border p-5">
                    <p class="text-sm text-muted-foreground">Revisões</p>
                    <p class="mt-2 text-3xl font-semibold">{{ totalRevisions }}</p>
                </div>
            </div>
        </div>

        <div>
            <h2 class="mb-1 text-lg font-semibold">Gráficos</h2>
            <p class="mb-3 text-sm text-muted-foreground">
                Visualização dos principais dados do sistema.
            </p>

            <div class="grid gap-4 xl:grid-cols-2">
                <div class="rounded-xl border p-5">
                    <div class="mb-4 flex items-center justify-between">
                        <h3 class="font-semibold">Veículos por marca</h3>
                        <span class="text-xs text-muted-foreground">Quantidade</span>
                    </div>

                    <div v-if="vehiclesByBrand.length" class="flex h-64 items-end gap-3 border-b border-l px-4 pb-2 pt-6">
                        <div
                            v-for="item in vehiclesByBrand"
                            :key="item.brand"
                            class="flex min-w-0 flex-1 flex-col items-center justify-end gap-2"
                        >
                            <span class="text-sm font-semibold">{{ item.total }}</span>
                            <div
                                class="w-full max-w-12 rounded-t-md bg-primary transition-all"
                                :style="{ height: `${(item.total / maxVehiclesByBrand) * 100}%`, minHeight: '6px' }"
                                :title="`${item.brand}: ${item.total}`"
                            />
                            <span class="max-w-full truncate text-xs text-muted-foreground" :title="item.brand">
                                {{ item.brand }}
                            </span>
                        </div>
                    </div>
                    <p v-else class="py-20 text-center text-sm text-muted-foreground">
                        Nenhum veículo cadastrado.
                    </p>
                </div>

                <div class="rounded-xl border p-5">
                    <div class="mb-4 flex items-center justify-between">
                        <h3 class="font-semibold">Revisões por mês</h3>
                        <span class="text-xs text-muted-foreground">Quantidade</span>
                    </div>

                    <div v-if="revisionsByMonth.length" class="flex h-64 items-end gap-3 border-b border-l px-4 pb-2 pt-6">
                        <div
                            v-for="item in revisionsByMonth"
                            :key="item.month"
                            class="flex min-w-0 flex-1 flex-col items-center justify-end gap-2"
                        >
                            <span class="text-sm font-semibold">{{ item.total }}</span>
                            <div
                                class="w-full max-w-12 rounded-t-md bg-primary transition-all"
                                :style="{ height: `${(item.total / maxRevisionsByMonth) * 100}%`, minHeight: '6px' }"
                                :title="`${formatMonth(item.month)}: ${item.total}`"
                            />
                            <span class="max-w-full truncate text-xs text-muted-foreground">
                                {{ formatMonth(item.month) }}
                            </span>
                        </div>
                    </div>
                    <p v-else class="py-20 text-center text-sm text-muted-foreground">
                        Nenhuma revisão cadastrada.
                    </p>
                </div>

                <div v-if="false" class="rounded-xl border p-5 xl:col-span-2">
                    <div class="mb-4 flex items-center justify-between">
                        <h3 class="font-semibold">Veículos por pessoa</h3>
                        <span class="text-xs text-muted-foreground">Quantidade</span>
                    </div>

                    <div v-if="peopleWithVehicles.length" class="space-y-4">
                        <div v-for="person in peopleWithVehicles" :key="person.id">
                            <div class="mb-1 flex items-center justify-between gap-4 text-sm">
                                <span class="truncate">{{ person.name }}</span>
                                <span class="font-semibold">{{ person.total }}</span>
                            </div>
                            <div class="h-3 overflow-hidden rounded-full bg-muted">
                                <div
                                    class="h-full rounded-full bg-primary transition-all"
                                    :style="{ width: `${(person.total / maxPeopleWithVehicles) * 100}%` }"
                                />
                            </div>
                        </div>
                    </div>
                    <p v-else class="py-8 text-center text-sm text-muted-foreground">
                        Nenhuma pessoa cadastrada.
                    </p>
                </div>
            </div>
        </div>

        <div>
            <h2 class="mb-3 text-lg font-semibold">Módulos</h2>
            <div class="grid gap-4 md:grid-cols-4">
            <Link href="/people" class="rounded-xl border p-6 transition hover:bg-muted">
                <h2 class="text-lg font-semibold">Pessoas</h2>
                <p class="mt-2 text-sm text-muted-foreground">
                    Gerencie proprietários cadastrados.
                </p>
            </Link>

            <Link href="/vehicles" class="rounded-xl border p-6 transition hover:bg-muted">
                <h2 class="text-lg font-semibold">Veículos</h2>
                <p class="mt-2 text-sm text-muted-foreground">
                    Consulte e gerencie veículos.
                </p>
            </Link>

            <Link href="/revisions" class="rounded-xl border p-6 transition hover:bg-muted">
                <h2 class="text-lg font-semibold">Revisões</h2>
                <p class="mt-2 text-sm text-muted-foreground">
                    Acompanhe as revisões realizadas.
                </p>
            </Link>

            <Link href="/reports" class="rounded-xl border p-6 transition hover:bg-muted">
                <h2 class="text-lg font-semibold">Relatórios</h2>
                <p class="mt-2 text-sm text-muted-foreground">
                    Consulte os indicadores e análises do sistema.
                </p>
            </Link>
            </div>
        </div>
    </div>
</template>
