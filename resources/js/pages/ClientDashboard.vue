<script setup lang="ts">
import { Head } from '@inertiajs/vue3';

interface Revision {
    id: number;
    maintenance_type: string | null;
    revision_date: string;
    mileage: number | null;
    description: string | null;
    cost: string | number | null;
    next_revision_date: string | null;
}

interface Vehicle {
    id: number;
    plate: string;
    brand: string;
    model: string;
    year: number | null;
    color: string | null;
    revisions: Revision[];
}

interface Person {
    name: string;
    cpf: string;
    email: string | null;
    phone: string | null;
    city: string | null;
    state: string | null;
    vehicles: Vehicle[];
}

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Meu painel',
                href: '/dashboard',
            },
        ],
    },
});

defineProps<{
    person: Person;
}>();

function formatDate(date: string | null): string {
    if (!date) {
        return 'Não informado';
    }

    const normalizedDate = date.length === 10 ? `${date}T00:00:00` : date;

    return new Intl.DateTimeFormat('pt-BR').format(new Date(normalizedDate));
}
</script>

<template>
    <Head title="Meu painel" />

    <div class="flex flex-1 flex-col gap-6 p-6">
        <div>
            <h1 class="text-2xl font-semibold">Olá, {{ person.name }}</h1>
            <p class="text-muted-foreground">
                Consulte seus dados, veículos e revisões.
            </p>
        </div>

        <section class="rounded-xl border p-5">
            <h2 class="mb-4 text-lg font-semibold">Meus dados</h2>
            <div class="grid gap-4 text-sm md:grid-cols-2 lg:grid-cols-4">
                <div>
                    <p class="text-muted-foreground">Nome</p>
                    <p class="font-medium">{{ person.name }}</p>
                </div>
                <div>
                    <p class="text-muted-foreground">CPF</p>
                    <p class="font-medium">{{ person.cpf }}</p>
                </div>
                <div>
                    <p class="text-muted-foreground">E-mail</p>
                    <p class="font-medium">{{ person.email || 'Não informado' }}</p>
                </div>
                <div>
                    <p class="text-muted-foreground">Localização</p>
                    <p class="font-medium">
                        {{ person.city ? `${person.city}/${person.state ?? ''}` : 'Não informado' }}
                    </p>
                </div>
            </div>
        </section>

        <section>
            <div class="mb-3 flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold">Meus veículos</h2>
                    <p class="text-sm text-muted-foreground">
                        {{ person.vehicles.length }} veículo(s) cadastrado(s).
                    </p>
                </div>
            </div>

            <div v-if="person.vehicles.length" class="overflow-x-auto rounded-xl border">
                <table class="w-full text-left text-sm">
                    <thead class="border-b bg-muted/50">
                        <tr>
                            <th class="px-4 py-3">Placa</th>
                            <th class="px-4 py-3">Marca</th>
                            <th class="px-4 py-3">Modelo</th>
                            <th class="px-4 py-3">Ano</th>
                            <th class="px-4 py-3">Cor</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="vehicle in person.vehicles" :key="vehicle.id" class="border-b last:border-0">
                            <td class="px-4 py-3 font-medium">{{ vehicle.plate }}</td>
                            <td class="px-4 py-3">{{ vehicle.brand }}</td>
                            <td class="px-4 py-3">{{ vehicle.model }}</td>
                            <td class="px-4 py-3">{{ vehicle.year || 'Não informado' }}</td>
                            <td class="px-4 py-3">{{ vehicle.color || 'Não informado' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div v-else class="rounded-xl border p-6 text-sm text-muted-foreground">
                Você ainda não possui veículos cadastrados.
            </div>
        </section>

        <section>
            <h2 class="mb-3 text-lg font-semibold">Minhas revisões</h2>

            <div
                v-if="person.vehicles.some((vehicle) => vehicle.revisions.length)"
                class="space-y-4"
            >
                <div
                    v-for="vehicle in person.vehicles.filter((item) => item.revisions.length)"
                    :key="vehicle.id"
                    class="rounded-xl border p-5"
                >
                    <h3 class="mb-3 font-semibold">{{ vehicle.plate }} — {{ vehicle.brand }} {{ vehicle.model }}</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="border-b bg-muted/50">
                                <tr>
                                    <th class="px-4 py-3">Data</th>
                                    <th class="px-4 py-3">Tipo</th>
                                    <th class="px-4 py-3">Quilometragem</th>
                                    <th class="px-4 py-3">Próxima revisão</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="revision in vehicle.revisions" :key="revision.id" class="border-b last:border-0">
                                    <td class="px-4 py-3">{{ formatDate(revision.revision_date) }}</td>
                                    <td class="px-4 py-3">{{ revision.maintenance_type || 'Não informado' }}</td>
                                    <td class="px-4 py-3">{{ revision.mileage || 'Não informado' }}</td>
                                    <td class="px-4 py-3">{{ formatDate(revision.next_revision_date) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div v-else class="rounded-xl border p-6 text-sm text-muted-foreground">
                Você ainda não possui revisões cadastradas.
            </div>
        </section>
    </div>
</template>
