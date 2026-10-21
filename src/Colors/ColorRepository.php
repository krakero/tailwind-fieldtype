<?php

namespace Krakero\TailwindFieldtype\Colors;

use Illuminate\Support\Facades\File;

class ColorRepository
{
    protected array $colors = [];
    protected array $sources = [];
    protected array $warnings = [];
    protected ThemeParser $parser;

    public function __construct(?ThemeParser $parser = null)
    {
        $this->parser = $parser ?? new ThemeParser();
        $this->loadColors();
    }

    /**
     * Get all colors as a structured array.
     *
     * Returns: ['red' => ['50' => '...', '500' => '...'], 'primary' => '#3490dc', ...]
     * Grouping: --color-X-N where last segment is numeric -> group X, shade N.
     * Otherwise flat color named by full remainder.
     * If a name has both flat value and shades, flat is stored as shade 'DEFAULT'.
     */
    public function all(): array
    {
        return $this->colors;
    }

    /**
     * Get list of color names.
     */
    public function names(): array
    {
        return array_keys($this->colors);
    }

    /**
     * Get list of source file paths that were parsed (absolute paths).
     */
    public function sources(): array
    {
        return $this->sources;
    }

    /**
     * Get warnings about missing or problematic files.
     */
    public function warnings(): array
    {
        return $this->warnings;
    }

    /**
     * Load colors from default palette and configured CSS files.
     */
    protected function loadColors(): void
    {
        $declarations = [];

        // Load default Tailwind v4 palette if enabled
        $includeDefaults = config('tailwind-fieldtype.include_defaults', true);
        if ($includeDefaults) {
            $defaultPath = __DIR__ . '/../../resources/tailwind/default-theme.css';
            if (File::exists($defaultPath)) {
                $css = File::get($defaultPath);
                $defaultDeclarations = $this->parser->parse($css);
                $declarations = array_merge($declarations, $defaultDeclarations);
                $this->sources[] = $defaultPath;
            }
        }

        // Load user CSS files from config
        $cssPaths = config('tailwind-fieldtype.css_paths', [
            resource_path('css/site.css'),
            resource_path('css/app.css'),
        ]);

        // Track if any configured CSS paths exist
        $foundAny = false;
        $checkedPaths = [];

        foreach ($cssPaths as $path) {
            $checkedPaths[] = str_replace(base_path() . '/', '', $path);

            if (File::exists($path)) {
                $foundAny = true;
                $css = File::get($path);
                $basePath = dirname($path);
                $userDeclarations = $this->parser->parse($css, $basePath);
                $declarations = array_merge($declarations, $userDeclarations);
                $this->sources[] = $path;
            }
        }

        // Warn if no configured CSS files were found
        if (!$foundAny && !empty($cssPaths)) {
            $this->warnings[] = 'No Tailwind CSS file found. Checked: ' . implode(', ', $checkedPaths);
        }

        // Process declarations in order
        $this->processDeclarations($declarations);

        // Add special keywords if not excluded
        $this->addKeywords();

        // Apply exclusions from config
        $this->applyExclusions();
    }

    /**
     * Process declarations and build color map.
     * Handles: grouping, wildcards, var() resolution, DEFAULT merging.
     */
    protected function processDeclarations(array $declarations): void
    {
        foreach ($declarations as $declaration) {
            if (empty($declaration)) {
                continue;
            }

            $name = $declaration['name'];
            $value = $declaration['value'];

            // Handle wildcard reset: --color-*: initial
            if ($name === '*' && $value === 'initial') {
                $this->colors = [];
                continue;
            }

            // Handle group wildcard reset: --color-red-*: initial
            if (str_ends_with($name, '-*') && $value === 'initial') {
                $groupName = substr($name, 0, -2);
                unset($this->colors[$groupName]);
                continue;
            }

            // Resolve var() references
            $value = $this->resolveVar($value);

            // Determine if this is a shade or flat color
            $parts = explode('-', $name);
            $lastPart = end($parts);

            // If last part is numeric (50, 100, 500, 950, etc.) -> shade
            if (is_numeric($lastPart)) {
                $colorName = implode('-', array_slice($parts, 0, -1));
                $shade = $lastPart;

                if (!isset($this->colors[$colorName])) {
                    $this->colors[$colorName] = [];
                }

                // If color was previously flat, move flat value to DEFAULT
                if (isset($this->colors[$colorName]) && is_string($this->colors[$colorName])) {
                    $flatValue = $this->colors[$colorName];
                    $this->colors[$colorName] = ['DEFAULT' => $flatValue];
                }

                $this->colors[$colorName][$shade] = $value;
            } else {
                // Flat color
                $colorName = $name;

                // If color already has shades, add as DEFAULT
                if (isset($this->colors[$colorName]) && is_array($this->colors[$colorName])) {
                    $this->colors[$colorName]['DEFAULT'] = $value;
                } else {
                    // Store as flat color
                    $this->colors[$colorName] = $value;
                }
            }
        }
    }

    /**
     * Resolve var(--color-...) references.
     * Only resolves if the referenced color exists.
     */
    protected function resolveVar(string $value): string
    {
        // Pattern: var(--color-NAME)
        if (!preg_match('/^var\(--color-([a-z0-9-]+)\)$/i', $value, $match)) {
            return $value;
        }

        $refName = $match[1];

        // Look up the referenced color
        // Check if it's a shade reference (e.g., var(--color-red-500))
        $parts = explode('-', $refName);
        $lastPart = end($parts);

        if (is_numeric($lastPart)) {
            $colorName = implode('-', array_slice($parts, 0, -1));
            $shade = $lastPart;

            if (isset($this->colors[$colorName][$shade])) {
                return $this->colors[$colorName][$shade];
            }
        }

        // Check if it's a flat color reference
        if (isset($this->colors[$refName])) {
            $color = $this->colors[$refName];
            return is_string($color) ? $color : ($color['DEFAULT'] ?? $value);
        }

        // Can't resolve, return original
        return $value;
    }

    /**
     * Add special color keywords.
     */
    protected function addKeywords(): void
    {
        $keywords = [
            'transparent' => 'transparent',
            'current' => 'currentColor',
            'inherit' => 'inherit',
        ];

        foreach ($keywords as $name => $value) {
            if (!isset($this->colors[$name])) {
                $this->colors[$name] = $value;
            }
        }
    }

    /**
     * Apply color exclusions from config.
     */
    protected function applyExclusions(): void
    {
        $exclude = config('tailwind-fieldtype.exclude', []);

        foreach ($exclude as $colorName) {
            unset($this->colors[$colorName]);
        }
    }
}
