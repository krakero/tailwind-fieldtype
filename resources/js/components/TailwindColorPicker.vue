<template>
    <div class="tailwind-color-picker">
        <!-- Warnings -->
        <div v-if="meta.warnings && meta.warnings.length > 0" class="mb-2">
            <div v-for="(warning, idx) in meta.warnings" :key="idx" class="text-xs text-red-500 dark:text-red-400">
                {{ warning }}
            </div>
        </div>

        <!-- Color Picker Popover -->
        <Popover>
            <template #trigger>
                <Button
                    :disabled="isReadOnly"
                    variant="default"
                    class="h-9 px-2 justify-start gap-2"
                >
                    <!-- Swatch -->
                    <div
                        v-if="selectedName"
                        class="h-5 w-5 rounded border border-gray-300 dark:border-gray-600 flex-shrink-0"
                        :class="{ 'checkerboard': selectedName === 'transparent' }"
                        :style="swatchStyle"
                        :aria-label="selectedLabel"
                    ></div>
                    <svg
                        v-else
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

                    <!-- Label -->
                    <span v-if="selectedName" class="text-sm">{{ selectedLabel }}</span>
                    <span v-else class="text-sm text-gray-500 dark:text-gray-400">Select a color</span>
                </Button>
            </template>

            <template #default="{ close }">
                <div class="p-4 max-h-96 overflow-y-auto w-80">
                    <!-- Clear button -->
                    <div v-if="selectedName" class="mb-3 pb-3 border-b border-gray-200 dark:border-gray-700">
                        <Button @click="clearColor(close)" variant="ghost" size="sm" class="w-full justify-start">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-4 w-4 mr-2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            Clear
                        </Button>
                    </div>

                    <!-- Color grid -->
                    <div class="space-y-3">
                        <div v-for="colorName in availableColorNames" :key="colorName" class="space-y-1">
                            <!-- Color name label -->
                            <div class="text-xs font-medium text-gray-600 dark:text-gray-400 capitalize">
                                {{ colorName }}
                            </div>

                            <!-- Advanced mode: show all shades -->
                            <div v-if="mode === 'advanced'" class="flex flex-wrap gap-1">
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
                            <div v-else class="flex flex-wrap gap-1">
                                <ColorSwatch
                                    :name="colorName"
                                    :shade="getSimpleModeShade(colorName)"
                                    :css-value="getColorValue(colorName, getSimpleModeShade(colorName))"
                                    :is-selected="selectedName === colorName"
                                    @select="selectColor(colorName, getSimpleModeShade(colorName), close)"
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        </Popover>
    </div>
</template>

<script setup>
import { computed, ref, watch } from 'vue';
import { Fieldtype } from '@statamic/cms';
import { Popover, Button } from '@statamic/cms/ui';
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
.checkerboard {
    background-image:
        linear-gradient(45deg, #ccc 25%, transparent 25%),
        linear-gradient(-45deg, #ccc 25%, transparent 25%),
        linear-gradient(45deg, transparent 75%, #ccc 75%),
        linear-gradient(-45deg, transparent 75%, #ccc 75%);
    background-size: 8px 8px;
    background-position: 0 0, 0 4px, 4px -4px, -4px 0px;
}

.dark .checkerboard {
    background-image:
        linear-gradient(45deg, #555 25%, transparent 25%),
        linear-gradient(-45deg, #555 25%, transparent 25%),
        linear-gradient(45deg, transparent 75%, #555 75%),
        linear-gradient(-45deg, transparent 75%, #555 75%);
}
</style>
