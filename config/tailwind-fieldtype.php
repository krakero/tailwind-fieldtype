<?php

return [
    /*
    |--------------------------------------------------------------------------
    | CSS Paths
    |--------------------------------------------------------------------------
    |
    | Paths to CSS files containing Tailwind @theme blocks.
    | Every existing file is parsed in order.
    | Later files override earlier declarations.
    |
    | Set to an empty array [] to use defaults only (no warning if files missing).
    | Non-empty array with no existing files triggers a warning.
    |
    */
    'css_paths' => [
        resource_path('css/site.css'),
        resource_path('css/app.css'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Include Default Palette
    |--------------------------------------------------------------------------
    |
    | Whether to include the bundled Tailwind v4 default color palette.
    | When true, default colors are loaded first, then overridden by your CSS.
    |
    */
    'include_defaults' => true,

    /*
    |--------------------------------------------------------------------------
    | Excluded Colors
    |--------------------------------------------------------------------------
    |
    | Color names to exclude globally from the picker.
    | Useful for hiding colors you don't want available.
    |
    */
    'exclude' => [
        // 'slate',
        // 'gray',
    ],
];
