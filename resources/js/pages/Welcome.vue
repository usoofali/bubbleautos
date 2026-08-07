<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { 
    Car, 
    ShieldCheck, 
    FileText, 
    Search, 
    ArrowRight, 
    Phone, 
    Mail, 
    MapPin, 
    CheckCircle, 
    Lock, 
    Zap, 
    Activity, 
    Cpu, 
    Clock, 
    Sparkles, 
    Check,
    Sun,
    Moon
} from '@lucide/vue';
import AppLogo from '@/components/AppLogo.vue';
import { useAppearance } from '@/composables/useAppearance';

interface CMS {
    company_name: string;
    company_logo: string;
    hero_title: string;
    hero_subtitle: string;
    contact_phone: string;
    contact_email: string;
    contact_address: string;
}

defineProps<{
    cms: CMS;
}>();

const { appearance, updateAppearance, resolvedAppearance } = useAppearance();

function toggleNextTheme() {
    if (appearance.value === 'system') {
        updateAppearance('light');
    } else if (appearance.value === 'light') {
        updateAppearance('dark');
    } else {
        updateAppearance('system');
    }
}

// Interactive VIN Search Demo State
const demoVinInput = ref('1G1YY221165103456');
const isSearching = ref(false);
const demoResult = ref<{
    vin: string;
    orderNo: string;
    vehicle: string;
    status: string;
    eta: string;
    location: string;
    titleStatus: string;
} | null>({
    vin: '1G1YY221165103456',
    orderNo: 'BO-2026-9042',
    vehicle: '2023 Chevrolet Corvette Stingray Z51',
    status: 'In Transit - Ocean Vessel',
    eta: 'Aug 14, 2026',
    location: 'Port of Savannah -> Dubai Jebel Ali',
    titleStatus: 'Verified in Digital Vault',
});

const sampleVins = [
    { label: 'Corvette Stingray', vin: '1G1YY221165103456' },
    { label: 'Ford Mustang GT', vin: '1FA6P8CF5H5501290' },
    { label: 'BMW M4 Competition', vin: 'WBS33AY050FP99182' },
];

function triggerDemoSearch(vinToSearch?: string) {
    if (vinToSearch) {
        demoVinInput.value = vinToSearch;
    }
    isSearching.value = true;
    setTimeout(() => {
        isSearching.value = false;
        if (demoVinInput.value.includes('1FA') || demoVinInput.value.toLowerCase().includes('mustang')) {
            demoResult.value = {
                vin: '1FA6P8CF5H5501290',
                orderNo: 'BO-2026-7718',
                vehicle: '2024 Ford Mustang GT Premium',
                status: 'Port Yard Ready for Loading',
                eta: 'Aug 18, 2026',
                location: 'Port of Newark (EFX Terminal)',
                titleStatus: 'Dock Receipt Attached',
            };
        } else if (demoVinInput.value.includes('WBS') || demoVinInput.value.toLowerCase().includes('bmw')) {
            demoResult.value = {
                vin: 'WBS33AY050FP99182',
                orderNo: 'BO-2026-6631',
                vehicle: '2023 BMW M4 Competition Coupe',
                status: 'Customs Cleared',
                eta: 'Delivered',
                location: 'Container Yard - Section B4',
                titleStatus: 'Original Title Dispatched',
            };
        } else {
            demoResult.value = {
                vin: demoVinInput.value.toUpperCase() || '1G1YY221165103456',
                orderNo: 'BO-2026-9042',
                vehicle: '2023 Chevrolet Corvette Stingray Z51',
                status: 'In Transit - Ocean Vessel',
                eta: 'Aug 14, 2026',
                location: 'Port of Savannah -> Dubai Jebel Ali',
                titleStatus: 'Verified in Digital Vault',
            };
        }
    }, 400);
}
</script>

