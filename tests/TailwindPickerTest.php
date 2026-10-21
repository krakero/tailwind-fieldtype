<?php

use Krakero\TailwindFieldtype\Fieldtypes\TailwindPicker;

test('preload returns correct shape', function () {
    config(['tailwind-fieldtype.include_defaults' => true]);

    $fieldtype = new TailwindPicker();
    $preload = $fieldtype->preload();

    expect($preload)->toHaveKeys(['colors', 'sources', 'warnings']);
    expect($preload['colors'])->toBeArray();
    expect($preload['sources'])->toBeArray();
    expect($preload['warnings'])->toBeArray();
});

test('preload includes colors from repository', function () {
    $fixturePath = $this->fixturesPath('basic-theme.css');
    config(['tailwind-fieldtype.css_paths' => [$fixturePath]]);

    $fieldtype = new TailwindPicker();
    $preload = $fieldtype->preload();

    expect($preload['colors'])->toHaveKey('red');
    expect($preload['colors'])->toHaveKey('primary');
    expect($preload['colors'])->toHaveKey('transparent');
});

test('preload returns relative paths in sources', function () {
    $fixturePath = $this->fixturesPath('basic-theme.css');
    config(['tailwind-fieldtype.css_paths' => [$fixturePath]]);

    $fieldtype = new TailwindPicker();
    $preload = $fieldtype->preload();

    expect($preload['sources'])->toBeArray();
    // Sources should be relative paths (without base_path prefix)
    foreach ($preload['sources'] as $source) {
        expect($source)->not->toStartWith(base_path());
    }
});

test('preload includes warnings when no CSS files found', function () {
    config([
        'tailwind-fieldtype.css_paths' => ['/nonexistent/file.css'],
        'tailwind-fieldtype.include_defaults' => false,
    ]);

    $fieldtype = new TailwindPicker();
    $preload = $fieldtype->preload();

    expect($preload['warnings'])->not->toBeEmpty();
    expect($preload['warnings'][0])->toContain('No Tailwind CSS file found');
});

test('preload returns empty warnings when files exist', function () {
    $fixturePath = $this->fixturesPath('basic-theme.css');
    config(['tailwind-fieldtype.css_paths' => [$fixturePath]]);

    $fieldtype = new TailwindPicker();
    $preload = $fieldtype->preload();

    expect($preload['warnings'])->toBe([]);
});

test('configFieldItems returns proper options', function () {
    $fixturePath = $this->fixturesPath('basic-theme.css');
    config(['tailwind-fieldtype.css_paths' => [$fixturePath]]);

    $fieldtype = new TailwindPicker();
    $reflection = new ReflectionClass($fieldtype);
    $method = $reflection->getMethod('configFieldItems');
    $method->setAccessible(true);
    $items = $method->invoke($fieldtype);

    expect($items)->toHaveKey('available_colors');
    expect($items)->toHaveKey('mode');
    expect($items)->toHaveKey('default_color');
    expect($items)->toHaveKey('class_prefix');
});

test('configFieldItems options contain color names', function () {
    $fixturePath = $this->fixturesPath('basic-theme.css');
    config(['tailwind-fieldtype.css_paths' => [$fixturePath]]);

    $fieldtype = new TailwindPicker();
    $reflection = new ReflectionClass($fieldtype);
    $method = $reflection->getMethod('configFieldItems');
    $method->setAccessible(true);
    $items = $method->invoke($fieldtype);

    expect($items['available_colors']['options'])->toBeArray();
    expect($items['available_colors']['options'])->toContain('red');
    expect($items['available_colors']['options'])->toContain('primary');
    expect($items['available_colors']['options'])->toContain('transparent');
});

test('can instantiate fieldtype without CSS files configured', function () {
    config([
        'tailwind-fieldtype.css_paths' => [],
        'tailwind-fieldtype.include_defaults' => false,
    ]);

    // Should not throw even with no CSS files
    $fieldtype = new TailwindPicker();

    expect($fieldtype)->toBeInstanceOf(TailwindPicker::class);
});

test('resolving fieldtype with no CSS files does not throw', function () {
    config([
        'tailwind-fieldtype.css_paths' => [],
        'tailwind-fieldtype.include_defaults' => false,
    ]);

    // Should be able to instantiate even with no files
    $fieldtype = new TailwindPicker();

    expect($fieldtype)->toBeInstanceOf(TailwindPicker::class);

    // preload should still work
    $preload = $fieldtype->preload();
    expect($preload)->toBeArray();
});

test('defaultValue returns null', function () {
    $fieldtype = new TailwindPicker();

    expect($fieldtype->defaultValue())->toBeNull();
});

test('preProcess returns data unchanged', function () {
    $fieldtype = new TailwindPicker();

    expect($fieldtype->preProcess('red-500'))->toBe('red-500');
    expect($fieldtype->preProcess('bg-blue-300'))->toBe('bg-blue-300');
    expect($fieldtype->preProcess(null))->toBeNull();
});

test('process returns data unchanged', function () {
    $fieldtype = new TailwindPicker();

    expect($fieldtype->process('red-500'))->toBe('red-500');
    expect($fieldtype->process('bg-blue-300'))->toBe('bg-blue-300');
    expect($fieldtype->process(null))->toBeNull();
});
