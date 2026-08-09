<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
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

                <Link
                    href="/people/create"
                    class="rounded-md bg-primary px-4 py-2 text-primary-foreground"
                >
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
                            <th class="px-4 py-3">Cidade</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr
                            v-for="person in people"
                            :key="person.id"
                            class="border-b last:border-0"
                        >
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
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AppLayout>
</template>