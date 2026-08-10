<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { onMounted, ref } from 'vue';

interface Person {
    id: number;
    name: string;
}

interface Color {
    id: string;
    name: string;
}

interface Brand {
    id: string;
    name: string;
}

interface VehicleModel {
    id: string;
    name: string;
}

interface VehicleData {
    id: number;
    person_id: number;
    plate: string;
    brand: string;
    model: string;
    year: number;
    color: string | null;
}

const { vehicle, people } = defineProps<{
    vehicle: VehicleData;
    people: Person[];
}>();

const maxYear = new Date().getFullYear() + 1;

const colors = ref<Color[]>([]);
const colorsLoading = ref(false);
const colorsError = ref('');
const brands = ref<Brand[]>([]);
const brandsLoading = ref(false);
const brandsError = ref('');
const models = ref<VehicleModel[]>([]);
const modelsLoading = ref(false);
const modelsError = ref('');

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Veículos',
        href: '/vehicles',
    },
    {
        title: 'Atualize veículo',
        href: '/vehicles/create',
    },
];

const form = useForm({
    person_id: String(vehicle.person_id),
    plate: vehicle.plate,
    brand: vehicle.brand,
    model: vehicle.model,
    year: String(vehicle.year),
    color: vehicle.color ?? '',
});
//API cores
async function loadColors() {
    colorsLoading.value = true;
    colorsError.value = '';

    try {
        const response = await fetch('/api/colors');

        if (!response.ok) {
            throw new Error();
        }

        colors.value = await response.json();
    } catch {
        colorsError.value = 'Não foi possível carregar as cores.';
    } finally {
        colorsLoading.value = false;
    }
}

//API veiculos
async function loadBrands() {
    brandsLoading.value = true;
    brandsError.value = '';

    try {
        const response = await fetch('/api/brands');

        if (!response.ok) {
            throw new Error();
        }

        brands.value = await response.json();
    } catch {
        brandsError.value = 'Não foi possível carregar as marcas.';
    } finally {
        brandsLoading.value = false;
    }
}

async function loadModels() {
    const selectedBrand = brands.value.find(
        (brand) => brand.name === form.brand,
    );

    form.model = '';
    models.value = [];
    modelsError.value = '';

    if (!selectedBrand) {
        return;
    }

    modelsLoading.value = true;

    try {
        const response = await fetch(
            `/api/brands/${selectedBrand.id}/models`,
        );

        if (!response.ok) {
            throw new Error();
        }

        models.value = await response.json();
    } catch {
        modelsError.value = 'Não foi possível carregar os modelos.';
    } finally {
        modelsLoading.value = false;
    }
}

function submit() {
    form.put(`/vehicles/${vehicle.id}`);
}

onMounted(async () => {
    loadColors();

    await loadBrands();
    await loadModels();

    form.model = vehicle.model;
});

</script>

<template>

    <Head title="Editar veículo" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 p-6">
            <div>
                <h1 class="text-2xl font-semibold">Atualize veículo</h1>

                <p class="text-muted-foreground">
                    Edite um veículo e vincule-o a um proprietário.
                </p>
            </div>

            <form class="grid gap-6 rounded-lg border p-6 md:grid-cols-2" @submit.prevent="submit">
                <div class="md:col-span-2">
                    <label for="person_id" class="mb-2 block text-sm font-medium">
                        Proprietário
                    </label>

                    <select id="person_id" v-model="form.person_id" required
                        class="w-full rounded-md border bg-background px-3 py-2">
                        <option value="">Selecione o proprietário</option>

                        <option v-for="person in people" :key="person.id" :value="String(person.id)">
                            {{ person.name }}
                        </option>
                    </select>

                    <p v-if="form.errors.person_id" class="mt-1 text-sm text-red-500">
                        {{ form.errors.person_id }}
                    </p>
                </div>

                <div>
                    <label for="plate" class="mb-2 block text-sm font-medium">
                        Placa
                    </label>

                    <input id="plate" v-model="form.plate" type="text" required maxlength="7" placeholder="ABC1D23"
                        class="w-full rounded-md border bg-background px-3 py-2 uppercase" />

                    <p v-if="form.errors.plate" class="mt-1 text-sm text-red-500">
                        {{ form.errors.plate }}
                    </p>
                </div>

                <div>
                    <label for="brand" class="mb-2 block text-sm font-medium">
                        Marca
                    </label>

                    <select id="brand" v-model="form.brand" required :disabled="brandsLoading"
                        class="w-full rounded-md border bg-background px-3 py-2" @change="loadModels">
                        <option value="">
                            {{
                                brandsLoading
                                    ? 'Carregando marcas...'
                                    : 'Selecione a marca'
                            }}
                        </option>

                        <option v-for="brand in brands" :key="brand.id" :value="brand.name">
                            {{ brand.name }}
                        </option>
                    </select>

                    <p v-if="brandsError" class="mt-1 text-sm text-red-500">
                        {{ brandsError }}
                    </p>

                    <p v-if="form.errors.brand" class="mt-1 text-sm text-red-500">
                        {{ form.errors.brand }}
                    </p>
                </div>

                <div>
                    <label for="model" class="mb-2 block text-sm font-medium">
                        Modelo
                    </label>

                    <select id="model" v-model="form.model" required :disabled="!form.brand || modelsLoading"
                        class="w-full rounded-md border bg-background px-3 py-2">
                        <option value="">
                            {{
                                !form.brand
                                    ? 'Selecione uma marca primeiro'
                                    : modelsLoading
                                        ? 'Carregando modelos...'
                                        : 'Selecione o modelo'
                            }}
                        </option>

                        <option v-for="model in models" :key="model.id" :value="model.name">
                            {{ model.name }}
                        </option>
                    </select>

                    <p v-if="modelsError" class="mt-1 text-sm text-red-500">
                        {{ modelsError }}
                    </p>

                    <p v-if="form.errors.model" class="mt-1 text-sm text-red-500">
                        {{ form.errors.model }}
                    </p>
                </div>

                <div>
                    <label for="color" class="mb-2 block text-sm font-medium">
                        Cor
                    </label>

                    <select id="color" v-model="form.color" :disabled="colorsLoading"
                        class="w-full rounded-md border bg-background px-3 py-2">
                        <option value="">
                            {{
                                colorsLoading
                                    ? 'Carregando cores...'
                                    : 'Selecione a cor'
                            }}
                        </option>

                        <option v-for="color in colors" :key="color.id" :value="color.name">
                            {{ color.name }}
                        </option>
                    </select>

                    <p v-if="colorsError" class="mt-1 text-sm text-red-500">
                        {{ colorsError }}
                    </p>

                    <p v-if="form.errors.color" class="mt-1 text-sm text-red-500">
                        {{ form.errors.color }}
                    </p>
                </div>

                <div class="flex gap-3 md:col-span-2">
                    <Link href="/vehicles" class="rounded-md border px-4 py-2">
                        Cancelar
                    </Link>

                    <button type="submit" :disabled="form.processing"
                        class="rounded-md bg-primary px-4 py-2 text-primary-foreground disabled:opacity-50">
                        {{
                            form.processing
                                ? 'Atualizando...'
                                : 'Atualizar veículo'
                        }}
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>