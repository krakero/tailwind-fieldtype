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
//        if (File::exists(__DIR__.'/../../tailwind.config.php')) {
            TailwindPicker::register();
//        } else {
//            Toast::error('No Tailwind Config Found run `kit:tailwind-config`');
//        }

        $this->commands([
            TailwindConfigGenerator::class,
        ]);
    }
}
