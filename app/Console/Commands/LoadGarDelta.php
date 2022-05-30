<?php

namespace App\Console\Commands;

use App\Services\GarParserService;
use Illuminate\Console\Command;
use ZipArchive;

class LoadGarDelta extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gar:delta {date=latest : Дата выгрузки. Базово ищет последнюю}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Скачивает изменения ГАРа за указанную дату';

    /**
     * Шаблон урла для получения файла
     */
    protected $urlTemplate = 'https://fias-file.nalog.ru/downloads/{date}/gar_delta_xml.zip';

    /**
     * Формат даты для урла
     */
    protected $garDateFormat = 'Y.m.d';

    /**
     * Время ожидания между запросами при поиске последней выгрузки
     */
    protected $timeout = 3;


    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $input = $this->argument('date');
        $local = false;

        if ($input == 'latest') {
            /**
             * Если требуется скачать последнюю выгрузку
             * будем искать её уменьшая текущую дату на 1 день
             * 
             * Каждая неудачная попытка загрузки будет завершаться паузой
             * в $this->timeout секунд
             */
            $date = new \DateTime();
            while (!$local) {
                $formatedDate = $date->format($this->garDateFormat);
                $local = $this->load($formatedDate);

                if (!$local) {
                    $date->sub(new \DateInterval('P1D'));
                    echo sprintf("Таймаут...\n\n");
                    sleep($this->timeout);
                }
            }
        } else {
            $date = new \DateTime($input);
            $formatedDate = $date->format($this->garDateFormat);
            $local = $this->load($formatedDate);
        }

        $this->call('gar:parse:param-types', ['date' => $date->format('c')]);
        $this->call('gar:parse:house-params', ['date' => $date->format('c')]);
        $this->call('gar:parse:add-house-types', ['date' => $date->format('c')]);
        $this->call('gar:parse:addr-obj-types', ['date' => $date->format('c')]);
    }

    /**
     * Пытается скачать выгрузку за переданную дату
     * 
     * @param string $formatedDate  дата выгрузки. Должна соответствовать дате в урле fias.nalog.ru
     * @return string|false         путь скачанного файла или `false` если выгрузки за переданную дату нет
     */
    protected function load(string $formatedDate)
    {
        $url = str_replace('{date}', $formatedDate, $this->urlTemplate);
        
        echo sprintf("Чтение выгрузки за %s.\n", $formatedDate);

        try {
            $remote = fopen($url, 'r');
        } catch (\ErrorException $e) {
            echo sprintf("Выгрузка за %s не найдена.\n", $formatedDate);
            return false;
        }

        $local = $this->getFilesDirectoryPath($formatedDate) . "/tmp.zip";

        file_put_contents($local, $remote);

        $zip = new ZipArchive();
        $zip->open($local);

        $zip->extractTo($this->getFilesDirectoryPath($formatedDate));
        $zip->close();
        unlink($local);
        
        return $local;
    }

    /**
     * Возвращает путь до папки для запрошенной даты
     * 
     * @param string $date Дата выгрузки
     * @return string      Путь до папки
     */
    protected function getFilesDirectoryPath(string $date): string
    {
        $path = str_replace('{date}', $date, storage_path('app/gar/{date}'));

        if (!is_dir($path)) {
            mkdir($path);
        }

        return $path;
    }
}
