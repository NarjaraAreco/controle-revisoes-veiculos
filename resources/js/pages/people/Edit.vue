<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { ref } from 'vue';
import {
    blockNonNumericKeys,
    formatCep,
    formatCpf,
    formatPhone,
    formatState,
    sanitizeDigits,
    showClientErrors,
    validatePerson,
} from '@/lib/clientValidation';

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

const { person } = defineProps<{
    person: Person;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Pessoas',
        href: '/people',
    },
    {
        title: 'Nova pessoa',
        href: '/people/create',
    },
];

const form = useForm({
    name: person.name,
    cpf: formatCpf(person.cpf),
    birth_date: person.birth_date
        ? person.birth_date.substring(0, 10)
        : '',
    gender: person.gender ?? '',
    phone: formatPhone(person.phone ?? ''),
    email: person.email ?? '',
    cep: formatCep(person.cep ?? ''),
    street: person.street ?? '',
    number: person.number ?? '',
    complement: person.complement ?? '',
    neighborhood: person.neighborhood ?? '',
    city: person.city ?? '',
    state: person.state ?? '',
});

const cepLoading = ref(false);
const cepError = ref('');
const cepValidated = ref(Boolean(person.cep));

function sanitizeCpf() {
    form.cpf = formatCpf(form.cpf);
}

function blockCpfLetters(event: KeyboardEvent) {
    const allowedKeys = ['Backspace', 'Delete', 'ArrowLeft', 'ArrowRight', 'Tab', 'Home', 'End'];

    if (!event.ctrlKey && !event.metaKey && !allowedKeys.includes(event.key) && !/^\d$/.test(event.key)) {
        event.preventDefault();
    }
}

function pasteCpf(event: ClipboardEvent) {
    event.preventDefault();
    form.cpf = formatCpf(`${form.cpf}${event.clipboardData?.getData('text') ?? ''}`);
}

function sanitizePhone() {
    form.phone = formatPhone(form.phone);
}

function sanitizeCep() {
    form.cep = formatCep(form.cep);
    cepValidated.value = false;
}

async function lookupCep() {
    const cep = form.cep.replace(/\D/g, '');

    form.cep = formatCep(cep);
    cepValidated.value = false;
    cepError.value = '';

    if (!cep) {
        return;
    }

    if (cep.length !== 8) {
        cepError.value = 'O CEP deve possuir 8 dígitos.';
        return;
    }

    cepLoading.value = true;

    try {
        const response = await fetch(
            `https://viacep.com.br/ws/${cep}/json/`,
        );

        if (!response.ok) {
            throw new Error();
        }

        const data = await response.json();

        if (data.erro) {
            throw new Error();
        }

        form.street = data.logradouro ?? '';
        form.neighborhood = data.bairro ?? '';
        form.city = data.localidade ?? '';
        form.state = data.uf ?? '';

        cepValidated.value = true;
    } catch {
        cepError.value = 'CEP não encontrado ou serviço indisponível.';
    } finally {
        cepLoading.value = false;
    }
    }

function submit() {
    if (form.cep && !cepValidated.value) {
        cepError.value = 'Consulte um CEP válido antes de salvar.';
        return;
    }

    const errors = validatePerson(form);
    if (Object.keys(errors).length > 0) {
        showClientErrors(errors);
        return;
    }

    form.put(`/people/${person.id}`);
}
</script>

