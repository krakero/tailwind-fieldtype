import { describe, it, expect } from 'vitest';
import {
    parseColorValue,
    formatColorValue,
    getColorCssValue,
    getDefaultShade,
} from '../../resources/js/color-value.js';

const colors = {
    red: { 50: '#fef2f2', 500: '#ef4444', 900: '#7f1d1d' },
    'red-dark': { 500: '#991b1b' },
    black: '#000',
    primary: { DEFAULT: '#123456', light: '#234567' },
    gray: { 100: '#f3f4f6', 200: '#e5e7eb', 300: '#d1d5db' },
};

describe('parseColorValue', () => {
    it('returns nulls for empty values', () => {
        expect(parseColorValue('', 'bg', colors)).toEqual({ name: null, shade: null, raw: null });
        expect(parseColorValue(null, 'bg', colors)).toEqual({ name: null, shade: null, raw: null });
    });

    it('strips a simple prefix', () => {
        expect(parseColorValue('bg-red-500', 'bg', colors)).toEqual({ name: 'red', shade: '500', raw: null });
    });

    it('strips multi-segment prefixes', () => {
        expect(parseColorValue('border-t-red-500', 'border-t', colors)).toEqual({ name: 'red', shade: '500', raw: null });
    });

    it('works without a prefix', () => {
        expect(parseColorValue('red-500', '', colors)).toEqual({ name: 'red', shade: '500', raw: null });
    });

    it('recognises keywords with and without prefix', () => {
        for (const k of ['transparent', 'current', 'inherit']) {
            expect(parseColorValue(k, '', colors)).toEqual({ name: k, shade: null, raw: null });
            expect(parseColorValue(`bg-${k}`, 'bg', colors)).toEqual({ name: k, shade: null, raw: null });
        }
    });

    it('matches flat colors and bare names', () => {
        expect(parseColorValue('bg-black', 'bg', colors)).toEqual({ name: 'black', shade: null, raw: null });
        expect(parseColorValue('primary', '', colors)).toEqual({ name: 'primary', shade: null, raw: null });
    });

    it('prefers the longest color name', () => {
        expect(parseColorValue('red-dark-500', '', colors)).toEqual({ name: 'red-dark', shade: '500', raw: null });
        expect(parseColorValue('red-500', '', colors)).toEqual({ name: 'red', shade: '500', raw: null });
    });

    it('matches non-numeric shade keys', () => {
        expect(parseColorValue('primary-light', '', colors)).toEqual({ name: 'primary', shade: 'light', raw: null });
    });

    it('returns unknown values as raw', () => {
        expect(parseColorValue('bg-purple-500', 'bg', colors)).toEqual({ name: null, shade: null, raw: 'purple-500' });
        expect(parseColorValue('red-123', '', colors)).toEqual({ name: null, shade: null, raw: 'red-123' });
    });

    it('does not strip a prefix that does not match', () => {
        expect(parseColorValue('text-red-500', 'bg', colors)).toEqual({ name: null, shade: null, raw: 'text-red-500' });
    });
});

describe('formatColorValue', () => {
    it('returns null without a name', () => {
        expect(formatColorValue(null, '500', 'bg')).toBeNull();
    });

    it('formats name, shade and prefix', () => {
        expect(formatColorValue('red', '500', 'bg')).toBe('bg-red-500');
        expect(formatColorValue('red', '500', 'border-t')).toBe('border-t-red-500');
        expect(formatColorValue('red', '500', '')).toBe('red-500');
    });

    it('omits null and DEFAULT shades', () => {
        expect(formatColorValue('black', null, 'bg')).toBe('bg-black');
        expect(formatColorValue('primary', 'DEFAULT', 'text')).toBe('text-primary');
    });

    it('formats keywords', () => {
        expect(formatColorValue('transparent', null, 'bg')).toBe('bg-transparent');
    });
});

describe('getColorCssValue', () => {
    it('resolves keywords not in colors', () => {
        expect(getColorCssValue('transparent', null, colors)).toBe('transparent');
        expect(getColorCssValue('current', null, colors)).toBe('currentColor');
        expect(getColorCssValue('inherit', null, colors)).toBe('inherit');
    });

    it('returns null for missing or unknown names', () => {
        expect(getColorCssValue(null, null, colors)).toBeNull();
        expect(getColorCssValue('nope', null, colors)).toBeNull();
    });

    it('returns flat color values', () => {
        expect(getColorCssValue('black', null, colors)).toBe('#000');
    });

    it('returns the requested shade', () => {
        expect(getColorCssValue('red', '500', colors)).toBe('#ef4444');
    });

    it('falls back to DEFAULT, then first shade', () => {
        expect(getColorCssValue('primary', '999', colors)).toBe('#123456');
        expect(getColorCssValue('primary', null, colors)).toBe('#123456');
        expect(getColorCssValue('red', '999', colors)).toBe('#fef2f2');
    });

    it('lets a defined color override keyword defaults', () => {
        expect(getColorCssValue('transparent', null, { transparent: 'rgba(0,0,0,0)' })).toBe('rgba(0,0,0,0)');
    });
});

describe('getDefaultShade', () => {
    it('returns null for missing/unknown/flat colors', () => {
        expect(getDefaultShade(null, colors, '500')).toBeNull();
        expect(getDefaultShade('nope', colors, '500')).toBeNull();
        expect(getDefaultShade('black', colors, '500')).toBeNull();
    });

    it('returns null for keywords', () => {
        expect(getDefaultShade('transparent', colors, '500')).toBeNull();
    });

    it('uses the configured shade when present', () => {
        expect(getDefaultShade('red', colors, '500')).toBe('500');
    });

    it('falls back to DEFAULT', () => {
        expect(getDefaultShade('primary', colors, '500')).toBe('DEFAULT');
    });

    it('falls back to the middle numeric shade', () => {
        expect(getDefaultShade('gray', colors, '500')).toBe('200');
    });
});
