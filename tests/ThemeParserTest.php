<?php

use Krakero\TailwindFieldtype\Colors\ThemeParser;

test('parses basic @theme block', function () {
    $css = file_get_contents($this->fixturesPath('basic-theme.css'));
    $parser = new ThemeParser();
    $declarations = $parser->parse($css);

    expect($declarations)->toHaveCount(5);
    expect($declarations[0])->toBe(['name' => 'red-50', 'value' => 'oklch(0.97 0.013 17.38)']);
    expect($declarations[1])->toBe(['name' => 'red-500', 'value' => 'oklch(0.637 0.237 25.331)']);
    expect($declarations[2])->toBe(['name' => 'red-950', 'value' => 'oklch(0.318 0.119 26.555)']);
    expect($declarations[3])->toBe(['name' => 'primary', 'value' => '#3490dc']);
    expect($declarations[4])->toBe(['name' => 'transparent', 'value' => 'transparent']);
});

test('handles nested @keyframes in @theme block', function () {
    $css = file_get_contents($this->fixturesPath('nested-keyframes.css'));
    $parser = new ThemeParser();
    $declarations = $parser->parse($css);

    // Should get both color declarations, ignoring the nested @keyframes
    expect($declarations)->toHaveCount(2);
    expect($declarations[0])->toBe(['name' => 'blue-500', 'value' => 'oklch(0.5 0.2 240)']);
    expect($declarations[1])->toBe(['name' => 'green-500', 'value' => 'oklch(0.6 0.15 140)']);
});

test('parses same-line declarations', function () {
    $css = file_get_contents($this->fixturesPath('same-line-declarations.css'));
    $parser = new ThemeParser();
    $declarations = $parser->parse($css);

    expect($declarations)->toHaveCount(3);
    expect($declarations[0])->toBe(['name' => 'a', 'value' => '#fff']);
    expect($declarations[1])->toBe(['name' => 'b', 'value' => '#000']);
    expect($declarations[2])->toBe(['name' => 'c', 'value' => '#999']);
});

test('handles multi-line values', function () {
    $css = file_get_contents($this->fixturesPath('multi-line-value.css'));
    $parser = new ThemeParser();
    $declarations = $parser->parse($css);

    expect($declarations)->toHaveCount(2);
    expect($declarations[0]['name'])->toBe('gradient');
    expect($declarations[0]['value'])->toContain('linear-gradient');
    expect($declarations[1]['name'])->toBe('complex');
    expect($declarations[1]['value'])->toContain('oklch');
});

test('parses multiple @theme blocks', function () {
    $css = <<<CSS
    @theme {
        --color-red-500: #ff0000;
    }

    @theme {
        --color-blue-500: #0000ff;
    }
    CSS;

    $parser = new ThemeParser();
    $declarations = $parser->parse($css);

    expect($declarations)->toHaveCount(2);
    expect($declarations[0])->toBe(['name' => 'red-500', 'value' => '#ff0000']);
    expect($declarations[1])->toBe(['name' => 'blue-500', 'value' => '#0000ff']);
});

test('parses @theme modifiers', function () {
    $css = file_get_contents($this->fixturesPath('modifiers.css'));
    $parser = new ThemeParser();
    $declarations = $parser->parse($css);

    expect($declarations)->toHaveCount(4);
    expect($declarations[0])->toBe(['name' => 'base', 'value' => '#000']);
    expect($declarations[1])->toBe(['name' => 'inline', 'value' => '#111']);
    expect($declarations[2])->toBe(['name' => 'static', 'value' => '#222']);
    expect($declarations[3])->toBe(['name' => 'default', 'value' => '#333']);
});

test('ignores comments', function () {
    $css = file_get_contents($this->fixturesPath('comments.css'));
    $parser = new ThemeParser();
    $declarations = $parser->parse($css);

    expect($declarations)->toHaveCount(2);
    expect($declarations[0])->toBe(['name' => 'red-500', 'value' => '#ff0000']);
    expect($declarations[1])->toBe(['name' => 'blue-500', 'value' => '#0000ff']);
});

