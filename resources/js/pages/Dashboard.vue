<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';

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

defineProps<{
    totalPeople: number;
    totalVehicles: number;
    totalRevisions: number;
}>();

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
