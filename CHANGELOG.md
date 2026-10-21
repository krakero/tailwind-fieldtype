# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [3.0.0] - Unreleased

### Added
- Tailwind CSS 4 support with `@theme` directive parsing
- OKLCH color space support  
- Configurable CSS file paths via published config
- Support for `--color-*: initial` to reset color groups
- `tailwind-fieldtype:colors` debug command to list detected colors
- Bundled Tailwind v4 default color palette
- Local `@import` following in CSS theme files
- PHP-only CSS parsing (no Node.js dependency)

### Changed
- **BREAKING:** Requires Statamic 6+ (Vue 3 control panel)
- **BREAKING:** Requires Tailwind CSS 4+ (Tailwind 3 no longer supported)
- **BREAKING:** Requires PHP 8.3+
- **BREAKING:** Removed `krakero:tailwind-config` Artisan command
- **BREAKING:** Node.js and npm no longer required
- Rewrote fieldtype component for Vue 3 with Composition API
- Colors are now discovered from `@theme` blocks in CSS files instead of `tailwind.config.js`
- Updated to use Statamic's native UI components (`ui-popover`)
- Swatches now render CSS color values directly (supports OKLCH)
- Picker UI keeps the v2 layout in Statamic 6: a single swatch trigger (tooltip shows the value, X icon when empty) opening a popover with right-aligned color names beside one row of rounded swatches per color, and Clear at the bottom

### Fixed
- Multi-segment class prefix parsing (e.g., `border-t-red-500`)
- `default_color` not being applied correctly in simple mode
- Flat color hex value lookup
- `transparent` keyword not working in advanced mode
- Dark mode color swatch rendering
- `transparent` now shows a checkerboard swatch with a red diagonal slash instead of a plain white box (#4)

### Removed
- **BREAKING:** `tailwind.config.php` generation and usage
- **BREAKING:** `@vendeka/tailwind-config-php` dependency
- **BREAKING:** `inputmask` dependency
- Statamic 4 and 5 support (use version 2.x instead)

### Security
- Removed shell command execution (`exec()`) in favor of pure PHP parsing

## [2.0.3] - 2024-02-15

### Fixed
- Value parsing improvements

## [2.0.2] - 2024-01-20

### Added
- Unique reference for popper component

## [2.0.1] - 2024-01-15

### Changed
- Switched to popover component

## [2.0.0] - 2024-01-10

### Added
- Statamic V5 support

## [1.0.2] - 2023-06-15

### Fixed
- Configuration path handling

## [1.0.1] - 2023-06-10

### Changed
- Improved build process
- Updated README

## [1.0.0] - 2023-06-01

### Added
- Advanced mode (all color shades)
- Simple mode (single shade selection)
- Class prefix support (e.g., `bg`, `text`)
- Available colors filtering
- Tailwind config generator command

## [0.0.3] - 2023-05-20

### Fixed
- Initial bug fixes and improvements

## [0.0.2] - 2023-05-15

### Changed
- Work in progress improvements

## [0.0.1] - 2023-05-10

### Added
- Initial release
- Basic Tailwind color picker functionality
