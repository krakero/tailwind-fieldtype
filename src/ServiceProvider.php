<?php

namespace Krakero\TailwindFieldtype;

use Krakero\TailwindFieldtype\Console\Commands\TailwindConfigGenerator;
use Krakero\TailwindFieldtype\Fieldtypes\TailwindPicker;
use Statamic\Facades\CP\Toast;
use Statamic\Facades\File;
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

    public function bootAddon()
    {
        TailwindPicker::register();

        $this->commands([
            TailwindConfigGenerator::class,
        ]);
    }
}
