# Upgrade Guide

## Upgrading from 2.x to 3.0

### Requirements
Version 3.0 requires **Statamic 6**, **Tailwind CSS 4**, and **PHP 8.3+**.

If you're using Statamic 4 or 5 with Tailwind CSS 3, stay on version 2.x:
```bash
composer require krakero/tailwind-fieldtype:^2.0
```

### Step 1: Update the Package
```bash
composer require krakero/tailwind-fieldtype:^3.0
```

### Step 2: Remove Old Files
Delete the generated `tailwind.config.php` file from your project root:
```bash
rm tailwind.config.php
```

The old `krakero:tailwind-config` Artisan command has been removed.

### Step 3: Migrate Colors to @theme
Tailwind 4 uses CSS-based configuration with `@theme` blocks instead of JavaScript config files. Move your custom colors from `tailwind.config.js` to your CSS file:

**Before (Tailwind 3 - tailwind.config.js):**
```js
module.exports = {
  theme: {
    extend: {
      colors: {
        primary: '#3490dc',
        secondary: '#ffed4e',
      }
    }
  }
}
```

**After (Tailwind 4 - e.g., resources/css/site.css):**
```css
@import "tailwindcss";

@theme {
  --color-primary: #3490dc;
  --color-secondary: #ffed4e;
}
```

### Step 4: Configure CSS File Paths (Optional)
By default, the fieldtype looks for `resources/css/site.css` and `resources/css/app.css`. To customize this, publish the config file:

```bash
php artisan vendor:publish --tag=tailwind-fieldtype-config
```

Edit `config/tailwind-fieldtype.php` to set your CSS file paths:
```php
return [
    'css_paths' => [
        resource_path('css/my-custom.css'),
    ],
    'include_defaults' => true,  // Include Tailwind's default palette
    'exclude' => [],             // Hide specific colors
];
```

### Step 5: Ensure Classes are Safeguarded
The fieldtype saves class names (like `bg-red-500`) directly in your content. Tailwind 4 must be able to see these classes to include them in your build.

Add your content directory to Tailwind's sources in your main CSS file:
```css
@import "tailwindcss";

@source "../../content";  /* Adjust path to your content directory */
```

Or use an inline safelist for specific patterns:
```css
@source inline("bg-red-500 text-blue-600 border-primary");
```

### What Stays the Same
- **Stored field values are unchanged** - no content migration needed
- **Field configuration options** remain the same (mode, available_colors, class_prefix, default_color)
- **Template usage** in Antlers and Blade is identical

### Breaking Changes
- Tailwind CSS 3 is no longer supported
- Statamic 4 and 5 are no longer supported  
- The `krakero:tailwind-config` Artisan command has been removed
- Node.js and npm are no longer required
- The `tailwind.config.php` file is no longer used or generated

### New Features
- Full Tailwind CSS 4 support with `@theme` directive
- OKLCH color space support
- PHP-only parsing (no shell execution)
- `--color-*: initial` support for resetting color groups
- Optional `tailwind-fieldtype:colors` debug command to list detected colors
