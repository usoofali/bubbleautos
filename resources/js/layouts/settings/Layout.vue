<script setup lang="ts">
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import { User, Lock, Sun, ShieldCheck, Settings } from '@lucide/vue';

const page = usePage();
const permissions = computed<string[]>(() => (page.props.auth as any)?.user?.permissions || []);
const hasPermission = (slug: string) => permissions.value.includes(slug);

const sidebarNavItems = computed(() => {
    const items = [
        {
            title: 'Profile',
            href: '/settings/profile',
            icon: User,
            show: true,
        },
        {
            title: 'Security',
            href: '/settings/security',
            icon: Lock,
            show: true,
        },
        {
            title: 'Appearance',
            href: '/settings/appearance',
            icon: Sun,
            show: true,
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
    ];

    return items.filter((i) => i.show);
});

const { isCurrentOrParentUrl } = useCurrentUrl();
</script>

<template>
    <div class="px-4 py-6">
        <Heading
            title="Settings & System Administration"
            description="Manage your user profile, security, appearance, roles & permissions, and system configurations"
        />

        <div class="flex flex-col lg:flex-row lg:space-x-12 mt-6">
            <aside class="w-full max-w-xl lg:w-56">
                <nav
                    class="flex flex-col space-y-1 space-x-0"
                    aria-label="Settings"
                >
                    <Button
                        v-for="item in sidebarNavItems"
                        :key="item.href"
                        variant="ghost"
                        :class="[
                            'w-full justify-start gap-2.5 font-semibold text-xs py-2.5 rounded-xl transition-all',
                            isCurrentOrParentUrl(item.href)
                                ? 'text-blue-600 dark:text-blue-400 font-bold bg-blue-50/60 dark:bg-blue-950/30 hover:text-blue-600 dark:hover:text-blue-400'
                                : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800',
                        ]"
                        as-child
                    >
                        <Link :href="item.href">
                            <component :is="item.icon" class="h-4 w-4 shrink-0" />
                            {{ item.title }}
                        </Link>
                    </Button>
                </nav>
            </aside>

            <Separator class="my-6 lg:hidden" />

            <div class="flex-1 md:max-w-4xl">
                <section class="space-y-6">
                    <slot />
                </section>
            </div>
        </div>
    </div>
</template>
