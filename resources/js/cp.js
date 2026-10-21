import TailwindColorPicker from './components/TailwindColorPicker.vue';

Statamic.booting(() => {
    Statamic.$components.register('tailwind_picker-fieldtype', TailwindColorPicker);
});