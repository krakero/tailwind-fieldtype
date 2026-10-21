<?php

use Illuminate\Support\Facades\Artisan;

test('command runs successfully with defaults', function () {
    config(['tailwind-fieldtype.include_defaults' => true]);

    $exitCode = Artisan::call('tailwind-fieldtype:colors');

    expect($exitCode)->toBe(0);
});

test('command runs successfully with custom CSS file', function () {
    $fixturePath = $this->fixturesPath('basic-theme.css');
    config(['tailwind-fieldtype.css_paths' => [$fixturePath]]);

    $exitCode = Artisan::call('tailwind-fieldtype:colors');
    $output = Artisan::output();

    expect($exitCode)->toBe(0);
    expect($output)->toContain('Color Sources:');
    expect($output)->toContain('Detected Colors:');
});

test('command displays color table', function () {
    $fixturePath = $this->fixturesPath('basic-theme.css');
    config(['tailwind-fieldtype.css_paths' => [$fixturePath]]);

    Artisan::call('tailwind-fieldtype:colors');
    $output = Artisan::output();

    expect($output)->toContain('red');
    expect($output)->toContain('primary');
    expect($output)->toContain('transparent');
});

test('command displays warnings when no CSS files exist', function () {
    config([
        'tailwind-fieldtype.css_paths' => ['/nonexistent/file.css'],
        'tailwind-fieldtype.include_defaults' => false,
    ]);

    Artisan::call('tailwind-fieldtype:colors');
    $output = Artisan::output();

    expect($output)->toContain('Warnings:');
    expect($output)->toContain('No Tailwind CSS file found');
});

test('command shows sources list', function () {
    $fixturePath = $this->fixturesPath('basic-theme.css');
    config(['tailwind-fieldtype.css_paths' => [$fixturePath]]);

    Artisan::call('tailwind-fieldtype:colors');
    $output = Artisan::output();

    expect($output)->toContain('Color Sources:');
});

test('command returns failure code when no colors detected', function () {
    config([
        'tailwind-fieldtype.css_paths' => [],
        'tailwind-fieldtype.include_defaults' => false,
    ]);

    $exitCode = Artisan::call('tailwind-fieldtype:colors');

    expect($exitCode)->toBe(1);
});

test('command displays total color count', function () {
    $fixturePath = $this->fixturesPath('basic-theme.css');
    config(['tailwind-fieldtype.css_paths' => [$fixturePath]]);

    Artisan::call('tailwind-fieldtype:colors');
    $output = Artisan::output();

    expect($output)->toContain('Total:');
    expect($output)->toContain('colors');
});
