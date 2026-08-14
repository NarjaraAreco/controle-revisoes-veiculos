<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';

interface Person {
    id: number;
    name: string;
    cpf: string;
    email: string | null;
    city: string | null;
    state: string | null;
}

defineProps<{
    people: Person[];
}>();

const deleteForm = useForm({});

function deletePerson(id: number) {
    if (!window.confirm('Deseja realmente excluir esta pessoa?')) {
        return;
    }

    deleteForm.delete(`/people/${id}`);
}
const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Pessoas',
        href: '/people',
    },
];
</script>

<template>

    <Head title="Pessoas" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold">Pessoas</h1>
                    <p class="text-muted-foreground">
                        Clientes e proprietários cadastrados.
                    </p>
                </div>

                <Link href="/people/create" class="rounded-md bg-primary px-4 py-2 text-primary-foreground">
                    Nova pessoa
                </Link>
            </div>

            <div v-if="people.length === 0" class="rounded-lg border p-6">
                Nenhuma pessoa cadastrada.
            </div>

            <div v-else class="overflow-x-auto rounded-lg border">
                <table class="w-full text-left text-sm">
                    <thead class="border-b bg-muted/50">
                        <tr>
                            <th class="px-4 py-3">Nome</th>
                            <th class="px-4 py-3">CPF</th>
                            <th class="px-4 py-3">E-mail</th>
                            <th class="px-4 py-3">Cidade/UF</th>
                            <th class="px-4 py-3">Ações</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr v-for="person in people" :key="person.id" class="border-b last:border-0">
                            <td class="px-4 py-3">{{ person.name }}</td>
                            <td class="px-4 py-3">{{ person.cpf }}</td>
                            <td class="px-4 py-3">
                                {{ person.email || 'Não informado' }}
                            </td>
                            <td class="px-4 py-3">
                                {{
                                    person.city
                                        ? `${person.city}/${person.state ?? ''}`
                                        : 'Não informado'
                                }}
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex gap-2">
                                    <Link :href="`/vehicles/create?person_id=${person.id}`"
                                        class="rounded-md border px-3 py-1">
                                        Novo veículo
                                    </Link>

                                    <Link :href="`/people/${person.id}/edit`" class="rounded-md border px-3 py-1">
                                        Editar
                                    </Link>

                                    <button type="button" :disabled="deleteForm.processing"
                                        class="rounded-md border border-red-500 px-3 py-1 text-red-500 disabled:cursor-not-allowed disabled:opacity-50"
                                        @click="deletePerson(person.id)">
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