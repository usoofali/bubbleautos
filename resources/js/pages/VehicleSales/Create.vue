<script setup lang="ts">
import { watch } from 'vue';
import { useForm, Head, Link } from '@inertiajs/vue3';
import AppCard from '@/components/common/AppCard.vue';
import AppButton from '@/components/common/AppButton.vue';
import AppInput from '@/components/common/AppInput.vue';
import AppFormField from '@/components/common/AppFormField.vue';
import { convertNumberToWords } from '@/lib/numberToWords';
import { ArrowLeft, Save } from '@lucide/vue';

const props = defineProps<{
    nextSaleNumber: string;
    defaultDate: string;
}>();

const form = useForm({
    customer_name: '',
    customer_phone: '',
    customer_address: '',
    vehicle_make: '',
    vehicle_model: '',
    vehicle_year: new Date().getFullYear().toString(),
    vehicle_vin: '',
    vehicle_color: '',
    vehicle_description: '',
    sale_date: props.defaultDate,
    sale_amount: '',
    amount_paid: '',
    payment_method: 'bank_transfer',
    amount_in_words: '',
    notes: '',
});

// Auto-populate "Amount in Words" as the staff types the sale amount
watch(
    () => form.sale_amount,
    (val) => {
        if (val) {
            form.amount_in_words = convertNumberToWords(val);
        }
    }
);

