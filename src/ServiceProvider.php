<?php

namespace Krakero\TailwindFieldtype;

use Krakero\TailwindFieldtype\Console\Commands\TailwindConfigGenerator;
use Krakero\TailwindFieldtype\Fieldtypes\TailwindPicker;
use Statamic\Providers\AddonServiceProvider;

class ServiceProvider extends AddonServiceProvider
{
    public function bootAddon()
    {
        TailwindPicker::register();
    }

    public function boot()
    {
        $this->commands([
            TailwindConfigGenerator::class,
        ]);

        parent::boot();
    }


}
