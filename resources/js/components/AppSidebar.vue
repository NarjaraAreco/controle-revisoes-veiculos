<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import {
    BookOpen,
    Car,
    ChartBar,
    FolderGit2,
    LayoutGrid,
    UserRound,
    Users,
    Wrench,
} from '@lucide/vue';
import AppLogo from '@/components/AppLogo.vue';
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import type { NavItem } from '@/types';

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: LayoutGrid,
    },
    {
        title: 'Meu perfil',
        href: '/settings/profile',
        icon: UserRound,
    },
    {
        title: 'Relatórios',
        href: '/reports',
        icon: ChartBar,
    },
    {
        title: 'Pessoas',
        href: '/people',
        icon: Users,
    },
    {
        title: 'Veículos',
        href: '/vehicles',
        icon: Car,
    },
    {
        title: 'Revisões',
        href: '/revisions',
        icon: Wrench,
    },
];

const clientNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: LayoutGrid,
    },
    {
        title: 'Meu perfil',
        href: '/client/profile',
        icon: UserRound,
    },
];

const footerNavItems: NavItem[] = [
    {
        title: 'Repositório',
        href: 'https://github.com/laravel/vue-starter-kit',
        icon: FolderGit2,
    },
    {
        title: 'Documentação',
        href: 'https://laravel.com/docs/starter-kits#vue',
        icon: BookOpen,
    },
];

const page = usePage();
const isAdmin = computed(() => page.props.auth.user?.role === 'admin');
const isClient = computed(() => page.props.client !== null);
const visibleMainNavItems = computed(() => isClient.value
    ? clientNavItems
    : isAdmin.value
        ? mainNavItems
        : mainNavItems.slice(0, 1));
</script>

<template>
    <Sidebar collapsible="none" variant="sidebar" class="min-h-svh shrink-0">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="visibleMainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter v-if="!isClient" :items="footerNavItems" />
            <NavUser v-if="!isClient" />
            <SidebarMenu v-else>
                <SidebarMenuItem>
                    <SidebarMenuButton as-child>
                        <Link href="/client/logout" method="post" as="button">
                            <span>Sair</span>
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarFooter>
    </Sidebar>
</template>
