<?php

namespace Krakero\TailwindFieldtype\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class TailwindConfigGenerator extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'krakero:tailwind-config';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update the Tailwind Config for your project';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (File::exists(base_path('tailwind.config.js'))) {
            try {
                exec('cd ' . __DIR__ . '/../../.. && npm install');
            } catch (\Exception $e) {
                $this->error('NPM Install failure');

                return self::FAILURE;
            }

            $this->info('Config file found, processing to PHP');
            $path_to_library = __DIR__ . '/../../../node_modules/@vendeka/tailwind-config-php/index.js';
            $path_to_config =  __DIR__ . '/../../../tailwind.config.php';

            if (File::exists($path_to_library)) {
                $cmd = $path_to_library . ' -o ' . $path_to_config;
                exec($cmd);
                $this->info('Config file processed');

                return self::SUCCESS;
            }

            $this->error('Config parser not found');

            return self::FAILURE;
        }

        $this->error('No Tailwind CSS Config File Found!');

        return self::FAILURE;
    }
}