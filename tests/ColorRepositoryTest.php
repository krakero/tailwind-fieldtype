<?php

use Krakero\TailwindFieldtype\Colors\ColorRepository;

beforeEach(function () {
    // Reset config for each test
    config([
        'tailwind-fieldtype.css_paths' => [],
        'tailwind-fieldtype.include_defaults' => true,
        'tailwind-fieldtype.exclude' => [],
    ]);
});

test('includes default palette when include_defaults is true', function () {
    config(['tailwind-fieldtype.include_defaults' => true]);

    $repository = app(ColorRepository::class);
    $colors = $repository->all();

    // Should have some default Tailwind colors
    expect($colors)->toHaveKey('red');
    expect($colors['red'])->toHaveKey('500');
});

test('excludes default palette when include_defaults is false', function () {
    config([
        'tailwind-fieldtype.include_defaults' => false,
        'tailwind-fieldtype.css_paths' => [],
    ]);

    $repository = app(ColorRepository::class);
    $colors = $repository->all();

    // Should only have keywords (transparent, current, inherit)
    expect($colors)->toHaveKey('transparent');
    expect($colors)->toHaveKey('current');
    expect($colors)->toHaveKey('inherit');
    expect($colors)->not->toHaveKey('red');
});

test('groups numeric shade colors', function () {
    $fixturePath = $this->fixturesPath('basic-theme.css');
    config(['tailwind-fieldtype.css_paths' => [$fixturePath]]);

    $repository = app(ColorRepository::class);
    $colors = $repository->all();

    expect($colors)->toHaveKey('red');
    expect($colors['red'])->toBeArray();
    expect($colors['red'])->toHaveKey('50');
    expect($colors['red'])->toHaveKey('500');
    expect($colors['red'])->toHaveKey('950');
});

test('handles flat colors without shades', function () {
    $fixturePath = $this->fixturesPath('basic-theme.css');
    config(['tailwind-fieldtype.css_paths' => [$fixturePath]]);

    $repository = app(ColorRepository::class);
    $colors = $repository->all();

    expect($colors)->toHaveKey('primary');
    expect($colors['primary'])->toBe('#3490dc');
});

test('handles hyphenated color names with shades', function () {
    $css = <<<CSS
    @theme {
        --color-light-blue-500: #87ceeb;
        --color-light-blue-600: #6bb6d9;
    }
    CSS;

    $tmpFile = tempnam(sys_get_temp_dir(), 'test') . '.css';
    file_put_contents($tmpFile, $css);
    config(['tailwind-fieldtype.css_paths' => [$tmpFile]]);

    $repository = app(ColorRepository::class);
    $colors = $repository->all();

    expect($colors)->toHaveKey('light-blue');
    expect($colors['light-blue'])->toHaveKey('500');
    expect($colors['light-blue'])->toHaveKey('600');

    unlink($tmpFile);
});

test('handles hyphenated flat color names', function () {
    $css = <<<CSS
    @theme {
        --color-brand-dark: #123456;
    }
    CSS;

    $tmpFile = tempnam(sys_get_temp_dir(), 'test') . '.css';
    file_put_contents($tmpFile, $css);
    config(['tailwind-fieldtype.css_paths' => [$tmpFile]]);

    $repository = app(ColorRepository::class);
    $colors = $repository->all();

    expect($colors)->toHaveKey('brand-dark');
    expect($colors['brand-dark'])->toBe('#123456');
});

test('merges flat value as DEFAULT shade', function () {
    $css = <<<CSS
    @theme {
        --color-blue: #0000ff;
        --color-blue-500: #0066ff;
    }
    CSS;

    $tmpFile = tempnam(sys_get_temp_dir(), 'test') . '.css';
    file_put_contents($tmpFile, $css);
    config(['tailwind-fieldtype.css_paths' => [$tmpFile]]);

    $repository = app(ColorRepository::class);
    $colors = $repository->all();

    expect($colors)->toHaveKey('blue');
    expect($colors['blue'])->toBeArray();
    expect($colors['blue'])->toHaveKey('DEFAULT');
    expect($colors['blue']['DEFAULT'])->toBe('#0000ff');
    expect($colors['blue'])->toHaveKey('500');
    expect($colors['blue']['500'])->toBe('#0066ff');
});

