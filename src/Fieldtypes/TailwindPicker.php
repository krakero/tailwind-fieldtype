<?php

namespace Krakero\TailwindFieldtype\Fieldtypes;

use Krakero\TailwindFieldtype\Colors\ColorRepository;
use Statamic\Fields\Fieldtype;

class TailwindPicker extends Fieldtype
{
    protected static $handle = 'tailwind_picker';

    protected $icon = 'color';

    /**
     * Get the color repository instance.
     * Lazy-loaded from container to avoid constructor side effects.
     */
    protected function colorRepository(): ColorRepository
    {
        return app(ColorRepository::class);
    }

    /**
     * The blank/default value.
     */
    public function defaultValue()
    {
        return null;
    }

    /**
     * Pre-process the data before it gets sent to the publish page.
     */
    public function preProcess($data)
    {
        return $data;
    }

    /**
     * Process the data before it gets saved.
     */
    public function process($data)
    {
        return $data;
    }

    /**
     * Preload data for the Vue component.
     * Returns: {colors, sources, warnings}
     */
    public function preload(): array
    {
        $repository = $this->colorRepository();
        $colors = $repository->all();
        $sources = $repository->sources();
        $warnings = $repository->warnings();

        return [
            'colors' => $colors,
            'sources' => array_map(fn($path) => str_replace(base_path() . '/', '', $path), $sources),
            'warnings' => $warnings,
        ];
    }

    /**
     * Config field items for the blueprint editor.
     */
    protected function configFieldItems(): array
    {
        $colorNames = $this->colorRepository()->names();

        return [
            'available_colors' => [
                'display' => 'Available Colors',
                'instructions' => 'Choose which colors you want available in the picker',
                'placeholder' => 'Select Colors',
                'type' => 'select',
                'multiple' => true,
                'options' => $colorNames,
            ],
            'mode' => [
                'type' => 'button_group',
                'options' => [
                    'simple' => 'Simple',
                    'advanced' => 'Advanced',
                ],
                'instructions' => 'Choose "Simple" to only show the color, or "Advanced" for a full shade selector',
                'default' => 'advanced',
            ],
            'default_color' => [
                'type' => 'text',
                'display' => 'Default Color Weight',
                'instructions' => 'For colors that have multiple shades, which shade should we display? (e.g. `500` or `DEFAULT`)',
                'default' => '500',
                'if' => [
                    'mode' => 'simple',
                ]
            ],
            'class_prefix' => [
                'type' => 'text',
                'display' => 'Prefix',
                'instructions' => 'Add a prefix to the color value before saving. (e.g. `bg` or `text` or `border-t`) to get `bg-red-500`',
                'placeholder' => 'bg',
                'default' => '',
            ],
        ];
    }
}
