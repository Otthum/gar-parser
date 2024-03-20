<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class Parse extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gar:parse
        {--path= : Путь до архива с выгрузкой внутри папки "storage/app/gar"}
        {--region= : Регион для парсинга. Базово парсит все}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Запускает процесс парсинга для всех видов данных в переданном архиве с учётом региона';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $path = $this->option('path');

        if ($path == null) {
            throw new \Exception("No source path specified for parsing!");
        }

        $parsingOptions = [
            'path' => $path,
        ];

        $this->call('gar:parse:param-types', $parsingOptions);
        $this->call('gar:parse:add-house-types', $parsingOptions);
        $this->call('gar:parse:addr-obj-types', $parsingOptions);
        $this->call('gar:parse:apartments-types', $parsingOptions);
        $this->call('gar:parse:object-levels', $parsingOptions);
        $this->call('gar:parse:operation-types', $parsingOptions);
        $this->call('gar:parse:room-types', $parsingOptions);
        $this->call('gar:parse:doc-kinds', $parsingOptions);
        $this->call('gar:parse:doc-types', $parsingOptions);
        $this->call('gar:parse:house-types', $parsingOptions);

        if ($this->option('region')) {
            $parsingOptions['region'] = $this->option('region');
        }
        
        $this->call('gar:parse:houses', $parsingOptions);
        $this->call('gar:parse:house-params', $parsingOptions);
        $this->call('gar:parse:add-objects', $parsingOptions);
        $this->call('gar:parse:apartments', $parsingOptions);
        $this->call('gar:parse:apartments-params', $parsingOptions);
        
        $this->call('gar:parse:mun-hierarchy', $parsingOptions);
    }
}
