<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';

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
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Relatórios',
        href: '/reports',
    },
];
//GRAFICOS
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
