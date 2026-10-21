<?php

namespace Krakero\TailwindFieldtype;

use Krakero\TailwindFieldtype\Colors\ColorRepository;
use Krakero\TailwindFieldtype\Console\Commands\ShowColorsCommand;
use Krakero\TailwindFieldtype\Fieldtypes\TailwindPicker;
use Statamic\Providers\AddonServiceProvider;

class ServiceProvider extends AddonServiceProvider
{
    protected $vite = [
        'input' => [
            'resources/js/cp.js',
            'resources/css/cp.css',
        ],
        'publicDirectory' => 'resources/dist',
        'hotFile' => __DIR__ . '/../resources/dist/hot',
    ];

    public function register()
    {
        parent::register();

        // Bind ColorRepository as scoped (per-request, Octane-safe)
        $this->app->scoped(ColorRepository::class, function ($app) {
            return new ColorRepository();
        });
    }

    public function bootAddon()
    {
        TailwindPicker::register();

        $this->commands([
            ShowColorsCommand::class,
        ]);
    }
}
