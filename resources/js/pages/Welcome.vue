<script setup lang="ts">
import { ref, watch, onMounted } from 'vue';
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
    Moon,
    AlertCircle
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
const demoVinInput = ref('');
const isSearching = ref(false);
const searchMessage = ref('');
const demoResult = ref<{
    vin: string;
    orderNo: string;
    vehicle: string;
    status: string;
    eta: string;
    location: string;
    titleStatus: string;
    pictures?: string[];
} | null>(null);

let debounceTimer: ReturnType<typeof setTimeout> | null = null;

async function performLookup(query: string) {
    const trimmed = query.trim();
    if (!trimmed || trimmed.length < 6) {
        demoResult.value = null;
        searchMessage.value = trimmed.length > 0 ? 'Type at least 6 digits of VIN to auto-lookup...' : '';
        return;
    }

    isSearching.value = true;
    searchMessage.value = '';

    try {
        const response = await fetch(`/api/public/vin-lookup?q=${encodeURIComponent(trimmed)}`);
        const data = await response.json();
        if (data.found && data.result) {
            demoResult.value = data.result;
            searchMessage.value = '';
        } else {
            demoResult.value = null;
            searchMessage.value = data.message || `No vehicle order matching '${trimmed}' found in system.`;
        }
    } catch (err) {
        demoResult.value = null;
        searchMessage.value = 'Lookup temporarily unavailable.';
    } finally {
        isSearching.value = false;
    }
}

watch(demoVinInput, (newVal) => {
    if (debounceTimer) clearTimeout(debounceTimer);
    if (newVal.trim().length >= 6) {
        debounceTimer = setTimeout(() => {
            performLookup(newVal);
        }, 250);
    } else {
        demoResult.value = null;
        searchMessage.value = newVal.trim().length > 0 ? 'Type at least 6 digits to auto-lookup...' : '';
    }
});

onMounted(() => {
    if (demoVinInput.value.length >= 6) {
        performLookup(demoVinInput.value);
    }
});
</script>

