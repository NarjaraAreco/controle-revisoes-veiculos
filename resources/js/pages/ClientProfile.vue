<script setup lang="ts">
import { Head } from '@inertiajs/vue3';

interface Person {
    id: number;
    name: string;
    cpf: string;
    birth_date: string | null;
    gender: string | null;
    phone: string | null;
    email: string | null;
    cep: string | null;
    street: string | null;
    number: string | null;
    complement: string | null;
    neighborhood: string | null;
    city: string | null;
    state: string | null;
}

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Meu perfil',
                href: '/client/profile',
            },
        ],
    },
});

defineProps<{
    person: Person;
}>();

function display(value: string | null): string {
    return value || 'Não informado';
}

function formatDate(date: string | null): string {
    if (!date) {
        return 'Não informado';
    }

    const normalizedDate = date.length === 10 ? `${date}T00:00:00` : date;

    return new Intl.DateTimeFormat('pt-BR').format(new Date(normalizedDate));
}
</script>

<template>
    <Head title="Meu perfil" />

    <div class="flex flex-1 flex-col gap-6 p-6">
        <div>
            <h1 class="text-2xl font-semibold">Meu perfil</h1>
            <p class="text-muted-foreground">
                Confira os dados cadastrados pelo administrador.
            </p>
        </div>

        <section class="rounded-xl border p-5">
            <h2 class="mb-4 text-lg font-semibold">Dados pessoais</h2>
            <div class="grid gap-5 text-sm md:grid-cols-2 lg:grid-cols-3">
                <div>
                    <p class="text-muted-foreground">Nome</p>
                    <p class="font-medium">{{ person.name }}</p>
                </div>
                <div>
                    <p class="text-muted-foreground">CPF</p>
                    <p class="font-medium">{{ person.cpf }}</p>
                </div>
                <div>
                    <p class="text-muted-foreground">Data de nascimento</p>
                    <p class="font-medium">{{ formatDate(person.birth_date) }}</p>
                </div>
                <div>
                    <p class="text-muted-foreground">Gênero</p>
                    <p class="font-medium">{{ display(person.gender) }}</p>
                </div>
                <div>
                    <p class="text-muted-foreground">Telefone</p>
                    <p class="font-medium">{{ display(person.phone) }}</p>
                </div>
                <div>
                    <p class="text-muted-foreground">E-mail</p>
                    <p class="font-medium">{{ display(person.email) }}</p>
                </div>
            </div>
        </section>

        <section class="rounded-xl border p-5">
            <h2 class="mb-4 text-lg font-semibold">Endereço</h2>
            <div class="grid gap-5 text-sm md:grid-cols-2 lg:grid-cols-3">
                <div>
                    <p class="text-muted-foreground">CEP</p>
                    <p class="font-medium">{{ display(person.cep) }}</p>
                </div>
                <div>
                    <p class="text-muted-foreground">Rua</p>
                    <p class="font-medium">{{ display(person.street) }}</p>
                </div>
                <div>
                    <p class="text-muted-foreground">Número</p>
                    <p class="font-medium">{{ display(person.number) }}</p>
                </div>
                <div>
                    <p class="text-muted-foreground">Complemento</p>
                    <p class="font-medium">{{ display(person.complement) }}</p>
                </div>
                <div>
                    <p class="text-muted-foreground">Bairro</p>
                    <p class="font-medium">{{ display(person.neighborhood) }}</p>
                </div>
                <div>
                    <p class="text-muted-foreground">Cidade/UF</p>
                    <p class="font-medium">
                        {{ person.city ? `${person.city}/${person.state ?? ''}` : 'Não informado' }}
                    </p>
                </div>
            </div>
        </section>
    </div>
</template>
