# Statamic Tailwind Fieldtype

Tailwind Fieldtype is a Statamic addon that provides a Tailwind color picker.

> **NOTE:** This fieldtype does not ensure the CSS classes are available in your frontend.

## Features
- Choose "advanced" mode for all shades
- Choose "simple" mode for just a single color selection
- Add a prefix like `bg` or `text`, so the class is ready to go (e.g. `bg-red-500`)

## How to Install

You can search for this addon in the `Tools > Addons` section of the Statamic control panel and click **install**, or run the following command from your project root:

``` bash
composer require krakero/tailwind-fieldtype
```

## How to Use

Once you install the addon you will need to run `krakero:tailwind-config`. This command will look for the `tailwind.config.js` file located in your project root and generate a version for the fieldtype.

> **NOTE:** If you make changes to your config file you will need to run this command again.

