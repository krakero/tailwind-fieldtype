<?php

namespace Krakero\TailwindFieldtype\Colors;

class ThemeParser
{
    protected array $importedFiles = [];
    protected int $maxImportDepth = 10;

    /**
     * Parse CSS content to extract @theme block declarations.
     *
     * Returns ordered list: [['name' => 'red-500', 'value' => 'oklch(...)'], ...]
     * Handles @theme, @theme inline, @theme static, @theme default, etc.
     * Follows local relative @import when $basePath is provided.
     *
     * @param string $css CSS content to parse
     * @param string|null $basePath Base directory for resolving relative @import paths
     * @return array Ordered list of color declarations
     */
    public function parse(string $css, ?string $basePath = null): array
    {
        $this->importedFiles = [];

        // Process @import statements first
        if ($basePath !== null) {
            $css = $this->resolveImports($css, $basePath, 0);
        }

        // Remove CSS comments (/* ... */)
        $css = $this->removeComments($css);

        // Extract all @theme blocks
        $declarations = [];

        // Find @theme blocks by scanning for @theme and matching braces
        $offset = 0;
        while (($pos = strpos($css, '@theme', $offset)) !== false) {
            // Find opening brace (handle both '@theme {' and '@theme{')
            $bracePos = strpos($css, '{', $pos);
            if ($bracePos === false) {
                break;
            }

            // Find matching closing brace by counting depth
            $depth = 1;
            $i = $bracePos + 1;
            $length = strlen($css);

            while ($i < $length && $depth > 0) {
                if ($css[$i] === '{') {
                    $depth++;
                } elseif ($css[$i] === '}') {
                    $depth--;
                }
                $i++;
            }

            if ($depth === 0) {
                // Extract the block content (between braces)
                $blockContent = substr($css, $bracePos + 1, $i - $bracePos - 2);

                // Extract only top-level declarations from this block
                $blockDeclarations = $this->extractDeclarations($blockContent);
                $declarations = array_merge($declarations, $blockDeclarations);
            }

            $offset = $i;
        }

        return $declarations;
    }

    /**
     * Remove CSS comments from content.
     */
    protected function removeComments(string $css): string
    {
        // Remove /* ... */ comments (including multi-line)
        return preg_replace('/\/\*.*?\*\//s', '', $css);
    }

    /**
     * Resolve @import statements recursively.
     * Handles: @import "path", @import 'path', @import url("path"), @import "path" layer(x)
     * Only follows local relative imports that resolve to existing files.
     */
    protected function resolveImports(string $css, string $basePath, int $depth): string
    {
        if ($depth >= $this->maxImportDepth) {
            return $css; // Recursion guard
        }

        // Pattern: @import followed by quoted path or url(), optional layer/media
        // Captures: "./path" or 'path' or url("path")
        $pattern = '/@import\s+(?:url\()?["\']([^"\']+)["\'](?:\))?[^;]*;/';

        return preg_replace_callback($pattern, function ($matches) use ($basePath, $depth) {
            $path = $matches[1];

            // Try to resolve as relative path
            // If path doesn't start with ./ or ../, try both with and without ./
            $tryPaths = [];
            if (str_starts_with($path, './') || str_starts_with($path, '../')) {
                $tryPaths[] = $path;
            } else {
                // Try as-is first, then with ./
                $tryPaths[] = $path;
                $tryPaths[] = './' . $path;
            }

            foreach ($tryPaths as $tryPath) {
                $absolutePath = realpath($basePath . '/' . $tryPath);

                // Skip if: can't resolve, not a file, or already imported (recursion guard)
                if ($absolutePath === false || !file_exists($absolutePath) || !is_readable($absolutePath)) {
                    continue;
                }

                if (in_array($absolutePath, $this->importedFiles)) {
                    return ''; // Already imported, remove the @import
                }

                $this->importedFiles[] = $absolutePath;
                $importedCss = file_get_contents($absolutePath);
                $importBasePath = dirname($absolutePath);

                // Recursively resolve imports in the imported file
                return $this->resolveImports($importedCss, $importBasePath, $depth + 1);
            }

            // Couldn't resolve to a local file (package import or missing file), remove it
            return '';
        }, $css);
    }