<template>
    <Head title="Bubbles Autos - Vehicle Shipping & Operations Management Platform" />

    <div class="min-h-screen bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-slate-100 flex flex-col selection:bg-blue-600 selection:text-white font-sans relative overflow-hidden transition-colors duration-300">
        <!-- Ambient Glowing Background Lights -->
        <div class="absolute -top-40 left-1/2 -translate-x-1/2 w-[800px] h-[500px] bg-blue-500/10 dark:bg-blue-600/10 rounded-full blur-[140px] pointer-events-none"></div>
        <div class="absolute top-96 -right-20 w-[450px] h-[450px] bg-indigo-500/10 dark:bg-indigo-600/10 rounded-full blur-[120px] pointer-events-none"></div>
        <div class="absolute inset-0 bg-[linear-gradient(to_right,#cbd5e120_1px,transparent_1px),linear-gradient(to_bottom,#cbd5e120_1px,transparent_1px)] dark:bg-[linear-gradient(to_right,#1e293b10_1px,transparent_1px),linear-gradient(to_bottom,#1e293b10_1px,transparent_1px)] bg-[size:4rem_4rem] [mask-image:radial-gradient(ellipse_60%_50%_at_50%_0%,#000_70%,transparent_100%)] pointer-events-none"></div>

        <!-- Navigation Header -->
        <header class="border-b border-slate-200/80 dark:border-slate-800/80 bg-white/80 dark:bg-slate-950/80 backdrop-blur-xl sticky top-0 z-50 transition-colors">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">
                <div class="flex items-center gap-6">
                    <AppLogo />
                    <div class="hidden md:flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200 dark:border-emerald-800/50 text-emerald-700 dark:text-emerald-400 text-xs font-semibold">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 dark:bg-emerald-400 animate-pulse"></span>
                        <span>System Operational v2.4</span>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <!-- Theme Toggle Switcher -->
                    <button
                        @click="toggleNextTheme()"
                        type="button"
                        class="inline-flex items-center gap-2 px-3 py-2 rounded-xl bg-slate-100 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-xs font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-800 transition-all"
                        :title="`Current theme preference: ${appearance} (${resolvedAppearance}). Click to toggle.`"
                    >
                        <Sun v-if="resolvedAppearance === 'light'" class="w-4 h-4 text-amber-500" />
                        <Moon v-else class="w-4 h-4 text-blue-400" />
                        <span class="hidden sm:inline capitalize">{{ appearance }}</span>
                    </button>

                    <Link
                        href="/login"
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl font-bold text-sm bg-blue-600 hover:bg-blue-500 active:scale-[0.98] text-white shadow-lg shadow-blue-600/30 transition-all duration-150"
                    >
                        <Lock class="w-4 h-4" />
                        <span>Staff Login</span>
                    </Link>
                </div>
            </div>
        </header>

        <!-- Hero Section -->
        <section class="relative py-16 lg:py-24 overflow-hidden">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 text-center">
                <!-- Hero Badge -->
                <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-blue-50 dark:bg-blue-950/70 border border-blue-200 dark:border-blue-800/60 text-blue-700 dark:text-blue-400 text-xs font-bold uppercase tracking-wider mb-8 shadow-xs">
                    <Sparkles class="w-3.5 h-3.5" />
                    <span>Next-Gen Vehicle Shipment & Logistics Intelligence</span>
                </div>

                <!-- Hero Title -->
                <h1 class="text-4xl sm:text-6xl lg:text-7xl font-black tracking-tight text-slate-900 dark:text-white max-w-5xl mx-auto leading-[1.1] mb-6">
                    {{ cms.hero_title || 'Streamlined Vehicle Shipment & Inventory Control' }}
                </h1>

                <!-- Hero Subtitle -->
                <p class="text-lg sm:text-xl text-slate-600 dark:text-slate-400 max-w-3xl mx-auto leading-relaxed mb-10">
                    {{ cms.hero_subtitle || 'Sub-second VIN search, automated financial line items, dock receipt tracking, and immutable document vaults engineered for high-volume auto export operations.' }}
                </p>

                <!-- Hero CTA & Quick Actions -->
                <div class="flex flex-wrap items-center justify-center gap-4 mb-16">
                    <Link
                        href="/login"
                        class="inline-flex items-center gap-2.5 px-8 py-4 rounded-2xl font-extrabold text-base bg-blue-600 hover:bg-blue-500 text-white shadow-xl shadow-blue-600/30 active:scale-[0.98] transition-all duration-150 group"
                    >
                        <Car class="w-5 h-5" />
                        <span>Access Operations Portal</span>
                        <ArrowRight class="w-5 h-5 group-hover:translate-x-1 transition-transform" />
                    </Link>
                </div>

                <!-- Interactive Live Demo Widget -->
                <div class="max-w-4xl mx-auto bg-white/90 dark:bg-slate-900/90 backdrop-blur-2xl border border-slate-200/90 dark:border-slate-800/90 rounded-3xl p-6 sm:p-8 shadow-xl dark:shadow-2xl dark:shadow-slate-950/80 text-left transition-colors">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6 pb-6 border-b border-slate-200 dark:border-slate-800/80">
                        <div>
                            <div class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-blue-600 dark:text-blue-400">
                                <Zap class="w-4 h-4 text-blue-600 dark:text-blue-400" />
                                <span>Live Interactive Feature Preview</span>
                            </div>
                            <h3 class="text-lg font-bold text-slate-900 dark:text-white mt-1">Instant 17-Character VIN & Order Lookup</h3>
                        </div>
                        <div class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400">
                            <span>Try example:</span>
                            <button
                                v-for="sample in sampleVins"
                                :key="sample.vin"
                                @click="triggerDemoSearch(sample.vin)"
                                class="px-2.5 py-1 rounded-lg bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-medium text-[11px] transition-colors border border-slate-200 dark:border-slate-700/60"
                            >
                                {{ sample.label }}
                            </button>
                        </div>
                    </div>

                    <!-- Search Input Bar -->
                    <div class="flex flex-col sm:flex-row items-center gap-3 mb-6">
                        <div class="relative w-full">
                            <Search class="w-5 h-5 absolute left-4 top-1/2 -translate-y-1/2 text-slate-400" />
                            <input
                                v-model="demoVinInput"
                                @keyup.enter="triggerDemoSearch()"
                                type="text"
                                placeholder="Enter 17-character VIN (e.g. 1G1YY221165103456) or Bubble Order #"
                                class="w-full pl-12 pr-4 py-3.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-500 text-sm font-mono focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition-all"
                            />
                        </div>
                        <button
                            @click="triggerDemoSearch()"
                            :disabled="isSearching"
                            class="w-full sm:w-auto px-6 py-3.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-bold text-sm shadow-md shadow-blue-600/20 shrink-0 transition-all flex items-center justify-center gap-2"
                        >
                            <span v-if="isSearching" class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                            <Search v-else class="w-4 h-4" />
                            <span>Lookup</span>
                        </button>
                    </div>

                    <!-- Search Result Preview Card -->
                    <div v-if="demoResult" class="p-5 rounded-2xl bg-slate-50/80 dark:bg-slate-950/70 border border-slate-200/80 dark:border-slate-800/80 transition-all">
                        <div class="flex flex-wrap items-center justify-between gap-3 mb-4 pb-3 border-b border-slate-200 dark:border-slate-800/60">
                            <div>
                                <div class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Vehicle Identified</div>
                                <div class="text-base font-extrabold text-slate-900 dark:text-white">{{ demoResult.vehicle }}</div>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="px-3 py-1 rounded-full bg-emerald-50 dark:bg-emerald-950/80 border border-emerald-200 dark:border-emerald-800/80 text-emerald-700 dark:text-emerald-400 text-xs font-bold flex items-center gap-1.5">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 dark:bg-emerald-400 animate-pulse"></span>
                                    {{ demoResult.status }}
                                </span>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-xs">
                            <div>
                                <span class="text-slate-500 block">VIN Number</span>
                                <span class="font-mono text-slate-800 dark:text-slate-200 font-bold">{{ demoResult.vin }}</span>
                            </div>
                            <div>
                                <span class="text-slate-500 block">Order Number</span>
                                <span class="font-mono text-blue-600 dark:text-blue-400 font-bold">{{ demoResult.orderNo }}</span>
                            </div>
                            <div>
                                <span class="text-slate-500 block">Current Route / Port</span>
                                <span class="text-slate-800 dark:text-slate-200 font-medium truncate block">{{ demoResult.location }}</span>
                            </div>
                            <div>
                                <span class="text-slate-500 block">Title Vault Status</span>
                                <span class="text-amber-600 dark:text-amber-400 font-semibold flex items-center gap-1">
                                    <ShieldCheck class="w-3.5 h-3.5" />
                                    {{ demoResult.titleStatus }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Bento Feature Section -->
        <section class="py-20 bg-slate-100/70 dark:bg-slate-900/60 border-y border-slate-200 dark:border-slate-800/80 relative transition-colors">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-3xl mx-auto mb-16">
                    <div class="text-xs font-bold uppercase tracking-wider text-blue-600 dark:text-blue-400 mb-2">Core Platform Capability</div>
                    <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 dark:text-white">Built Specifically for High-Volume Vehicle Operations</h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <!-- Feature 1 -->
                    <div class="p-8 rounded-3xl bg-white dark:bg-slate-900/80 border border-slate-200/80 dark:border-slate-800 hover:border-blue-500/50 dark:hover:border-blue-500/50 transition-all group hover:-translate-y-1 duration-200 shadow-xs dark:shadow-none">
                        <div class="w-14 h-14 rounded-2xl bg-blue-100 dark:bg-blue-600/20 text-blue-600 dark:text-blue-400 flex items-center justify-center mb-6 border border-blue-200 dark:border-blue-500/30 group-hover:scale-110 transition-transform">
                            <Search class="w-7 h-7" />
                        </div>
                        <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-3">Sub-Second VIN Indexing</h3>
                        <p class="text-slate-600 dark:text-slate-400 text-sm leading-relaxed mb-4">
                            Instant multi-field search across 17-character VINs, Bubble Order identifiers, and buyer details with zero lag.
                        </p>
                        <ul class="space-y-2 text-xs text-slate-700 dark:text-slate-300">
                            <li class="flex items-center gap-2"><Check class="w-4 h-4 text-blue-600 dark:text-blue-400" /> Fuzzy matching for fast lookup</li>
                            <li class="flex items-center gap-2"><Check class="w-4 h-4 text-blue-600 dark:text-blue-400" /> Real-time search indexing</li>
                        </ul>
                    </div>

                    <!-- Feature 2 -->
                    <div class="p-8 rounded-3xl bg-white dark:bg-slate-900/80 border border-slate-200/80 dark:border-slate-800 hover:border-amber-500/50 dark:hover:border-amber-500/50 transition-all group hover:-translate-y-1 duration-200 shadow-xs dark:shadow-none">
                        <div class="w-14 h-14 rounded-2xl bg-amber-100 dark:bg-amber-600/20 text-amber-600 dark:text-amber-400 flex items-center justify-center mb-6 border border-amber-200 dark:border-amber-500/30 group-hover:scale-110 transition-transform">
                            <FileText class="w-7 h-7" />
                        </div>
                        <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-3">Financials & Auto-Invoicing</h3>
                        <p class="text-slate-600 dark:text-slate-400 text-sm leading-relaxed mb-4">
                            Auto-calculating line items, custom discounts, partial payment tracking, and automated PDF invoice generation.
                        </p>
                        <ul class="space-y-2 text-xs text-slate-700 dark:text-slate-300">
                            <li class="flex items-center gap-2"><Check class="w-4 h-4 text-amber-600 dark:text-amber-400" /> Real-time balance calculations</li>
                            <li class="flex items-center gap-2"><Check class="w-4 h-4 text-amber-600 dark:text-amber-400" /> One-click PDF dispatch</li>
                        </ul>
                    </div>

                    <!-- Feature 3 -->
                    <div class="p-8 rounded-3xl bg-white dark:bg-slate-900/80 border border-slate-200/80 dark:border-slate-800 hover:border-emerald-500/50 dark:hover:border-emerald-500/50 transition-all group hover:-translate-y-1 duration-200 shadow-xs dark:shadow-none">
                        <div class="w-14 h-14 rounded-2xl bg-emerald-100 dark:bg-emerald-600/20 text-emerald-600 dark:text-emerald-400 flex items-center justify-center mb-6 border border-emerald-200 dark:border-emerald-500/30 group-hover:scale-110 transition-transform">
                            <ShieldCheck class="w-7 h-7" />
                        </div>
                        <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-3">Document Vault & Timeline</h3>
                        <p class="text-slate-600 dark:text-slate-400 text-sm leading-relaxed mb-4">
                            Centralized title management, dock receipts, and bills of lading with an immutable chronological audit trail.
                        </p>
                        <ul class="space-y-2 text-xs text-slate-700 dark:text-slate-300">
                            <li class="flex items-center gap-2"><Check class="w-4 h-4 text-emerald-600 dark:text-emerald-400" /> Title receipt timestamping</li>
                            <li class="flex items-center gap-2"><Check class="w-4 h-4 text-emerald-600 dark:text-emerald-400" /> Immutable event history</li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>

        <!-- Stats & Infrastructure Section -->
        <section class="py-20 bg-white dark:bg-slate-950 transition-colors">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-6 text-center">
                    <div class="p-8 rounded-3xl bg-slate-50 dark:bg-slate-900/40 border border-slate-200/80 dark:border-slate-800/80 transition-colors">
                        <div class="text-4xl sm:text-5xl font-black text-blue-600 dark:text-blue-400 mb-1">100%</div>
                        <div class="text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400">VIN Lookup Accuracy</div>
                    </div>
                    <div class="p-8 rounded-3xl bg-slate-50 dark:bg-slate-900/40 border border-slate-200/80 dark:border-slate-800/80 transition-colors">
                        <div class="text-4xl sm:text-5xl font-black text-slate-900 dark:text-white mb-1">&lt; 100ms</div>
                        <div class="text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400">Query Response Time</div>
                    </div>
                    <div class="p-8 rounded-3xl bg-slate-50 dark:bg-slate-900/40 border border-slate-200/80 dark:border-slate-800/80 transition-colors">
                        <div class="text-4xl sm:text-5xl font-black text-amber-600 dark:text-amber-400 mb-1">Zero</div>
                        <div class="text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400">Paper Bottlenecks</div>
                    </div>
                    <div class="p-8 rounded-3xl bg-slate-50 dark:bg-slate-900/40 border border-slate-200/80 dark:border-slate-800/80 transition-colors">
                        <div class="text-4xl sm:text-5xl font-black text-emerald-600 dark:text-emerald-400 mb-1">24/7</div>
                        <div class="text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400">Email Manifest Parsing</div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Contact & Enterprise Footer -->
        <footer class="mt-auto border-t border-slate-200 dark:border-slate-800/80 bg-slate-50 dark:bg-slate-950 py-12 transition-colors">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col md:flex-row items-center justify-between gap-6">
                    <AppLogo />
                    <div class="flex flex-wrap items-center justify-center gap-6 text-xs text-slate-600 dark:text-slate-400">
                        <span class="flex items-center gap-2"><Phone class="w-4 h-4 text-blue-600 dark:text-blue-400" /> {{ cms.contact_phone }}</span>
                        <span class="flex items-center gap-2"><Mail class="w-4 h-4 text-blue-600 dark:text-blue-400" /> {{ cms.contact_email }}</span>
                        <span class="flex items-center gap-2"><MapPin class="w-4 h-4 text-blue-600 dark:text-blue-400" /> {{ cms.contact_address }}</span>
                    </div>
                </div>
                <div class="mt-8 pt-6 border-t border-slate-200 dark:border-slate-800/60 text-center text-xs text-slate-500 dark:text-slate-400">
                    &copy; {{ new Date().getFullYear() }} {{ cms.company_name }}. Internal Management Platform. All rights reserved.
                </div>
            </div>
        </footer>
    </div>
</template>