const submit = () => {
    form.post('/vehicle-sales', {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Record New Vehicle Sale" />

    <div class="max-w-4xl mx-auto space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-slate-900 p-5 sm:p-6 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm">
            <div class="flex items-center gap-3">
                <Link href="/vehicle-sales">
                    <button class="p-2 rounded-xl bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors">
                        <ArrowLeft class="w-5 h-5" />
                    </button>
                </Link>
                <div>
                    <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight">Record Vehicle Sale</h1>
                    <p class="text-xs text-slate-500 font-mono">Transaction No: <strong class="text-blue-600 dark:text-blue-400">{{ nextSaleNumber }}</strong></p>
                </div>
            </div>

            <AppButton variant="primary" @click="submit" :loading="form.processing" class="w-full sm:w-auto rounded-xl font-bold shadow-sm justify-center">
                <Save class="w-4 h-4 mr-1.5" /> Save & Generate Documents
            </AppButton>
        </div>

        <form @submit.prevent="submit" class="space-y-6">
            <!-- Section 1: Customer Information -->
            <AppCard title="1. Customer Information" description="Enter customer contact and billing details for the sales documents">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <AppFormField label="Customer Full Name" required :error="form.errors.customer_name">
                            <AppInput
                                v-model="form.customer_name"
                                placeholder="e.g. Alhaji Aminu Kano"
                                :error="!!form.errors.customer_name"
                            />
                        </AppFormField>
                    </div>

                    <div>
                        <AppFormField label="Phone Number" :error="form.errors.customer_phone">
                            <AppInput
                                v-model="form.customer_phone"
                                placeholder="e.g. 08033473516"
                                :error="!!form.errors.customer_phone"
                            />
                        </AppFormField>
                    </div>

                    <div>
                        <AppFormField label="Address" :error="form.errors.customer_address">
                            <AppInput
                                v-model="form.customer_address"
                                placeholder="e.g. No. 45 Nassarawa GRA, Kano"
                                :error="!!form.errors.customer_address"
                            />
                        </AppFormField>
                    </div>
                </div>
            </AppCard>

            <!-- Section 2: Vehicle Information -->
            <AppCard title="2. Vehicle Information" description="Enter details of the vehicle being sold">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <AppFormField label="Make / Manufacturer" required :error="form.errors.vehicle_make">
                            <AppInput
                                v-model="form.vehicle_make"
                                placeholder="e.g. Mercedes-Benz, Toyota"
                                :error="!!form.errors.vehicle_make"
                            />
                        </AppFormField>
                    </div>

                    <div>
                        <AppFormField label="Model" required :error="form.errors.vehicle_model">
                            <AppInput
                                v-model="form.vehicle_model"
                                placeholder="e.g. GLE 450, Camry"
                                :error="!!form.errors.vehicle_model"
                            />
                        </AppFormField>
                    </div>

                    <div>
                        <AppFormField label="Model Year" :error="form.errors.vehicle_year">
                            <AppInput
                                v-model="form.vehicle_year"
                                placeholder="e.g. 2024"
                                :error="!!form.errors.vehicle_year"
                            />
                        </AppFormField>
                    </div>

                    <div>
                        <AppFormField label="VIN / Chassis Number" :error="form.errors.vehicle_vin">
                            <AppInput
                                v-model="form.vehicle_vin"
                                placeholder="e.g. WDC1671591A123456"
                                :error="!!form.errors.vehicle_vin"
                            />
                        </AppFormField>
                    </div>

                    <div>
                        <AppFormField label="Color" :error="form.errors.vehicle_color">
                            <AppInput
                                v-model="form.vehicle_color"
                                placeholder="e.g. Black, Metallic Blue"
                                :error="!!form.errors.vehicle_color"
                            />
                        </AppFormField>
                    </div>

                    <div class="md:col-span-3">
                        <AppFormField label="Additional Vehicle Description (Optional)" :error="form.errors.vehicle_description">
                            <textarea
                                v-model="form.vehicle_description"
                                rows="2"
                                placeholder="e.g. Trim, specs, condition, key count, extra features..."
                                class="w-full px-3.5 py-2.5 text-sm bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            ></textarea>
                        </AppFormField>
                    </div>
                </div>
            </AppCard>

            <!-- Section 3: Transaction & Financial Information -->
            <AppCard title="3. Financial & Payment Details" description="Enter pricing, amount paid, and date for Invoice and Receipt generation">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <AppFormField label="Total Sale Amount (₦)" required :error="form.errors.sale_amount">
                            <AppInput
                                v-model="form.sale_amount"
                                type="number"
                                step="0.01"
                                placeholder="e.g. 85000000"
                                :error="!!form.errors.sale_amount"
                            />
                        </AppFormField>
                    </div>

                    <div>
                        <AppFormField label="Amount Paid (₦)" required :error="form.errors.amount_paid">
                            <AppInput
                                v-model="form.amount_paid"
                                type="number"
                                step="0.01"
                                placeholder="e.g. 85000000"
                                :error="!!form.errors.amount_paid"
                            />
                        </AppFormField>
                    </div>

                    <div>
                        <AppFormField label="Sale Date" required :error="form.errors.sale_date">
                            <AppInput
                                v-model="form.sale_date"
                                type="date"
                                :error="!!form.errors.sale_date"
                            />
                        </AppFormField>
                    </div>

                    <div>
                        <AppFormField label="Payment Method" :error="form.errors.payment_method">
                            <select
                                v-model="form.payment_method"
                                class="w-full px-3.5 py-2.5 text-sm bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            >
                                <option value="bank_transfer">Bank Transfer</option>
                                <option value="cash">Cash</option>
                                <option value="cheque">Cheque</option>
                                <option value="wire">Wire Transfer</option>
                                <option value="other">Other</option>
                            </select>
                        </AppFormField>
                    </div>

                    <div class="md:col-span-2">
                        <AppFormField label="Amount in Words (Auto-generated)" helpText="Automatically converts numbers to words as you type sale amount" :error="form.errors.amount_in_words">
                            <AppInput
                                v-model="form.amount_in_words"
                                placeholder="e.g. Eighty-Five Million Naira Only"
                                :error="!!form.errors.amount_in_words"
                            />
                        </AppFormField>
                    </div>

                    <div class="md:col-span-3">
                        <AppFormField label="Internal Notes / Remarks (Optional)" :error="form.errors.notes">
                            <textarea
                                v-model="form.notes"
                                rows="2"
                                placeholder="Internal staff comments..."
                                class="w-full px-3.5 py-2.5 text-sm bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            ></textarea>
                        </AppFormField>
                    </div>
                </div>
            </AppCard>

            <div class="flex items-center justify-end gap-3 pt-2">
                <Link href="/vehicle-sales">
                    <AppButton variant="outline" type="button">Cancel</AppButton>
                </Link>
                <AppButton variant="primary" type="submit" :loading="form.processing" class="rounded-xl font-bold shadow-sm">
                    <Save class="w-4 h-4 mr-1.5" /> Save Transaction & Generate Documents
                </AppButton>
            </div>
        </form>
    </div>
</template>
