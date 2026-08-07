<script setup lang="ts">
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import {
    LayoutGrid,
    Car,
    Users,
    Mail,
    UserCheck,
    Globe,
} from '@lucide/vue';
import AppLogo from '@/components/AppLogo.vue';
import NavFooter from '@/components/NavFooter.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    useSidebar,
} from '@/components/ui/sidebar';

const page = usePage();
const { isMobile, setOpenMobile } = useSidebar();
const permissions = computed<string[]>(() => (page.props.auth as any)?.user?.permissions || []);

const hasPermission = (slug: string) => permissions.value.includes(slug);

const handleNavClick = () => {
    if (isMobile.value) {
        setOpenMobile(false);
    }
};

const mainNavItems = computed(() => {
    const items = [
        {
            title: 'Dashboard',
            href: '/dashboard',
            icon: LayoutGrid,
            show: true,
        },
        {
            title: 'Vehicle Orders',
            href: '/orders',
            icon: Car,
            show: hasPermission('orders.view'),
        },
        {
            title: 'Customers',
            href: '/customers',
            icon: Users,
            show: hasPermission('customers.view'),
        },
        {
            title: 'Email Inbox',
            href: '/emails',
            icon: Mail,
            show: hasPermission('emails.review'),
        },
        {
            title: 'Staff Management',
            href: '/users',
            icon: UserCheck,
            show: hasPermission('users.manage'),
        },
    ];

    return items.filter((i) => i.show);
});

const footerNavItems = [
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link href="/dashboard" @click="handleNavClick">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent class="px-2 py-4">
            <div class="space-y-1">
                <Link
                    v-for="item in mainNavItems"
                    :key="item.href"
                    :href="item.href"
                    @click="handleNavClick"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-colors"
                    :class="
                        $page.url.startsWith(item.href) && (item.href !== '/dashboard' || $page.url === '/dashboard')
                            ? 'text-blue-600 dark:text-blue-400 font-bold bg-blue-50/60 dark:bg-blue-950/30'
                            : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100/80 dark:hover:bg-slate-800/60'
                    "
                >
                    <component :is="item.icon" class="w-5 h-5 shrink-0" />
                    <span>{{ item.title }}</span>
                </Link>
            </div>
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" @click="handleNavClick" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
</template>
