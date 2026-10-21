<?php

namespace Krakero\TailwindFieldtype\Console\Commands;

use Illuminate\Console\Command;
use Krakero\TailwindFieldtype\Colors\ColorRepository;

class ShowColorsCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'tailwind-fieldtype:colors';

    /**
     * The console command description.
     */
    protected $description = 'Display detected Tailwind colors from configured sources';

    /**
     * Execute the console command.
     */
    public function handle(ColorRepository $repository): int
    {
        $colors = $repository->all();
        $sources = $repository->sources();
        $warnings = $repository->warnings();

        // Display sources
        $this->info('Color Sources:');
        if (empty($sources)) {
            $this->warn('  No sources found');
        } else {
            foreach ($sources as $source) {
                $this->line('  - ' . str_replace(base_path() . '/', '', $source));
            }
        }
        $this->newLine();

        // Display warnings
        if (!empty($warnings)) {
            $this->warn('Warnings:');
            foreach ($warnings as $warning) {
                $this->line('  - ' . $warning);
            }
            $this->newLine();
        }

        // Display error if no colors or only keywords
        $keywords = ['transparent', 'current', 'inherit'];
        $nonKeywordColors = array_diff(array_keys($colors), $keywords);

        if (empty($colors) || empty($nonKeywordColors)) {
            $this->error('No colors detected!');
            $this->warn('Check your configuration in config/tailwind-fieldtype.php');
            return self::FAILURE;
        }

        // Build table data
        $rows = [];
        foreach ($colors as $name => $value) {
            if (is_string($value)) {
                // Flat color
                $rows[] = [$name, '-', $value];
            } elseif (is_array($value)) {
                // Color with shades
                foreach ($value as $shade => $hex) {
                    $rows[] = [$name, $shade, $hex];
                }
            }
        }

        // Sort by color name, then shade
        usort($rows, function ($a, $b) {
            $cmp = strcmp($a[0], $b[0]);
            if ($cmp !== 0) {
                return $cmp;
            }

            // Sort shades: DEFAULT first, then numeric ascending, then alphabetic
            $shadeA = $a[1];
            $shadeB = $b[1];

            if ($shadeA === 'DEFAULT') return -1;
            if ($shadeB === 'DEFAULT') return 1;

            if (is_numeric($shadeA) && is_numeric($shadeB)) {
                return (int)$shadeA - (int)$shadeB;
            }

            return strcmp($shadeA, $shadeB);
        });

        // Display table
        $this->info('Detected Colors:');
        $this->table(['Color', 'Shade', 'Value'], $rows);

        $this->newLine();
        $this->info('Total: ' . count($colors) . ' colors');

        return self::SUCCESS;
    }
}
