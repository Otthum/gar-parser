<?php

namespace App\Console\Commands;

use App\Services\GarParserService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use ZipArchive;

class LoadGarDump extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gar:load
        {date=latest : Дата выгрузки. Базово ищет последнюю}
        {--full : Загрузить полный архив или дельту}
        {--parse : Начать парсинг по завершению загрузки}
        {--region= : Регион, данные по которому будут разобраны. Используется только если указана опция --parse. Базово парсит все регионы}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Скачивает изменения ГАРа за указанную дату';

    /**
     * Шаблон урла для получения файла
     */
    protected $urlTemplateDelta = 'https://fias-file.nalog.ru/downloads/{date}/gar_delta_xml.zip';

    /**
     * Шаблон урла для получения файла
     */
    protected $urlTemplateFull = 'https://fias-file.nalog.ru/downloads/{date}/gar_xml.zip';

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


        if ($this->option('parse') == false) {
            return;
        }

        $start = new \DateTime();
        
        Artisan::call('gar:parse', [
            'path' => $local,
            'region' => $this->option('region')
        ]);

        $this->call('gar:address-str', ['date' => $start->format('c')]);
    }

    /**
     * Пытается скачать выгрузку за переданную дату
     * 
     * @param string $formatedDate  дата выгрузки. Должна соответствовать дате в урле fias.nalog.ru
     * @return string|false         путь скачанного файла или `false` если выгрузки за переданную дату нет
     */
    protected function load(string $formatedDate)
    {
        $urlTemplate = $this->urlTemplateDelta;
        $archiveName = 'arch.zip';
        if ($this->option('full')) {
            $urlTemplate = $this->urlTemplateFull;
            $archiveName = 'arch_full.zip';
        }
        
        $url = str_replace('{date}', $formatedDate, $urlTemplate);
        
        echo sprintf("Чтение выгрузки за %s (%s).\n", $formatedDate, $this->option('full') ? 'Всё' : 'Изменения');

        try {
            $remote = fopen($url, 'r');
        } catch (\ErrorException $e) {
            echo sprintf("Выгрузка за %s не найдена.\n", $formatedDate);
            return false;
        }

        $localPath = $this->getFilesDirectoryPath($formatedDate) . '/' . $archiveName;
        $local = fopen($localPath, 'w');

        $chunksize = (1024 * 1024) * 10; // 10 MB
        $length = $this->getRemoteFileSize($url);
        $loaded = 0;
        while(!feof($remote)) {
            $buf = '';
            $buf = fread($remote, $chunksize);
            $bytes = fwrite($local, $buf);

            /* if ($bytes == false) {
                return false;
            } */

            $loaded += $bytes;

            echo sprintf(
                "\rЗагружено %f/%f мб (%f%%)",
                $loaded / 1024 / 1024,
                $length / 1024 / 1024,
                $loaded / $length * 100
            );
        }

        return $formatedDate . '/' . $archiveName;
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

    /**
     * Возвращает размер загружаемого файла в байтах
     * 
     * @param string $url   урл до файла
     * @return float        размер в байтах
     */
    protected function getRemoteFileSize(string $url): float
    {
        $curl = curl_init( $url );

        curl_setopt( $curl, CURLOPT_NOBODY, true );
        curl_setopt( $curl, CURLOPT_HEADER, true );
        curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true );
        curl_setopt( $curl, CURLOPT_FOLLOWLOCATION, true );
        curl_setopt( $curl, CURLOPT_SSL_VERIFYHOST, false );
        curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER, false );

        $data = curl_exec( $curl );
        curl_close( $curl );
        return curl_getinfo($curl, CURLINFO_CONTENT_LENGTH_DOWNLOAD);
    }
}
