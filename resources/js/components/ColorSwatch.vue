<template>
    <button
        type="button"
        class="h-8 w-8 rounded-md border border-gray-300 dark:border-gray-600 flex-shrink-0 cursor-pointer transition-all hover:scale-110"
        :class="{
            'ring-2 ring-blue-500 dark:ring-blue-400 ring-offset-2 dark:ring-offset-gray-800': isSelected,
            'checkerboard': name === 'transparent',
            'neutral-swatch': name === 'current' || name === 'inherit',
        }"
        :style="swatchStyle"
        :title="swatchTitle"
        :aria-label="swatchTitle"
        @click="handleClick"
    >
        <!-- Label for current/inherit -->
        <span v-if="name === 'current' || name === 'inherit'" class="text-[10px] font-medium text-gray-600 dark:text-gray-300">
            {{ name === 'current' ? 'cur' : 'inh' }}
        </span>
    </button>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    name: {
        type: String,
        required: true,
    },
    shade: {
        type: String,
        default: null,
    },
    cssValue: {
        type: String,
        default: null,
    },
    isSelected: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['select']);

const swatchTitle = computed(() => {
    if (props.shade && props.shade !== 'DEFAULT') {
        return `${props.name}-${props.shade}`;
    }
    return props.name;
});

const swatchStyle = computed(() => {
    if (props.name === 'transparent') {
        return {};
    }

    if (props.name === 'current' || props.name === 'inherit') {
        return {
            backgroundColor: '#f3f4f6',
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'center',
        };
    }

    return {
        backgroundColor: props.cssValue || '#000',
    };
});

function handleClick() {
    emit('select', props.name, props.shade);
}
</script>

<style scoped>
.checkerboard {
    background-image:
        linear-gradient(to bottom right, transparent calc(50% - 1px), #ef4444 calc(50% - 1px), #ef4444 calc(50% + 1px), transparent calc(50% + 1px)),
        linear-gradient(45deg, #ccc 25%, transparent 25%),
        linear-gradient(-45deg, #ccc 25%, transparent 25%),
        linear-gradient(45deg, transparent 75%, #ccc 75%),
        linear-gradient(-45deg, transparent 75%, #ccc 75%);
    background-size: 100% 100%, 8px 8px, 8px 8px, 8px 8px, 8px 8px;
    background-position: 0 0, 0 0, 0 4px, 4px -4px, -4px 0px;
}

.dark .checkerboard {
    background-image:
        linear-gradient(to bottom right, transparent calc(50% - 1px), #f87171 calc(50% - 1px), #f87171 calc(50% + 1px), transparent calc(50% + 1px)),
        linear-gradient(45deg, #555 25%, transparent 25%),
        linear-gradient(-45deg, #555 25%, transparent 25%),
        linear-gradient(45deg, transparent 75%, #555 75%),
        linear-gradient(-45deg, transparent 75%, #555 75%);
}

.neutral-swatch {
    background-color: #f3f4f6;
}

.dark .neutral-swatch {
    background-color: #374151;
}
</style>
