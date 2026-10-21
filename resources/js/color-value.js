/**
 * Parse and format Tailwind color class values.
 * Handles prefix stripping, longest-name matching, and output formatting per contract.
 */

/**
 * Parse a color class value into its components.
 *
 * @param {string} value - The input value (e.g., "bg-red-500", "primary", "transparent")
 * @param {string} prefix - The class prefix (e.g., "bg", "border-t")
 * @param {Object} colors - The colors object from meta.colors
 * @returns {{name: string|null, shade: string|null, raw: string|null}}
 */
export function parseColorValue(value, prefix, colors) {
    if (!value) {
        return { name: null, shade: null, raw: null };
    }

    // Strip prefix if present
    let remaining = value;
    if (prefix) {
        const prefixWithDash = prefix + '-';
        if (value.startsWith(prefixWithDash)) {
            remaining = value.substring(prefixWithDash.length);
        }
    }

    // Special keywords are always available
    const keywords = ['transparent', 'current', 'inherit'];
    if (keywords.includes(remaining)) {
        return { name: remaining, shade: null, raw: null };
    }

    // Try to match against known color names (longest-first)
    const colorNames = Object.keys(colors).sort((a, b) => b.length - a.length);

    for (const colorName of colorNames) {
        // Exact match (flat color or DEFAULT shade)
        if (remaining === colorName) {
            return { name: colorName, shade: null, raw: null };
        }

        // Match with shade (name-shadeKey)
        if (remaining.startsWith(colorName + '-')) {
            const shadeKey = remaining.substring(colorName.length + 1);
            const colorValue = colors[colorName];

            // Check if this shade exists for this color
            if (typeof colorValue === 'object' && colorValue[shadeKey]) {
                return { name: colorName, shade: shadeKey, raw: null };
            }
        }
    }

    // Unknown value - return as raw
    return { name: null, shade: null, raw: remaining };
}

/**
 * Format a color selection into a class value.
 *
 * @param {string|null} name - The color name
 * @param {string|null} shade - The shade key (or null for flat/DEFAULT)
 * @param {string} prefix - The class prefix
 * @returns {string|null}
 */
export function formatColorValue(name, shade, prefix) {
    if (!name) {
        return null;
    }

    // Build the class name
    let className = name;

    // Add shade if not DEFAULT
    if (shade && shade !== 'DEFAULT') {
        className += '-' + shade;
    }

    // Add prefix if present
    if (prefix) {
        className = prefix + '-' + className;
    }

    return className;
}

/**
 * Get the CSS value for a color.
 *
 * @param {string} name - The color name
 * @param {string|null} shade - The shade key
 * @param {Object} colors - The colors object from meta.colors
 * @returns {string|null}
 */
export function getColorCssValue(name, shade, colors) {
    if (!name || !colors[name]) {
        if (name === 'transparent') return 'transparent';
        if (name === 'current') return 'currentColor';
        if (name === 'inherit') return 'inherit';
        return null;
    }

    const colorValue = colors[name];

    // Flat color (string value)
    if (typeof colorValue === 'string') {
        return colorValue;
    }

    // Color with shades (object)
    if (typeof colorValue === 'object') {
        // Try the specified shade
        if (shade && colorValue[shade]) {
            return colorValue[shade];
        }
        // Fall back to DEFAULT
        if (colorValue.DEFAULT) {
            return colorValue.DEFAULT;
        }
        // Fall back to first shade
        const shades = Object.keys(colorValue);
        if (shades.length > 0) {
            return colorValue[shades[0]];
        }
    }

    return null;
}

/**
 * Get the default shade for a color in simple mode.
 *
 * @param {string} name - The color name
 * @param {Object} colors - The colors object from meta.colors
 * @param {string} defaultShade - The configured default shade (e.g., "500")
 * @returns {string|null}
 */
export function getDefaultShade(name, colors, defaultShade) {
    if (!name || !colors[name]) {
        return null;
    }

    const colorValue = colors[name];

    // Flat color has no shade
    if (typeof colorValue === 'string') {
        return null;
    }

    // Keywords have no shade
    if (['transparent', 'current', 'inherit'].includes(name)) {
        return null;
    }

    // Color with shades
    if (typeof colorValue === 'object') {
        // Use configured default if it exists
        if (colorValue[defaultShade]) {
            return defaultShade;
        }
        // Fall back to DEFAULT
        if (colorValue.DEFAULT) {
            return 'DEFAULT';
        }
        // Fall back to middle shade (approximate)
        const shades = Object.keys(colorValue).filter(k => !isNaN(k)).sort((a, b) => a - b);
        if (shades.length > 0) {
            const middleIndex = Math.floor(shades.length / 2);
            return shades[middleIndex];
        }
    }

    return null;
}
