<?php

namespace Krakero\TailwindFieldtype\Fieldtypes;

use Statamic\Fields\Fieldtype;
use Illuminate\Support\Facades\File;

class TailwindPicker extends Fieldtype
{
    protected $color_names;
    protected $colors;
    protected $missing_file;
    protected $tailwind_colors;
    protected $tailwind_config;

    public function __construct()
    {
        $this->loadTailwindConfig();
        if ($this->tailwind_config) {
            $this->setTailwindColors();
            $this->setColorMap();
            $this->color_names = collect($this->tailwind_colors)->keys()->toArray();
        }
    }

    /**
     * The blank/default value.
     *
     * @return array
     */
    public function defaultValue()
    {
        return null;
    }

    /**
     * Pre-process the data before it gets sent to the publish page.
     *
     * @param  mixed  $data
     * @return array|mixed
     */
    public function preProcess($data)
    {
        return $data;
    }

    /**
     * Process the data before it gets saved.
     *
     * @param  mixed  $data
     * @return array|mixed
     */
    public function process($data)
    {
        return $data;
    }

    public function preload(): array
    {
        return [
            'colors' => $this->colors,
            'missing_file' => $this->missing_file,
        ];
    }


    protected function configFieldItems(): array
    {
        return [
            'available_colors' => [
                'display' => 'Available Colors',
                'instructions' => 'Choose which colors you want available in the picker',
                'placeholder' => 'Select Colors',
                'type' => 'select',
                'multiple' => true,
                'options' => $this->color_names,
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
                'instructions' => 'Add a prefix to the color value before saving. (e.g. `bg` or `text`) to get `bg-red-500`',
                'placeholder' => 'bg',
                'default' => '',
            ],
        ];
    }

    protected function setTailwindColors()
    {
        $this->tailwind_colors = $this->tailwind_config->theme->colors;
    }

    private function loadTailwindConfig()
    {
        if (File::exists(__DIR__.'/../../tailwind.config.php')) {
            $this->tailwind_config = require base_path('tailwind.config.php');
            $this->missing_file = false;
        } else {
            $this->tailwind_config = false;
            $this->missing_file = true;
        }
    }

    protected function setColorMap()
    {
        $this->colors = collect($this->tailwind_colors)->map(function ($color, $name) {
            return collect($color)->toArray();
        });
    }
}
