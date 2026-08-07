<script setup lang="ts">
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { Search, Car, ArrowRight, X } from '@lucide/vue';
import AppBadge from './AppBadge.vue';

const searchQuery = ref('');
const searchResults = ref<any[]>([]);
const isLoading = ref(false);
const showResults = ref(false);

let debounceTimer: any = null;

const performSearch = () => {
    if (!searchQuery.value || searchQuery.value.trim().length < 2) {
        searchResults.value = [];
        showResults.value = false;
        return;
    }

    isLoading.value = true;
    showResults.value = true;

    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(async () => {
        try {
            const res = await fetch(`/api/search?q=${encodeURIComponent(searchQuery.value.trim())}`);
            if (res.ok) {
                const data = await res.json();
                searchResults.value = data.orders || [];
            }
        } catch (e) {
            console.error(e);
        } finally {
            isLoading.value = false;
        }
    }, 250);
};

watch(searchQuery, performSearch);

const goToOrder = (orderId: number) => {
    showResults.value = false;
    searchQuery.value = '';
    router.visit(`/orders/${orderId}`);
};

const clearSearch = () => {
    searchQuery.value = '';
    searchResults.value = [];
    showResults.value = false;
};
</script>

<template>
    <div class="relative w-full max-w-md">
        <div class="relative flex items-center">
            <Search class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
            <input
                type="text"
                v-model="searchQuery"
                @focus="showResults = true"
                placeholder="Instant VIN Search (e.g. 1FA6..., BA-00001, John)..."
                class="w-full pl-10 pr-9 py-2 text-sm rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50/80 dark:bg-slate-900 text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
            />
            <button
                v-if="searchQuery"
                @click="clearSearch"
                type="button"
                class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600"
            >
                <X class="w-4 h-4" />
            </button>
        </div>

        <!-- Overlay Results Dropdown -->
        <div
            v-if="showResults && searchQuery.trim().length >= 2"
            class="absolute left-0 right-0 top-full mt-2 z-50 bg-white dark:bg-slate-800 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-700 overflow-hidden"
        >
            <div v-if="isLoading" class="p-4 text-center text-xs text-slate-400">
                Searching orders & VIN database...
            </div>
            <div v-else-if="searchResults.length === 0" class="p-4 text-center text-xs text-slate-400">
                No vehicles or orders matching "<span class="font-semibold text-slate-600 dark:text-slate-300">{{ searchQuery }}</span>".
            </div>
            <div v-else class="max-h-80 overflow-y-auto divide-y divide-slate-100 dark:divide-slate-700/60">
                <button
                    v-for="order in searchResults"
                    :key="order.id"
                    @click="goToOrder(order.id)"
                    class="w-full p-3.5 flex items-center justify-between text-left hover:bg-blue-50/60 dark:hover:bg-slate-700/50 transition-colors group cursor-pointer"
                >
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-9 h-9 rounded-xl bg-blue-100 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400 flex items-center justify-center shrink-0">
                            <Car class="w-5 h-5" />
                        </div>
                        <div class="min-w-0">
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-sm text-slate-900 dark:text-white">{{ order.order_number }}</span>
                                <AppBadge :status="order.status" size="sm" />
                            </div>
                            <p class="text-xs text-slate-600 dark:text-slate-300 truncate font-mono mt-0.5">
                                VIN: {{ order.vin }}
                            </p>
                            <p class="text-xs text-slate-400 truncate">
                                {{ order.year }} {{ order.make }} {{ order.model }} &bull; Customer: {{ order.customer?.name }}
                            </p>
                        </div>
                    </div>
                    <ArrowRight class="w-4 h-4 text-slate-300 group-hover:text-blue-600 group-hover:translate-x-1 transition-all shrink-0" />
                </button>
            </div>
        </div>
    </div>
</template>