    /**
     * Extract top-level --color-* declarations from a block of CSS.
     * Only extracts declarations at the top level (ignores nested blocks like @keyframes).
     * Handles multi-line values and minified CSS.
     */
    protected function extractDeclarations(string $content): array
    {
        $declarations = [];

        // First, remove nested at-rules and their blocks
        $topLevelContent = $this->removeNestedAtRules($content);

        // Now split by semicolons and look for --color-* declarations
        // We need to handle multi-line values, so we'll accumulate until we find a semicolon
        $currentDecl = '';
        $length = strlen($topLevelContent);

        for ($i = 0; $i < $length; $i++) {
            $char = $topLevelContent[$i];
            $currentDecl .= $char;

            if ($char === ';') {
                // End of declaration, try to parse it
                $trimmed = trim($currentDecl, "; \n\r\t");
                $parsed = $this->parseColorDeclaration($trimmed);

                if ($parsed !== null) {
                    $declarations[] = $parsed;
                }

                $currentDecl = '';
            }
        }

        // Handle any remaining declaration without trailing semicolon
        $trimmed = trim($currentDecl, "; \n\r\t");
        $parsed = $this->parseColorDeclaration($trimmed);

        if ($parsed !== null) {
            $declarations[] = $parsed;
        }

        return $declarations;
    }

    /**
     * Parse a single --color-* declaration.
     * Matches: --color-red-500, --color-*, --color-red-*, --color-light-blue-*, etc.
     * Returns ['name' => '...', 'value' => '...'] or null if not a valid color declaration.
     */
    protected function parseColorDeclaration(string $declaration): ?array
    {
        if (empty($declaration)) {
            return null;
        }

        // Pattern matches --color- followed by any combination of letters, digits, hyphens, and asterisks
        // Then validates that it's a sensible color name
        if (!preg_match('/^(--color-[a-z0-9*-]+):\s*(.+)$/is', $declaration, $match)) {
            return null;
        }

        $fullName = $match[1];
        $name = substr($fullName, 8); // Remove '--color-' prefix
        $value = preg_replace('/\s+/', ' ', trim($match[2])); // Normalize whitespace

        // Validate: name should be valid (no empty segments, no trailing/leading hyphens)
        // Valid: 'red', 'red-500', 'red-*', 'light-blue-*', '*'
        // Invalid: '-red', 'red-', '--', 'red--500'
        if ($name !== '*' && !preg_match('/^[a-z0-9]+(-[a-z0-9*]+)*$/', $name)) {
            return null;
        }

        return [
            'name' => $name,
            'value' => $value,
        ];
    }

    /**
     * Remove nested at-rules (like @keyframes, @media, etc.) from CSS content.
     * This keeps only top-level declarations.
     */
    protected function removeNestedAtRules(string $content): string
    {
        $result = '';
        $length = strlen($content);
        $i = 0;

        while ($i < $length) {
            // Check if we're at an @ rule
            if ($content[$i] === '@') {
                // Find the opening brace
                $bracePos = strpos($content, '{', $i);
                if ($bracePos !== false) {
                    // Find matching closing brace
                    $depth = 1;
                    $j = $bracePos + 1;

                    while ($j < $length && $depth > 0) {
                        if ($content[$j] === '{') {
                            $depth++;
                        } elseif ($content[$j] === '}') {
                            $depth--;
                        }
                        $j++;
                    }

                    // Skip the entire @rule block
                    $i = $j;
                    continue;
                }
            }

            // Not an @rule, include this character
            $result .= $content[$i];
            $i++;
        }

        return $result;
    }
}
