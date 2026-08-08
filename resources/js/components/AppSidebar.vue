<script setup lang="ts">
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import {
    LayoutGrid,
    Car,
    Users,
    Mail,
    UserCheck,
    ShieldCheck,
    Settings,
    Activity,
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
const { isMobile, setOpenMobile, state } = useSidebar();
const permissions = computed<string[]>(() => (page.props.auth as any)?.user?.permissions || []);

const hasPermission = (slug: string) => permissions.value.includes(slug);

const handleNavClick = () => {
    if (isMobile.value) {
        setOpenMobile(false);
    }
};

const isItemActive = (href: string) => {
    const currentUrl = page.url;
    if (href === '/dashboard') {
        return currentUrl === '/dashboard' || currentUrl === '/';
    }
    return currentUrl.startsWith(href);
};

const operationsItems = computed(() => [
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
].filter(i => i.show));

const adminItems = computed(() => [
    {
        title: 'Staff Management',
        href: '/users',
        icon: UserCheck,
        show: hasPermission('users.manage'),
    },
    {
        title: 'Roles & Permissions',
        href: '/roles',
        icon: ShieldCheck,
        show: hasPermission('roles.manage'),
    },
    {
        title: 'System Settings',
        href: '/settings/system',
        icon: Settings,
        show: hasPermission('settings.manage'),
    },
].filter(i => i.show));

const footerNavItems = [];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader class="p-3">
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child class="p-2 rounded-2xl bg-white/90 dark:bg-slate-900/90 backdrop-blur-xl border border-slate-200/80 dark:border-slate-800/80 shadow-xs hover:border-blue-500/40 transition-all">
                        <Link href="/dashboard" @click="handleNavClick">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent class="px-3 py-4 space-y-6">
            <!-- Operations Section -->
            <div v-if="operationsItems.length > 0" class="space-y-1.5">
                <div class="group-data-[collapsible=icon]:hidden px-3 py-1 text-[10px] font-black uppercase tracking-wider text-slate-400 dark:text-slate-500 flex items-center gap-1.5">
                    <Activity class="w-3 h-3 text-blue-500" />
                    <span>Operations</span>
                </div>
                <div class="space-y-1">
                    <Link
                        v-for="item in operationsItems"
                        :key="item.href"
                        :href="item.href"
                        @click="handleNavClick"
                        class="group relative flex items-center justify-between px-3 py-2.5 rounded-2xl text-xs sm:text-sm transition-all duration-200"
                        :class="
                            isItemActive(item.href)
                                ? 'text-blue-600 dark:text-blue-400 font-extrabold bg-blue-50/50 dark:bg-blue-950/30'
                                : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100/80 dark:hover:bg-slate-800/60 font-semibold'
                        "
                    >
                        <div class="flex items-center gap-3 min-w-0">
                            <div
                                class="w-7 h-7 rounded-xl flex items-center justify-center shrink-0 transition-transform duration-200 group-hover:scale-110"
                                :class="
                                    isItemActive(item.href)
                                        ? 'bg-blue-500/15 text-blue-600 dark:text-blue-400'
                                        : 'bg-slate-100 dark:bg-slate-800/80 text-slate-500 dark:text-slate-400 group-hover:text-blue-600 dark:group-hover:text-blue-400'
                                "
                            >
                                <component :is="item.icon" class="w-4 h-4" />
                            </div>
                            <span class="group-data-[collapsible=icon]:hidden truncate">{{ item.title }}</span>
                        </div>
                        <span
                            v-if="isItemActive(item.href)"
                            class="group-data-[collapsible=icon]:hidden w-1.5 h-1.5 rounded-full bg-blue-600 dark:bg-blue-400 animate-pulse shrink-0"
                        ></span>
                    </Link>
                </div>
            </div>

            <!-- Administration Section -->
            <div v-if="adminItems.length > 0" class="space-y-1.5 pt-2">
                <div class="group-data-[collapsible=icon]:hidden px-3 py-1 text-[10px] font-black uppercase tracking-wider text-slate-400 dark:text-slate-500 flex items-center gap-1.5">
                    <ShieldCheck class="w-3 h-3 text-emerald-500" />
                    <span>Administration</span>
                </div>
                <div class="space-y-1">
                    <Link
                        v-for="item in adminItems"
                        :key="item.href"
                        :href="item.href"
                        @click="handleNavClick"
                        class="group relative flex items-center justify-between px-3 py-2.5 rounded-2xl text-xs sm:text-sm transition-all duration-200"
                        :class="
                            isItemActive(item.href)
                                ? 'text-blue-600 dark:text-blue-400 font-extrabold bg-blue-50/50 dark:bg-blue-950/30'
                                : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100/80 dark:hover:bg-slate-800/60 font-semibold'
                        "
                    >
                        <div class="flex items-center gap-3 min-w-0">
                            <div
                                class="w-7 h-7 rounded-xl flex items-center justify-center shrink-0 transition-transform duration-200 group-hover:scale-110"
                                :class="
                                    isItemActive(item.href)
                                        ? 'bg-blue-500/15 text-blue-600 dark:text-blue-400'
                                        : 'bg-slate-100 dark:bg-slate-800/80 text-slate-500 dark:text-slate-400 group-hover:text-blue-600 dark:group-hover:text-blue-400'
                                "
                            >
                                <component :is="item.icon" class="w-4 h-4" />
                            </div>
                            <span class="group-data-[collapsible=icon]:hidden truncate">{{ item.title }}</span>
                        </div>
                        <span
                            v-if="isItemActive(item.href)"
                            class="group-data-[collapsible=icon]:hidden w-1.5 h-1.5 rounded-full bg-blue-600 dark:bg-blue-400 animate-pulse shrink-0"
                        ></span>
                    </Link>
                </div>
            </div>
        </SidebarContent>

        <SidebarFooter class="p-3 space-y-3">
            <div class="group-data-[collapsible=icon]:hidden px-3 py-2 rounded-2xl bg-blue-50/60 dark:bg-blue-950/40 border border-blue-200/60 dark:border-blue-800/40 flex items-center gap-2 text-xs font-semibold text-blue-700 dark:text-blue-300">
                <span class="w-2 h-2 rounded-full bg-blue-500 dark:bg-blue-400 animate-pulse shrink-0"></span>
                <span class="truncate">System Operational v2.4</span>
            </div>
            <NavFooter :items="footerNavItems" @click="handleNavClick" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
</template>