test('resolves var() references', function () {
    $fixturePath = $this->fixturesPath('var-references.css');
    config(['tailwind-fieldtype.css_paths' => [$fixturePath]]);

    $repository = app(ColorRepository::class);
    $colors = $repository->all();

    expect($colors)->toHaveKey('primary');
    expect($colors['primary'])->toBe('#3490dc');
    expect($colors)->toHaveKey('primary-dark');
    expect($colors['primary-dark'])->toBe('#3490dc');
    expect($colors)->toHaveKey('link');
    expect($colors['link'])->toBe('#3490dc');
});

test('excludes colors in exclude list', function () {
    $fixturePath = $this->fixturesPath('basic-theme.css');
    config([
        'tailwind-fieldtype.css_paths' => [$fixturePath],
        'tailwind-fieldtype.exclude' => ['red', 'primary'],
    ]);

    $repository = app(ColorRepository::class);
    $colors = $repository->all();

    expect($colors)->not->toHaveKey('red');
    expect($colors)->not->toHaveKey('primary');
    expect($colors)->toHaveKey('transparent'); // keywords not excluded
});

test('excludes keywords when in exclude list', function () {
    $fixturePath = $this->fixturesPath('basic-theme.css');
    config([
        'tailwind-fieldtype.css_paths' => [$fixturePath],
        'tailwind-fieldtype.exclude' => ['transparent'],
    ]);

    $repository = app(ColorRepository::class);
    $colors = $repository->all();

    expect($colors)->not->toHaveKey('transparent');
    expect($colors)->toHaveKey('current');
    expect($colors)->toHaveKey('inherit');
});

test('handles --color-*: initial clearing all colors', function () {
    $fixturePath = $this->fixturesPath('initial-clear.css');
    config([
        'tailwind-fieldtype.css_paths' => [$fixturePath],
        'tailwind-fieldtype.include_defaults' => false,
    ]);

    $repository = app(ColorRepository::class);
    $colors = $repository->all();

    // Only colors after --color-*: initial should exist
    expect($colors)->not->toHaveKey('red');
    expect($colors)->not->toHaveKey('blue');
    expect($colors)->toHaveKey('green');
    expect($colors)->toHaveKey('yellow');
});

test('handles --color-group-*: initial clearing specific group', function () {
    $fixturePath = $this->fixturesPath('initial-clear.css');
    config([
        'tailwind-fieldtype.css_paths' => [$fixturePath],
        'tailwind-fieldtype.include_defaults' => false,
    ]);

    $repository = app(ColorRepository::class);
    $colors = $repository->all();

    // red group should be cleared, but yellow should exist
    expect($colors)->not->toHaveKey('red');
    expect($colors)->toHaveKey('yellow');
    expect($colors)->toHaveKey('green');
});

test('returns color names list', function () {
    $fixturePath = $this->fixturesPath('basic-theme.css');
    config(['tailwind-fieldtype.css_paths' => [$fixturePath]]);

    $repository = app(ColorRepository::class);
    $names = $repository->names();

    expect($names)->toBeArray();
    expect($names)->toContain('red');
    expect($names)->toContain('primary');
    expect($names)->toContain('transparent');
});

test('returns sources list with absolute paths', function () {
    $fixturePath = $this->fixturesPath('basic-theme.css');
    config(['tailwind-fieldtype.css_paths' => [$fixturePath]]);

    $repository = app(ColorRepository::class);
    $sources = $repository->sources();

    expect($sources)->toBeArray();
    expect($sources)->toContain($fixturePath);
});

test('includes default theme in sources when include_defaults is true', function () {
    config(['tailwind-fieldtype.include_defaults' => true]);

    $repository = app(ColorRepository::class);
    $sources = $repository->sources();

    expect($sources)->toBeArray();
    $hasDefaultTheme = collect($sources)->contains(fn($path) => str_contains($path, 'default-theme.css'));
    expect($hasDefaultTheme)->toBeTrue();
});

