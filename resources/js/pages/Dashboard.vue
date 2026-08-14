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

        <div class="grid gap-4 md:grid-cols-3">
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
        </div>
    </div>
</template>