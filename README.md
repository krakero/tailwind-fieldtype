# Statamic Tailwind Fieldtype

> **3.0 is in beta.** It adds Statamic 6 and Tailwind CSS 4 support.
> Using Statamic 4 or 5? Install the stable 2.x release (see [Installation](#installation)).

A Tailwind CSS color picker fieldtype for Statamic 6. Select colors from your Tailwind theme and save them as CSS class names ready to use in your templates.

## Requirements

- **Statamic** 6.0 or higher
- **Tailwind CSS** 4.x
- **PHP** 8.3 or higher

> **Note:** For Statamic 4/5 with Tailwind CSS 3, use [version 2.x](https://github.com/krakero/tailwind-fieldtype/tree/2.x) instead.

## Installation

**Statamic 6 (3.0 beta):**

```bash
composer require krakero/tailwind-fieldtype:^3.0@beta
```

**Statamic 4/5 (stable 2.x):**

```bash
composer require krakero/tailwind-fieldtype:^2.1
```

Until 3.0.0 is released, install on Statamic 6 with Composer rather than the Control Panel's **Tools → Addons** page.

## Color Discovery

The fieldtype automatically discovers colors from two sources:

1. **Tailwind v4 default palette** - The complete set of Tailwind's built-in colors (red, blue, green, etc.)
2. **Custom colors from your CSS** - Any `--color-*` declarations in `@theme` blocks

### How @theme Parsing Works

The fieldtype parses `@theme` blocks from your CSS files to discover custom colors:

```css
@import "tailwindcss";

@theme {
  --color-primary: #3490dc;
  --color-secondary: #ffed4e;
  --color-danger-500: oklch(0.55 0.22 25);
  --color-brand-light: #e3f2fd;
  --color-brand-dark: #1565c0;
}
```

**Color grouping:**
- `--color-name-number` creates a color group with shades (e.g., `danger-500`, `danger-600`)
- `--color-name` creates a flat color (e.g., `primary`, `secondary`)
- If both exist, the flat value becomes the `DEFAULT` shade

**Resetting colors:**
```css
@theme {
  --color-*: initial;           /* Clears all colors (including defaults) */
  --color-red-*: initial;       /* Clears only the 'red' color group */
  --color-red-500: oklch(...);  /* Re-add specific shades */
}
```

**Local imports:**
The parser follows relative `@import` statements:
```css
@import "tailwindcss";
@import "./colors.css";  /* Local file imports are followed */
```

**Supported formats:**
All CSS color formats are supported, including OKLCH, hex, rgb(), hsl(), and color keywords.

## Configuration

By default, the fieldtype looks for colors in these files (if they exist):
- `resources/css/site.css`
- `resources/css/app.css`

### Publishing the Config

To customize settings, publish the configuration file:

```bash
php artisan vendor:publish --tag=tailwind-fieldtype-config
```

This creates `config/tailwind-fieldtype.php`:

```php
return [
    // CSS files to parse for @theme blocks (in order)
    'css_paths' => [
        resource_path('css/site.css'),
        resource_path('css/app.css'),
    ],

    // Include Tailwind's default color palette
    'include_defaults' => true,

    // Hide specific colors globally
    'exclude' => [
        // 'slate',
        // 'gray',
    ],
];
```

### Configuration Options

- **`css_paths`** - Array of absolute paths to CSS files. Only existing files are parsed, in the order given. Set to an empty array `[]` to use defaults only (no warning if files missing). A non-empty array with no existing files triggers a warning in the control panel.
- **`include_defaults`** - Boolean. When `true`, includes Tailwind v4's bundled default palette.
- **`exclude`** - Array of color names to hide from the picker globally.

## Fieldtype Options

When adding the Tailwind Picker field to a blueprint, you can configure:

### Mode

**Advanced** (default) - Shows all color shades:
```yaml
type: tailwind_picker
mode: advanced
```

**Simple** - Shows only one shade per color:
```yaml
type: tailwind_picker
mode: simple
default_color: '500'  # Which shade to use (default: '500')
```

### Available Colors

Limit which colors appear in the picker:
```yaml
type: tailwind_picker
available_colors:
  - red
  - blue
  - primary
```

### Class Prefix

Add a Tailwind utility prefix to the saved class:
```yaml
type: tailwind_picker
class_prefix: bg        # Saves as: bg-red-500
```

```yaml
type: tailwind_picker
class_prefix: border-t  # Multi-segment prefixes work: border-t-red-500
```

## Stored Value Format

The fieldtype saves a **plain CSS class string** (without the leading dot):

```
red-500
bg-red-500
text-primary
border-t-blue-600
transparent
```

The special keywords `transparent`, `current`, and `inherit` are always available in the picker. `transparent` is shown as a checkerboard swatch with a red diagonal slash so it is distinguishable from white; `current` renders as the current text color (`currentColor`), and `inherit` as an inherited value.

This format means **no content migration** is needed when upgrading from version 2.x.

## Template Usage

The saved value is a string, so you can use it directly in your templates:

### Antlers

```antlers
<div class="{{ background_color }}">
  <!-- Outputs: <div class="bg-red-500"> -->
</div>

<p class="text-{{ text_color }}">
  <!-- Outputs: <p class="text-blue-600"> -->
</p>
```

### Blade

```blade
<div class="{{ $background_color }}">
  <!-- Outputs: <div class="bg-red-500"> -->
</div>

<p class="text-{{ $text_color }}">
  <!-- Outputs: <p class="text-blue-600"> -->
</p>
```

## ⚠️ Important: Safeguarding Dynamic Classes

**The fieldtype stores class names in your content files.** Tailwind 4 needs to see these classes during the build process, or they won't be included in your CSS.

### Recommended: Use @source Directive

Add your content directory to Tailwind's sources in your main CSS file:

```css
@import "tailwindcss";

@source "../../content";  /* Adjust path to your Statamic content directory */

@theme {
  --color-primary: #3490dc;
}
```

### Alternative: Inline Safelist

If you have a limited set of possible classes, use an inline safelist:

```css
@import "tailwindcss";

@source inline("
  bg-red-500 bg-blue-600 bg-primary
  text-red-500 text-blue-600 text-primary
  border-red-500 border-blue-600 border-primary
");
```

### Why This Matters

Without this step, Tailwind won't find the class names in your content files and will exclude them from the final CSS, causing your styled elements to appear unstyled in production.

## Debug Command

List all colors detected by the fieldtype:

```bash
php artisan tailwind-fieldtype:colors
```

This command displays:
- Color sources (parsed CSS files)
- Warnings about missing files
- Table of all detected colors with their shades and values

The command exits with a non-zero status code if no colors are found (besides the special keywords `transparent`, `current`, and `inherit`), making it useful for CI validation.

## Features

- ✅ **Familiar UI** - A single swatch field (hover for the value) opens a popover with a labelled row of swatches per color and a Clear option
- ✅ **Advanced mode** - All color shades in a visual picker
- ✅ **Simple mode** - Single shade selection (perfect for brand colors)
- ✅ **Class prefixes** - `bg`, `text`, `border`, `border-t`, etc.
- ✅ **Color filtering** - Show only specific colors per field
- ✅ **Dark mode** - Swatches adapt to control panel theme
- ✅ **OKLCH support** - Modern color spaces render correctly
- ✅ **Special keywords** - `transparent`, `current`, `inherit`
- ✅ **Custom colors** - Any `--color-*` in your `@theme` blocks
- ✅ **No Node.js required** - Pure PHP parsing

## Example Blueprint

```yaml
fields:
  background_color:
    type: tailwind_picker
    display: Background Color
    mode: advanced
    class_prefix: bg
    available_colors:
      - slate
      - red
      - blue
      - primary
    instructions: Choose a background color for this section
  
  accent_color:
    type: tailwind_picker
    display: Accent Color
    mode: simple
    default_color: '500'
    instructions: Choose an accent color (will use the 500 shade)
```

## Development

The picker's control panel styles are built with Tailwind CSS 4 via `@statamic/cms`.

### Setup

```bash
composer install
npm install
```

### Building Assets

```bash
# Production build
npm run build

# Development (watch mode)
npm run dev
```

The `dev` script runs `vite build --watch` for automatic rebuilds during development.

### Running Tests

```bash
vendor/bin/pest
```

## Upgrading

See [UPGRADE.md](UPGRADE.md) for migration instructions from version 2.x.

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for release history.

## License

This package is open-source software licensed under the [MIT license](LICENSE).
