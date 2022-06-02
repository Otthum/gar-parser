<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use SimpleXMLElement;
use ZipArchive;

abstract class AbstractGarParserCommand extends Command
{
    /**
     * Нужный файл лежит в папке региона или отдельно?
     */
    protected $isSpecificForRegion = true;

    /**
     * Массив спаршенных данных
     */
    protected $toCommit = [];

    /**
     * Через какое кол-во строк запускать обновление базы
     */
    protected $commitCount = 2000;

    /**
     * Сколько всего элементов спарсили
     */
    protected $totalItems = 0;
    protected $currentFileElems = 0;

    
    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        /**
         * Спасаем память
         * https://github.com/laravel/framework/issues/30012#issuecomment-635943892
         */
        DB::connection()->unsetEventDispatcher();

        echo "\n";

        $arc = new ZipArchive();
        $arc->open(storage_path('app/gar/' . $this->argument('path')));

        for ($i = 0; $i < $arc->count(); $i++) {
            $file = $arc->getStream($arc->getNameIndex($i));
            $fileName = stream_get_meta_data($file)['uri'];

            preg_match($this->getFileNamePattern(), $fileName, $matches);

            if (!isset($matches[0])) {
                continue;
            }

            $this->currentFileElems = 0;
            echo sprintf("Парсим файл %s\n", $fileName);

            /* $xml = new SimpleXMLElement($arc->getFromIndex($i));

            echo sprintf("Найдено %d записей\n", $xml->count()); */

            $parser = xml_parser_create();
            xml_set_element_handler(
                $parser,
                function ($parser, $data, $attribs) {       
                    if ($attribs == []) {
                        return false;
                    }

                    $this->toCommit[] = $this->parseItem($attribs);
                    $this->currentFileElems++;

                    if (count($this->toCommit) % $this->commitCount === 0) {
                        $this->commit();
                    }

                    return true;
                },
                false
            );

            while ($data = fread($file, 32768)) {
                xml_parse($parser, $data);
            }
            echo sprintf("Парсинг файла завершён, собрано %d строк\n", $this->currentFileElems);
        }

        $this->commit();

        echo sprintf("Команда завершена, спаршено %d строк", $this->totalItems);

        return true;

    }



    /**
     * Записывает изменения в базу одним запросом
     */
    protected function commit()
    {
        if ( !property_exists($this, 'parsingClass') ) {
            throw new \Exception(sprintf("Класс %s не имеет параметра `parsingClass`. Не удалось сохранить изменения.", static::class));
        }

        echo sprintf("Коммитим %s строк\n", count($this->toCommit));
        $this->parsingClass::upsert($this->toCommit, ['gar_id']);
        $this->totalItems += count($this->toCommit);
        
        /**
        * Чистим массив элементов чтобы не коммитить их заного
        * и не переполнять память
        */
        $this->toCommit = [];
    }

    /**
     * Возвращает регулярку для поиска необходимого файла в архиве.
     */
    protected function getFileNamePattern(): string
    {
        if ( !$this->isSpecificForRegion ) {
            return sprintf('~%s~i', $this->fileNamePattern);
        }

        if ($this->argument('region') == 'all') {
            $regionPart = '\d{2}/';
        } else {
            $regionPart = $this->argument('region') . '/';
        }

        return sprintf('~%s%s~i', $regionPart, $this->fileNamePattern);
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
