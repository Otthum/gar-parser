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
     * Массив данных для обновления/создания
     */
    protected $toUpdate = [];

    /**
     * Через какое кол-во строк запускать обновление базы
     */
    protected $commitCount = 2000;

    /**
     * Сколько всего элементов спарсили
     */
    protected $totalUpdatedItems = 0;
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

            $parser = xml_parser_create();
            xml_set_element_handler(
                $parser,
                function ($parser, $data, $attribs) {       
                    if ($attribs == []) {
                        return false;
                    }

                    $parsed = $this->parseItem($attribs);

                    if (isset($parsed['is_actual']) && !$parsed['is_actual']) {
                        return false;
                    }
                    
                    $this->toUpdate[] = $parsed['data'];
                    $this->currentFileElems++;

                    if (count($this->toUpdate) % $this->commitCount === 0) {
                        $this->commit();
                    }

                    return true;
                },
                false
            );

            while ($data = fread($file, 32768)) {
                xml_parse($parser, $data);
            }
            echo sprintf("\nПарсинг файла завершён, собрано %d строк\n", $this->currentFileElems);
        }

        $this->commit();

        echo sprintf(
            "\nКоманда завершена, обновлено %d строк\n",
            $this->totalUpdatedItems,
        );

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

        echo sprintf(
            "\rКоммитим %d строк (всего с этого файла закомиченно %d строк)",
            count($this->toUpdate),
            $this->currentFileElems
        );
        $this->parsingClass::upsert($this->toUpdate, ['gar_id']);
        $this->totalUpdatedItems += count($this->toUpdate);
        
        /**
        * Чистим массив элементов чтобы не коммитить их заного
        * и не переполнять память
        */
        $this->toUpdate = [];
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
     * @param mixed $item строка с данными в виде массива
     */
    protected function parseItem($item)
    {
        //
    }
}