test('handles --color-*: initial clearing', function () {
    $css = <<<CSS
    @theme {
        --color-red-500: #ff0000;
        --color-blue-500: #0000ff;
        --color-*: initial;
        --color-green-500: #00ff00;
    }
    CSS;

    $parser = new ThemeParser();
    $declarations = $parser->parse($css);

    expect($declarations)->toHaveCount(4);
    expect($declarations[0])->toBe(['name' => 'red-500', 'value' => '#ff0000']);
    expect($declarations[1])->toBe(['name' => 'blue-500', 'value' => '#0000ff']);
    expect($declarations[2])->toBe(['name' => '*', 'value' => 'initial']);
    expect($declarations[3])->toBe(['name' => 'green-500', 'value' => '#00ff00']);
});

test('handles --color-group-*: initial clearing', function () {
    $css = <<<CSS
    @theme {
        --color-red-500: #ff0000;
        --color-red-600: #cc0000;
        --color-red-*: initial;
        --color-blue-500: #0000ff;
    }
    CSS;

    $parser = new ThemeParser();
    $declarations = $parser->parse($css);

    expect($declarations)->toHaveCount(4);
    expect($declarations[0])->toBe(['name' => 'red-500', 'value' => '#ff0000']);
    expect($declarations[1])->toBe(['name' => 'red-600', 'value' => '#cc0000']);
    expect($declarations[2])->toBe(['name' => 'red-*', 'value' => 'initial']);
    expect($declarations[3])->toBe(['name' => 'blue-500', 'value' => '#0000ff']);
});

test('ignores non-color variables', function () {
    $css = file_get_contents($this->fixturesPath('non-color-vars.css'));
    $parser = new ThemeParser();
    $declarations = $parser->parse($css);

    expect($declarations)->toHaveCount(2);
    expect($declarations[0])->toBe(['name' => 'red-500', 'value' => '#ff0000']);
    expect($declarations[1])->toBe(['name' => 'blue-500', 'value' => '#0000ff']);
});

test('follows local @import with basePath', function () {
    $css = file_get_contents($this->fixturesPath('import-main.css'));
    $parser = new ThemeParser();
    $declarations = $parser->parse($css, $this->fixturesPath());

    // Should include colors from imported files
    expect($declarations)->toContainEqual(['name' => 'imported-blue', 'value' => '#0000ff']);
    expect($declarations)->toContainEqual(['name' => 'imported-red', 'value' => '#ff0000']);
    expect($declarations)->toContainEqual(['name' => 'imported-green', 'value' => '#00ff00']);
    expect($declarations)->toContainEqual(['name' => 'primary', 'value' => '#3490dc']);
});

test('handles @import with layer() syntax', function () {
    $css = '@import "./import-colors.css" layer(theme);';
    $parser = new ThemeParser();
    $declarations = $parser->parse($css, $this->fixturesPath());

    expect($declarations)->toContainEqual(['name' => 'imported-blue', 'value' => '#0000ff']);
    expect($declarations)->toContainEqual(['name' => 'imported-red', 'value' => '#ff0000']);
});

test('handles @import with url() syntax', function () {
    $css = '@import url("./import-colors.css");';
    $parser = new ThemeParser();
    $declarations = $parser->parse($css, $this->fixturesPath());

    expect($declarations)->toContainEqual(['name' => 'imported-blue', 'value' => '#0000ff']);
    expect($declarations)->toContainEqual(['name' => 'imported-red', 'value' => '#ff0000']);
});

test('prevents recursion in @import', function () {
    $css = file_get_contents($this->fixturesPath('recursion-import.css'));
    $parser = new ThemeParser();
    $declarations = $parser->parse($css, $this->fixturesPath());

    // Should process once and not loop infinitely
    expect($declarations)->toContainEqual(['name' => 'recursive', 'value' => '#ff0000']);
});

test('skips missing import file without error', function () {
    $css = '@import "./nonexistent.css";';
    $parser = new ThemeParser();
    $declarations = $parser->parse($css, $this->fixturesPath());

    expect($declarations)->toBe([]);
});

test('skips package imports', function () {
    $css = '@import "tailwindcss";';
    $parser = new ThemeParser();
    $declarations = $parser->parse($css, $this->fixturesPath());

    expect($declarations)->toBe([]);
});

test('returns empty array when no @theme blocks', function () {
    $css = '.foo { color: red; }';
    $parser = new ThemeParser();
    $declarations = $parser->parse($css);

    expect($declarations)->toBe([]);
});
