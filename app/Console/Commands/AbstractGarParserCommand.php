<?php

namespace App\Console\Commands;

use DirectoryIterator;
use Illuminate\Console\Command;
use SimpleXMLElement;

abstract class AbstractGarParserCommand extends Command
{
    /**
     * Формат даты в файлах выгрузки
     */
    protected $garDateFormat = 'Y.m.d';

    /**
     * Нужный файл лежит в папке региона или отдельно?
     */
    protected $isSpecificForRegion = true;

    /**
     * Массив спаршенных данных
     */
    protected $parsedItems = [];

    /**
     * Через какое кол-во строк запускать обновление базы
     */
    protected $commitCount = 2000;

    
    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        echo "\n";

        $dir = $this->getDirectoryIteratorForDate();

        if (! $this->isSpecificForRegion) {
            $this->parseDirectory($dir);
            return true;
        }

        if ($this->argument('region') === 'all') {
            foreach ($dir as $item) {
                if ($item->isDot() || $item->isFile()) {
                    continue;
                }

                $this->parseDirectory(new DirectoryIterator($item->getRealPath()));
            }

            return true;
        }

        try {
            $this->parseDirectory(new DirectoryIterator($dir->getRealPath() . '/' . $this->argument('region')));
        } catch (\UnexpectedValueException $e) {
            echo sprintf(
                "Ошибка при открытии папки для запрошенной даты (%s) и региона (%s)\n%s\n",
                $this->argument('date'),
                $this->argument('region'),
                $e->getMessage());
            
            return false;
        }
        

        return true;

    }

    /**
     * Возвращает папку для запрошенной даты
     * 
     * @return DirectoryIterator|false Папка с выгрузкой по запрошенной дате или `false`, если не найдено
     */
    protected function getDirectoryIteratorForDate() : DirectoryIterator|false
    {
        $date = $this->argument('date');

        $date = new \DateTime($date);
        $dirPath = str_replace('{date}', $date->format($this->garDateFormat), storage_path('app/gar/{date}'));

        try {
            $iterator = new DirectoryIterator($dirPath);
        } catch (\UnexpectedValueException $e) {
            echo sprintf(
                "Ошибка при открытии папки для запрошенной даты (%s)\n%s\n",
                $this->argument('date'),
                $e->getMessage());
            return false;
        }

        return $iterator;
    }

    /**
     * Ищет в папке региона соответствующий файл и запускает парсер элемента
     * на каждую строку в нём
     * 
     * @param DirectoryIterator $dir папка с выгрузкой по региону
     * @return void
     */
    protected function parseDirectory(DirectoryIterator $dir): void
    {
        /**
         * @var SplFileInfo $file
         */
        foreach ($dir as $file) {
            preg_match($this->fileNamePattern, $file->getFileName(), $matches);

            if (!isset($matches[0])) {
                continue;
            }

            echo sprintf("Парсим файл %s\n", $file->getPathname());

            $xml = simplexml_load_file($file->getPathname());

            echo sprintf("Найдено %d записей\n", $xml->count());

            foreach ($xml->children() as $item) {
                $this->items[] = $this->parseItem($item);

                if (count($this->items) % $this->commitCount === 0) {
                    $this->commit();

                    /**
                     * Чистим массив элементов т.к. memory limit overflow
                     */
                    $this->items = [];
                }
                
            }
        }


        echo "\n";

    }

    /**
     * Записывает изменения в базу одним запросом
     */
    protected function commit()
    {
        if (property_exists($this, 'parsingClass')) {
            $this->parsingClass::upsert($this->parsedItems, ['gar_id']);
        } else {
            throw new \Exception(sprintf("Класс %s не имеет параметра `parsingClass`. Не удалось сохранить изменения.", static::class));
        }
    }

    /**
     * Парсит строку в файле с выгрузкой
     * 
     * @param SimpleXMLElement $item строка с данными в виде xml элемента
     */
    protected function parseItem(SimpleXMLElement $item)
    {
        //
    }
}
