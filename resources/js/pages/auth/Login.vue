<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
/* @chisel-registration */
//import { register } from '@/routes';
/* @end-chisel-registration */
import { store } from '@/routes/login';
import { request } from '@/routes/password';
/* @chisel-passkeys */
import PasskeyVerify from '@/components/PasskeyVerify.vue';
/* @end-chisel-passkeys */

const loginMode = ref<'admin' | 'client'>('admin');

defineOptions({
    layout: {
        title: 'Entre na sua conta',
        description: 'Coloque seu e-mail e senha',
    },
});

defineProps<{
    status?: string;
    canResetPassword: boolean;
}>();
</script>

<template>
    <Head title="Login" />

    <div
        v-if="status"
        class="mb-4 text-center text-sm font-medium text-green-600"
    >
        {{ status }}
    </div>

    <!-- @chisel-passkeys -->
    <PasskeyVerify />
    <!-- @end-chisel-passkeys -->

    <div class="grid grid-cols-2 gap-2 rounded-lg border p-1">
        <button
            type="button"
            class="rounded-md px-3 py-2 text-sm transition"
            :class="loginMode === 'admin' ? 'bg-primary text-primary-foreground' : 'text-muted-foreground hover:bg-muted'"
            @click="loginMode = 'admin'"
        >
            Administrador
        </button>
        <button
            type="button"
            class="rounded-md px-3 py-2 text-sm transition"
            :class="loginMode === 'client' ? 'bg-primary text-primary-foreground' : 'text-muted-foreground hover:bg-muted'"
            @click="loginMode = 'client'"
        >
            Cliente
        </button>
    </div>

    <Form
        v-if="loginMode === 'admin'"
        v-bind="store.form()"
        :reset-on-success="['password']"
        v-slot="{ errors, processing }"
        class="flex flex-col gap-6"
    >
        <div class="grid gap-6">
            <div class="grid gap-2">
                <Label for="email">Email:</Label>
                <Input
                    id="email"
                    type="email"
                    name="email"
                    required
                    autofocus
                    :tabindex="1"
                    autocomplete="email"
                    placeholder="email@example.com"
                />
                <InputError :message="errors.email" />
            </div>

            <div class="grid gap-2">
                <div class="flex items-center justify-between">
                    <Label for="password">Senha: </Label>
                    <TextLink
                        v-if="canResetPassword"
                        :href="request()"
                        class="text-sm"
                        :tabindex="5"
                    >
                        Esqueceu sua senha?
                    </TextLink>
                </div>
                <PasswordInput
                    id="password"
                    name="password"
                    required
                    :tabindex="2"
                    autocomplete="current-password"
                    placeholder="Senha"
                />
                <InputError :message="errors.password" />
            </div>

            <div class="flex items-center justify-between">
                <Label for="remember" class="flex items-center space-x-3">
                    <Checkbox id="remember" name="remember" :tabindex="3" />
                    <span>Lembrar login</span>
                </Label>
            </div>

            <Button
                type="submit"
                class="mt-4 w-full"
                :tabindex="4"
                :disabled="processing"
                data-test="login-button"
            >
                <Spinner v-if="processing" />
                Entrar
            </Button>
        </div>
    </Form>

    <Form
        v-else
        action="/client/login"
        method="post"
        v-slot="{ errors, processing }"
        class="flex flex-col gap-6"
    >
        <div class="rounded-lg border border-primary/30 bg-primary/5 p-3 text-sm text-muted-foreground">
            Entre usando o e-mail e a data de nascimento cadastrados pelo administrador.
        </div>

        <div class="grid gap-6">
            <div class="grid gap-2">
                <Label for="client-email">E-mail:</Label>
                <Input
                    id="client-email"
                    type="email"
                    name="email"
                    required
                    autofocus
                    autocomplete="email"
                    placeholder="email@example.com"
                />
                <InputError :message="errors.email || errors.client" />
            </div>

            <div class="grid gap-2">
                <Label for="birth-date">Data de nascimento:</Label>
                <Input
                    id="birth-date"
                    type="date"
                    name="birth_date"
                    required
                />
                <InputError :message="errors.birth_date" />
            </div>

            <Button
                type="submit"
                class="mt-4 w-full"
                :disabled="processing"
            >
                <Spinner v-if="processing" />
                Entrar como cliente
            </Button>
        </div>
    </Form>
</template>