test('excludes default theme from sources when include_defaults is false', function () {
    config(['tailwind-fieldtype.include_defaults' => false]);

    $repository = app(ColorRepository::class);
    $sources = $repository->sources();

    $hasDefaultTheme = collect($sources)->contains(fn($path) => str_contains($path, 'default-theme.css'));
    expect($hasDefaultTheme)->toBeFalse();
});

test('warnings method returns warning when no CSS files exist', function () {
    config([
        'tailwind-fieldtype.css_paths' => [
            '/nonexistent/file1.css',
            '/nonexistent/file2.css',
        ],
        'tailwind-fieldtype.include_defaults' => false,
    ]);

    $repository = app(ColorRepository::class);
    $warnings = $repository->warnings();

    expect($warnings)->toBeArray();
    expect($warnings)->not->toBeEmpty();
    expect($warnings[0])->toContain('No Tailwind CSS file found');
});

test('warnings method returns empty when CSS files exist', function () {
    $fixturePath = $this->fixturesPath('basic-theme.css');
    config(['tailwind-fieldtype.css_paths' => [$fixturePath]]);

    $repository = app(ColorRepository::class);
    $warnings = $repository->warnings();

    expect($warnings)->toBe([]);
});

test('warnings method warns when css_paths exist but files missing even with defaults', function () {
    config([
        'tailwind-fieldtype.css_paths' => ['/nonexistent/file.css'],
        'tailwind-fieldtype.include_defaults' => true,
    ]);

    $repository = app(ColorRepository::class);
    $warnings = $repository->warnings();

    // Should warn even with defaults - tells editors their custom theme isn't being read
    expect($warnings)->not->toBeEmpty();
    expect($warnings[0])->toContain('No Tailwind CSS file found');
});

test('warnings method returns empty when css_paths is empty with defaults', function () {
    config([
        'tailwind-fieldtype.css_paths' => [],
        'tailwind-fieldtype.include_defaults' => true,
    ]);

    $repository = app(ColorRepository::class);
    $warnings = $repository->warnings();

    // No warning for empty css_paths (defaults-only mode)
    expect($warnings)->toBe([]);
});

test('always includes keyword colors', function () {
    config([
        'tailwind-fieldtype.include_defaults' => false,
        'tailwind-fieldtype.css_paths' => [],
    ]);

    $repository = app(ColorRepository::class);
    $colors = $repository->all();

    expect($colors)->toHaveKey('transparent');
    expect($colors['transparent'])->toBe('transparent');
    expect($colors)->toHaveKey('current');
    expect($colors['current'])->toBe('currentColor');
    expect($colors)->toHaveKey('inherit');
    expect($colors['inherit'])->toBe('inherit');
});

test('processes multiple CSS files in order', function () {
    $css1 = <<<CSS
    @theme {
        --color-primary: #ff0000;
    }
    CSS;

    $css2 = <<<CSS
    @theme {
        --color-primary: #0000ff;
        --color-secondary: #00ff00;
    }
    CSS;

    $tmpFile1 = tempnam(sys_get_temp_dir(), 'test') . '.css';
    $tmpFile2 = tempnam(sys_get_temp_dir(), 'test') . '.css';
    file_put_contents($tmpFile1, $css1);
    file_put_contents($tmpFile2, $css2);

    config([
        'tailwind-fieldtype.css_paths' => [$tmpFile1, $tmpFile2],
        'tailwind-fieldtype.include_defaults' => false,
    ]);

    $repository = app(ColorRepository::class);
    $colors = $repository->all();

    // Second file should override first
    expect($colors['primary'])->toBe('#0000ff');
    expect($colors['secondary'])->toBe('#00ff00');

    unlink($tmpFile1);
    unlink($tmpFile2);
});

test('skips nonexistent CSS files without error', function () {
    $fixturePath = $this->fixturesPath('basic-theme.css');
    config([
        'tailwind-fieldtype.css_paths' => [
            '/nonexistent/file.css',
            $fixturePath,
        ],
    ]);

    $repository = app(ColorRepository::class);
    $colors = $repository->all();

    expect($colors)->toHaveKey('red');
    expect($colors)->toHaveKey('primary');
});
