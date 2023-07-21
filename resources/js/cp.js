import TailwindColorPicker from './TailwindColorPicker.vue';

Statamic.booting(() => {
    Statamic.component('tailwind_picker-fieldtype', TailwindColorPicker)
});