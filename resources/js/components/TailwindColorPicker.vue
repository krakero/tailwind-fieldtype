<template>
    <div class="tailwind-color-picker">
        <!-- Warnings -->
        <div v-if="meta.warnings && meta.warnings.length > 0" class="mb-2">
            <div v-for="(warning, idx) in meta.warnings" :key="idx" class="text-xs text-red-500 dark:text-red-400">
                {{ warning }}
            </div>
        </div>

        <!-- Color Picker Popover -->
        <Popover align="start" inset class="tailwind-color-picker-popover">
            <template #trigger>
                <button
                    type="button"
                    :disabled="isReadOnly"
                    class="h-8 w-8 rounded-md border border-gray-300 dark:border-gray-600 flex flex-shrink-0 items-center justify-center cursor-pointer transition-colors hover:border-gray-400 dark:hover:border-gray-500 disabled:cursor-not-allowed disabled:opacity-50"
                    :class="{ 'checkerboard': selectedName === 'transparent' }"
                    :style="swatchStyle"
                    :title="triggerLabel"
                    :aria-label="triggerLabel"
                >
                    <svg
                        v-if="!selectedName"
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke-width="1.5"
                        stroke="currentColor"
                        class="h-5 w-5 text-gray-400"
                        aria-hidden="true"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </template>

            <template #default="{ close }">
                <div class="picker-content p-3 overflow-y-auto">
                    <div class="grid grid-cols-[auto_1fr] items-center gap-x-3 gap-y-1.5">
                        <template v-for="colorName in availableColorNames" :key="colorName">
                            <!-- Color name label -->
                            <div class="text-xs text-right text-gray-500 dark:text-gray-400 whitespace-nowrap">
                                {{ colorName }}
                            </div>

                            <!-- Advanced mode: show all shades -->
                            <div v-if="mode === 'advanced'" class="flex gap-1.5">
                                <template v-if="isFlatColor(colorName)">
                                    <ColorSwatch
                                        :name="colorName"
                                        :shade="null"
                                        :css-value="getColorValue(colorName, null)"
                                        :is-selected="selectedName === colorName && selectedShade === null"
                                        @select="selectColor(colorName, null, close)"
                                    />
                                </template>
                                <template v-else>
                                    <ColorSwatch
                                        v-for="(cssValue, shadeKey) in colors[colorName]"
                                        :key="shadeKey"
                                        :name="colorName"
                                        :shade="shadeKey"
                                        :css-value="cssValue"
                                        :is-selected="selectedName === colorName && selectedShade === shadeKey"
                                        @select="selectColor(colorName, shadeKey, close)"
                                    />
                                </template>
                            </div>

                            <!-- Simple mode: show only default shade -->
                            <div v-else class="flex gap-1.5">
                                <ColorSwatch
                                    :name="colorName"
                                    :shade="getSimpleModeShade(colorName)"
                                    :css-value="getColorValue(colorName, getSimpleModeShade(colorName))"
                                    :is-selected="selectedName === colorName"
                                    @select="selectColor(colorName, getSimpleModeShade(colorName), close)"
                                />
                            </div>
                        </template>

                        <!-- Clear button -->
                        <template v-if="selectedName">
                            <div></div>
                            <button
                                type="button"
                                class="flex items-center gap-1 justify-self-start text-xs text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 cursor-pointer"
                                @click="clearColor(close)"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-3.5 w-3.5" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                                Clear
                            </button>
                        </template>
                    </div>
                </div>
            </template>
        </Popover>
    </div>
</template>

<script setup>
import { computed, ref, watch } from 'vue';
import { Fieldtype } from '@statamic/cms';
import { Popover } from '@statamic/cms/ui';
import { parseColorValue, formatColorValue, getColorCssValue, getDefaultShade } from '../color-value.js';
import ColorSwatch from './ColorSwatch.vue';

const emit = defineEmits(Fieldtype.emits);
const props = defineProps(Fieldtype.props);

// Fieldtype integration
const { expose, update, isReadOnly } = Fieldtype.use(emit, props);
defineExpose(expose);

// Reactive state
const selectedName = ref(null);
const selectedShade = ref(null);
const selectedRaw = ref(null);

// Computed properties
const colors = computed(() => props.meta.colors || {});
const mode = computed(() => props.config.mode || 'advanced');
const prefix = computed(() => props.config.class_prefix || '');
const defaultColorShade = computed(() => props.config.default_color || '500');

const availableColorNames = computed(() => {
    const configColors = props.config.available_colors;
    const allColors = Object.keys(colors.value);

    // Filter by config if specified
    if (configColors && Array.isArray(configColors) && configColors.length > 0) {
        return configColors.filter(name => allColors.includes(name));
    }

    return allColors;
});

const selectedLabel = computed(() => {
    if (selectedRaw.value) {
        return selectedRaw.value;
    }
    if (!selectedName.value) {
        return '';
    }
    if (selectedShade.value && selectedShade.value !== 'DEFAULT') {
        return `${selectedName.value}-${selectedShade.value}`;
    }
    return selectedName.value;
});

const triggerLabel = computed(() => selectedLabel.value || 'Pick Color');

const swatchStyle = computed(() => {
    if (!selectedName.value) {
        return {};
    }

    const cssValue = getColorValue(selectedName.value, selectedShade.value);

    if (selectedName.value === 'transparent') {
        return {};
    }

    if (selectedName.value === 'current') {
        return { backgroundColor: 'currentColor' };
    }

    if (selectedName.value === 'inherit') {
        return { backgroundColor: 'inherit' };
    }

    return { backgroundColor: cssValue };
});

// Methods
function isFlatColor(colorName) {
    const colorValue = colors.value[colorName];
    return typeof colorValue === 'string';
}

function getColorValue(colorName, shade) {
    return getColorCssValue(colorName, shade, colors.value);
}

function getSimpleModeShade(colorName) {
    return getDefaultShade(colorName, colors.value, defaultColorShade.value);
}

function selectColor(colorName, shade, close) {
    selectedName.value = colorName;
    selectedShade.value = shade;
    selectedRaw.value = null;

    const output = formatColorValue(colorName, shade, prefix.value);
    update(output);
    close();
}

function clearColor(close) {
    selectedName.value = null;
    selectedShade.value = null;
    selectedRaw.value = null;
    update(null);
    close();
}

function parseValue(val) {
    const parsed = parseColorValue(val, prefix.value, colors.value);
    selectedName.value = parsed.name;
    selectedShade.value = parsed.shade;
    selectedRaw.value = parsed.raw;
}

// Watch for external value changes
watch(() => props.value, (newValue) => {
    parseValue(newValue);
}, { immediate: true });
</script>

<style scoped>
.picker-content {
    max-height: min(24rem, 60vh);
    overflow: auto;
    scrollbar-width: thin;
    scrollbar-color: #9ca3af transparent;
}

.dark .picker-content {
    scrollbar-color: #6b7280 transparent;
}

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
</style>

<style>
/* Statamic's Popover content is fixed at w-64; let it fit a full row of swatches. */
.tailwind-color-picker-popover {
    width: max-content !important;
    max-width: calc(100vw - 2rem) !important;
}
</style>