<template>
    <Head title="Editar pessoa" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 p-6">
            <div>
                <h1 class="text-2xl font-semibold">Nova pessoa</h1>
                <p class="text-muted-foreground">
                    Edite um cliente ou proprietário de veículo.
                </p>
            </div>

            <form
                class="grid gap-6 rounded-lg border p-6 md:grid-cols-2"
                @submit.prevent="submit"
            >
                <div class="md:col-span-2">
                    <label for="name" class="mb-2 block text-sm font-medium">
                        Nome completo
                    </label>
                    <input
                        id="name"
                        v-model="form.name"
                        type="text"
                        required
                        maxlength="255"
                        class="w-full rounded-md border bg-background px-3 py-2"
                    />
                    <p v-if="form.errors.name" class="mt-1 text-sm text-red-500">
                        {{ form.errors.name }}
                    </p>
                </div>

                <div>
                    <label for="cpf" class="mb-2 block text-sm font-medium">
                        CPF
                    </label>
                    <input
                        id="cpf"
                        v-model="form.cpf"
                        type="text"
                        inputmode="numeric"
                        pattern="[0-9]{3}\.[0-9]{3}\.[0-9]{3}-[0-9]{2}"
                        required
                        maxlength="14"
                        placeholder="000.000.000-00"
                        class="w-full rounded-md border bg-background px-3 py-2"
                        @keydown="blockCpfLetters"
                        @input="sanitizeCpf"
                        @paste="pasteCpf"
                    />
                    <p v-if="form.errors.cpf" class="mt-1 text-sm text-red-500">
                        {{ form.errors.cpf }}
                    </p>
                </div>

                <div>
                    <label
                        for="birth_date"
                        class="mb-2 block text-sm font-medium"
                    >
                        Data de nascimento
                    </label>
                    <input
                        id="birth_date"
                        v-model="form.birth_date"
                        type="date"
                        required
                        class="w-full rounded-md border bg-background px-3 py-2"
                    />
                </div>

                <div>
                    <label for="gender" class="mb-2 block text-sm font-medium">
                        Sexo
                    </label>
                    <select
                        id="gender"
                        v-model="form.gender"
                        class="w-full rounded-md border bg-background px-3 py-2"
                    >
                        <option value="">Selecione</option>
                        <option value="Feminino">Feminino</option>
                        <option value="Masculino">Masculino</option>
                        <option value="Outro">Outro</option>
                    </select>
                </div>

                <div>
                    <label for="phone" class="mb-2 block text-sm font-medium">
                        Telefone
                    </label>
                    <input
                        id="phone"
                        v-model="form.phone"
                        type="text"
                        inputmode="numeric"
                        maxlength="15"
                        class="w-full rounded-md border bg-background px-3 py-2"
                        @keydown="blockNonNumericKeys"
                        @input="sanitizePhone"
                    />
                </div>

                <div class="md:col-span-2">
                    <label for="email" class="mb-2 block text-sm font-medium">
                        E-mail
                    </label>
                    <input
                        id="email"
                        v-model="form.email"
                        type="email"
                        required
                        maxlength="255"
                        class="w-full rounded-md border bg-background px-3 py-2"
                    />
                </div>

                <div>
                    <label for="cep" class="mb-2 block text-sm font-medium">
                        CEP
                    </label>
                    <input
                        id="cep"
                        v-model="form.cep"
                        type="text"
                        inputmode="numeric"
                        maxlength="9"
                        placeholder="00000-000"
                        class="w-full rounded-md border bg-background px-3 py-2"
                        @keydown="blockNonNumericKeys"
                        @input="sanitizeCep"
                        @blur="lookupCep"
                    />
                    <p v-if="cepLoading" class="mt-1 text-sm text-muted-foreground">
                        Consultando CEP...
                    </p>

                    <p v-if="cepError" class="mt-1 text-sm text-red-500">
                        {{ cepError }}
                    </p>
                </div>

                <div>
                    <label for="street" class="mb-2 block text-sm font-medium">
                        Rua
                    </label>
                    <input
                        id="street"
                        v-model="form.street"
                        type="text"
                        maxlength="255"
                        class="w-full rounded-md border bg-background px-3 py-2"
                    />
                </div>

                <div>
                    <label for="number" class="mb-2 block text-sm font-medium">
                        Número
                    </label>
                    <input
                        id="number"
                        v-model="form.number"
                        type="text"
                        maxlength="20"
                        class="w-full rounded-md border bg-background px-3 py-2"
                    />
                </div>

                <div>
                    <label
                        for="complement"
                        class="mb-2 block text-sm font-medium"
                    >
                        Complemento
                    </label>
                    <input
                        id="complement"
                        v-model="form.complement"
                        type="text"
                        maxlength="255"
                        class="w-full rounded-md border bg-background px-3 py-2"
                    />
                </div>

                <div>
                    <label
                        for="neighborhood"
                        class="mb-2 block text-sm font-medium"
                    >
                        Bairro
                    </label>
                    <input
                        id="neighborhood"
                        v-model="form.neighborhood"
                        type="text"
                        maxlength="255"
                        class="w-full rounded-md border bg-background px-3 py-2"
                    />
                </div>

                <div>
                    <label for="city" class="mb-2 block text-sm font-medium">
                        Cidade
                    </label>
                    <input
                        id="city"
                        v-model="form.city"
                        type="text"
                        maxlength="255"
                        class="w-full rounded-md border bg-background px-3 py-2"
                    />
                </div>
                <div>
                    <label for="state" class="mb-2 block text-sm font-medium">
                        Estado
                    </label>
                    <input
                        id="state"
                        v-model="form.state"
                        type="text"
                        maxlength="2"
                        placeholder="UF"
                        class="w-full rounded-md border bg-background px-3 py-2"
                        @input="form.state = formatState(form.state)"
                    />
                </div>

                <div class="flex gap-3 md:col-span-2">
                    <Link
                        href="/people"
                        class="rounded-md border px-4 py-2"
                    >
                        Cancelar
                    </Link>

                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="rounded-md bg-primary px-4 py-2 text-primary-foreground disabled:opacity-50"
                    >
                        {{ form.processing ? 'Atualizando...' : 'Atualizar pessoa' }}
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