<template>
    <Head title="Bubbles Autos - Vehicle Order & Operations Management Platform" />

    <div class="min-h-screen bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-slate-100 flex flex-col selection:bg-blue-600 selection:text-white font-sans relative overflow-hidden transition-colors duration-300">
        <!-- Textured Grid & Matrix Pattern -->
        <div class="absolute inset-0 bg-[radial-gradient(#94a3b8_1px,transparent_1px)] dark:bg-[radial-gradient(#334155_1px,transparent_1px)] [background-size:24px_24px] opacity-40 dark:opacity-30 pointer-events-none z-0"></div>

        <!-- Ambient Glowing Background Lights -->
        <div class="absolute -top-40 left-1/2 -translate-x-1/2 w-[800px] h-[500px] bg-blue-500/10 dark:bg-blue-600/10 rounded-full blur-[140px] pointer-events-none z-0"></div>
        <div class="absolute top-96 -right-20 w-[450px] h-[450px] bg-indigo-500/10 dark:bg-indigo-600/10 rounded-full blur-[120px] pointer-events-none z-0"></div>
        <div class="absolute inset-0 bg-[linear-gradient(to_right,#cbd5e120_1px,transparent_1px),linear-gradient(to_bottom,#cbd5e120_1px,transparent_1px)] dark:bg-[linear-gradient(to_right,#1e293b10_1px,transparent_1px),linear-gradient(to_bottom,#1e293b10_1px,transparent_1px)] bg-[size:4rem_4rem] [mask-image:radial-gradient(ellipse_60%_50%_at_50%_0%,#000_70%,transparent_100%)] pointer-events-none z-0"></div>

        <!-- Huge Subtle Background Watermark Graphic & Brand Text -->
        <div class="absolute inset-0 flex items-center justify-center overflow-hidden pointer-events-none select-none z-0">
            <div class="w-[700px] h-[700px] lg:w-[850px] lg:h-[850px] rounded-full border border-blue-500/10 dark:border-blue-400/10 flex items-center justify-center relative">
                <img src="/logo.jpeg" alt="" class="w-[400px] h-[400px] lg:w-[500px] lg:h-[500px] object-contain rounded-full opacity-[0.04] dark:opacity-[0.07] grayscale blur-[1px]" />
            </div>
            <div class="absolute text-[120px] sm:text-[180px] lg:text-[220px] font-black uppercase tracking-[0.25em] text-slate-900/[0.03] dark:text-white/[0.04] whitespace-nowrap -rotate-6 select-none">
                BUBBLES AUTOS
            </div>
        </div>

        <!-- Navigation Header -->
        <header class="border-b border-slate-200/80 dark:border-slate-800/80 bg-white/80 dark:bg-slate-950/80 backdrop-blur-xl sticky top-0 z-50 transition-colors">
            <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 h-16 sm:h-20 flex items-center justify-between gap-2">
                <div class="flex items-center gap-2 sm:gap-6 min-w-0 shrink-0">
                    <AppLogo />
                    <div class="hidden md:flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200 dark:border-emerald-800/50 text-emerald-700 dark:text-emerald-400 text-xs font-semibold">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 dark:bg-emerald-400 animate-pulse"></span>
                        <span>System Operational v2.4</span>
                    </div>
                </div>

                <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                    <!-- Theme Toggle Switcher -->
                    <button
                        @click="toggleNextTheme()"
                        type="button"
                        class="inline-flex items-center justify-center gap-1.5 p-2 sm:px-3 sm:py-2 rounded-xl bg-slate-100 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-xs font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-800 transition-all shrink-0"
                        :title="`Current theme preference: ${appearance} (${resolvedAppearance}). Click to toggle.`"
                    >
                        <Sun v-if="resolvedAppearance === 'light'" class="w-4 h-4 text-amber-500 shrink-0" />
                        <Moon v-else class="w-4 h-4 text-blue-400 shrink-0" />
                        <span class="hidden sm:inline capitalize">{{ appearance }}</span>
                    </button>

                    <Link
                        href="/login"
                        class="inline-flex items-center justify-center gap-1.5 px-3.5 py-2 sm:px-5 sm:py-2.5 rounded-xl font-bold text-xs sm:text-sm bg-blue-600 hover:bg-blue-500 active:scale-[0.98] text-white shadow-md sm:shadow-lg shadow-blue-600/30 transition-all duration-150 shrink-0 whitespace-nowrap"
                    >
                        <Lock class="w-3.5 h-3.5 sm:w-4 sm:h-4 shrink-0" />
                        <span>Login</span>
                    </Link>
                </div>
            </div>
        </header>

        <!-- Hero Section -->
        <section class="relative py-12 lg:py-16 overflow-hidden">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 text-center">
                <!-- Hero Badge -->
                <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-blue-50 dark:bg-blue-950/70 border border-blue-200 dark:border-blue-800/60 text-blue-700 dark:text-blue-400 text-xs font-bold uppercase tracking-wider mb-6 shadow-xs">
                    <Sparkles class="w-3.5 h-3.5" />
                    <span>Next-Gen Vehicle Shipment & Logistics Intelligence</span>
                </div>

                <!-- Hero Title -->
                <h1 class="text-3xl sm:text-5xl lg:text-6xl font-black tracking-tight text-slate-900 dark:text-white max-w-5xl mx-auto leading-[1.1] mb-5">
                    {{ cms.hero_title || 'Streamlined Vehicle Shipment & Inventory Control' }}
                </h1>

                <!-- Hero Subtitle -->
                <p class="text-base sm:text-lg text-slate-600 dark:text-slate-400 max-w-3xl mx-auto leading-relaxed mb-10">
                    {{ cms.hero_subtitle || 'Sub-second VIN search, automated financial line items, dock receipt tracking, and immutable document vaults engineered for high-volume auto export operations.' }}
                </p>

                <!-- Interactive Live Demo Widget -->
                <div class="max-w-3xl mx-auto bg-white/90 dark:bg-slate-900/90 backdrop-blur-2xl border border-slate-200/90 dark:border-slate-800/90 rounded-3xl p-6 sm:p-8 shadow-xl dark:shadow-2xl dark:shadow-slate-950/80 text-left transition-colors">
                    <div class="flex items-center justify-between gap-4 mb-6 pb-4 border-b border-slate-200 dark:border-slate-800/80">
                        <div>
                            <div class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-blue-600 dark:text-blue-400">
                                <Zap class="w-4 h-4 text-blue-600 dark:text-blue-400" />
                                <span>Live Interactive Feature Preview</span>
                            </div>
                            <h3 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white mt-1">Automatic VIN & Order Lookup</h3>
                        </div>
                    </div>

                    <!-- Search Input Bar (Auto-triggers on typing 6+ characters) -->
                    <div class="relative w-full mb-6">
                        <Search class="w-5 h-5 absolute left-4 top-1/2 -translate-y-1/2 text-slate-400" />
                        <input
                            v-model="demoVinInput"
                            type="text"
                            placeholder="e.g. Enter last 6 VIN (e.g. 5123456)"
                            class="w-full pl-12 pr-10 py-3.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-500 text-sm font-mono focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition-all"
                        />
                        <div v-if="isSearching" class="absolute right-4 top-1/2 -translate-y-1/2">
                            <span class="w-4 h-4 border-2 border-blue-600/30 border-t-blue-600 rounded-full animate-spin block"></span>
                        </div>
                    </div>

                    <!-- Search Result Preview Card (Real Database Match) -->
                    <div v-if="demoResult" class="p-5 rounded-2xl bg-slate-50/80 dark:bg-slate-950/70 border border-slate-200/80 dark:border-slate-800/80 transition-all space-y-4">
                        <div class="flex flex-wrap items-center justify-between gap-3 pb-3 border-b border-slate-200 dark:border-slate-800/60">
                            <div>
                                <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Vehicle Identified</div>
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
                                <span class="text-slate-500 block">Expected ETA</span>
                                <span class="text-blue-600 dark:text-blue-400 font-bold block">{{ demoResult.eta }}</span>
                            </div>
                        </div>

                        <div class="pt-2 border-t border-slate-200/60 dark:border-slate-800/40 text-xs text-slate-600 dark:text-slate-400 flex items-center gap-1.5">
                            <ShieldCheck class="w-4 h-4 text-emerald-500 shrink-0" />
                            <span>{{ demoResult.titleStatus }}</span>
                        </div>

                        <!-- Vehicle Photos Gallery (if present) -->
                        <div v-if="demoResult.pictures && demoResult.pictures.length > 0" class="pt-3 border-t border-slate-200/60 dark:border-slate-800/40 space-y-2">
                            <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider flex items-center justify-between">
                                <span>Vehicle Photos ({{ demoResult.pictures.length }})</span>
                            </div>
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5">
                                <a
                                    v-for="(pic, idx) in demoResult.pictures"
                                    :key="idx"
                                    :href="pic"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="group relative aspect-4/3 rounded-xl overflow-hidden bg-slate-100 dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-xs hover:border-blue-500/60 transition-all"
                                >
                                    <img :src="pic" :alt="demoResult.vehicle" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" />
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- No Result / Helper Message State -->
                    <div v-else-if="searchMessage" class="p-4 rounded-2xl bg-amber-50/50 dark:bg-amber-950/20 border border-amber-200/60 dark:border-amber-900/40 flex items-center gap-3 text-xs font-medium text-amber-800 dark:text-amber-300">
                        <AlertCircle class="w-4 h-4 shrink-0 text-amber-600 dark:text-amber-400" />
                        <span>{{ searchMessage }}</span>
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

        <!-- Footer -->
        <footer class="bg-white dark:bg-slate-950 border-t border-slate-200 dark:border-slate-800 py-12 text-slate-600 dark:text-slate-400 text-sm transition-colors mt-auto">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row items-center justify-between gap-6">
                <div class="flex items-center gap-3">
                    <AppLogo />
                    <span class="text-xs text-slate-500 font-medium">© {{ new Date().getFullYear() }} {{ cms.company_name }}. All rights reserved.</span>
                </div>
                <div class="flex flex-wrap items-center justify-center gap-6 text-xs font-semibold text-slate-600 dark:text-slate-400">
                    <span class="flex items-center gap-1.5"><Phone class="w-3.5 h-3.5 text-blue-500" /> {{ cms.contact_phone }}</span>
                    <span class="flex items-center gap-1.5"><Mail class="w-3.5 h-3.5 text-blue-500" /> {{ cms.contact_email }}</span>
                    <span class="flex items-center gap-1.5"><MapPin class="w-3.5 h-3.5 text-blue-500" /> {{ cms.contact_address }}</span>
                </div>
            </div>
        </footer>
    </div>
</template>
