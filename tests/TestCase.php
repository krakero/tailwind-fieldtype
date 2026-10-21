<?php

namespace Krakero\TailwindFieldtype\Tests;

use Krakero\TailwindFieldtype\ServiceProvider;
use Statamic\Testing\AddonTestCase;

abstract class TestCase extends AddonTestCase
{
    protected string $addonServiceProvider = ServiceProvider::class;

    protected function getPackageProviders($app)
    {
        return [
            \Statamic\Providers\StatamicServiceProvider::class,
            ServiceProvider::class,
        ];
    }

    protected function resolveApplicationConfiguration($app)
    {
        parent::resolveApplicationConfiguration($app);

        // Set up default config for testing
        $app['config']->set('tailwind-fieldtype', [
            'css_paths' => [],
            'include_defaults' => true,
            'exclude' => [],
        ]);
    }

    protected function fixturesPath(string $path = ''): string
    {
        return __DIR__ . '/__fixtures__' . ($path ? '/' . ltrim($path, '/') : '');
    }
}
