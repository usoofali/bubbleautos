<script setup lang="ts">
import { ref } from 'vue';
import { UploadCloud, FileText, X } from '@lucide/vue';

interface Props {
    accept?: string;
    maxSizeMb?: number;
    error?: string;
    modelValue?: File | null;
}

const props = withDefaults(defineProps<Props>(), {
    accept: '.pdf,.png,.jpg,.jpeg,.doc,.docx',
    maxSizeMb: 15,
    modelValue: null,
});

const emit = defineEmits(['file-selected', 'file-removed', 'change', 'update:modelValue']);

const selectedFile = ref<File | null>(props.modelValue || null);
const isDragging = ref(false);
const localError = ref<string | null>(null);

const handleFile = (file: File) => {
    localError.value = null;
    if (file.size > props.maxSizeMb * 1024 * 1024) {
        localError.value = `File exceeds maximum size limit of ${props.maxSizeMb}MB.`;
        return;
    }
    selectedFile.value = file;
    emit('file-selected', file);
    emit('change', file);
    emit('update:modelValue', file);
};

const onDrop = (e: DragEvent) => {
    isDragging.value = false;
    if (e.dataTransfer?.files && e.dataTransfer.files[0]) {
        handleFile(e.dataTransfer.files[0]);
    }
};

const onChange = (e: Event) => {
    const target = e.target as HTMLInputElement;
    if (target.files && target.files[0]) {
        handleFile(target.files[0]);
    }
};

const removeFile = () => {
    selectedFile.value = null;
    localError.value = null;
    emit('file-removed');
    emit('change', null);
    emit('update:modelValue', null);
};

const formatSize = (bytes: number) => {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
};
</script>

<template>
    <div class="w-full flex flex-col gap-2">
        <div
            v-if="!selectedFile"
            @dragover.prevent="isDragging = true"
            @dragleave.prevent="isDragging = false"
            @drop.prevent="onDrop"
            class="relative flex flex-col items-center justify-center p-6 border-2 border-dashed rounded-2xl cursor-pointer transition-all duration-150"
            :class="
                isDragging
                    ? 'border-blue-500 bg-blue-50/50 dark:bg-blue-900/20'
                    : 'border-slate-300 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-900/50 hover:bg-slate-100/60 dark:hover:bg-slate-800/60'
            "
        >
            <input
                type="file"
                :accept="accept"
                class="absolute inset-0 opacity-0 cursor-pointer w-full h-full"
                @change="onChange"
            />
            <div class="w-12 h-12 rounded-full bg-blue-50 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400 flex items-center justify-center mb-3">
                <UploadCloud class="w-6 h-6" />
            </div>
            <p class="text-sm font-medium text-slate-800 dark:text-slate-200 text-center">
                Click to upload or drag & drop file
            </p>
            <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">
                PDF, JPG, PNG or DOC (Max {{ maxSizeMb }}MB)
            </p>
        </div>

        <div
            v-else
            class="flex items-center justify-between p-4 bg-blue-50/60 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800/60 rounded-2xl"
        >
            <div class="flex items-center gap-3 overflow-hidden">
                <div class="w-10 h-10 rounded-xl bg-blue-100 dark:bg-blue-900/50 text-blue-600 flex items-center justify-center shrink-0">
                    <FileText class="w-5 h-5" />
                </div>
                <div class="min-w-0">
                    <p class="text-sm font-medium text-slate-900 dark:text-white truncate">
                        {{ selectedFile.name }}
                    </p>
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        {{ formatSize(selectedFile.size) }}
                    </p>
                </div>
            </div>
            <button
                type="button"
                @click="removeFile"
                class="p-1.5 rounded-lg text-slate-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/30 transition-colors"
            >
                <X class="w-5 h-5" />
            </button>
        </div>

        <p v-if="localError || error" class="text-xs text-red-500 font-medium">
            {{ localError || error }}
        </p>
    </div>
</template>
