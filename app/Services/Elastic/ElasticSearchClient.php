<?php

namespace App\Services\Elastic;

use CurlHandle;
use Illuminate\Support\Facades\Log;
use stdClass;

class ElasticSearchClient
{
    public function __construct(
        protected string $user,
        protected string $pass,
        protected string $host,
        protected string $port        
    ) {}



    public function createIndex(string $name, array $body)
    {
        return $this->call($this->host . '/' . $name, 'PUT', json_encode($body));
    }

    public function search(string $index, array $body)
    {
        $body['size'] = $body['size'] ?? 10;

        return $this->call($this->host . '/' . $index . '/_search?search_type=dfs_query_then_fetch', 'POST', json_encode($body));
    }

    public function indexDoc(string $index, array $body, ?string $id = null)
    {
        $url = $this->host . '/' . $index . '/_doc';

        if ($id !== null) {
            $url .= '/' . $id;
        }

        return $this->call($url, 'POST', json_encode($body));
    }

    public function indexBulk(string $index, array $data)
    {
        $body = '';
        foreach ($data as $doc) {
            $body .= json_encode([
                'index' => isset($doc['id']) ? ['_id' => $doc['id']] : []
            ]) . "\n";
            $body .= json_encode($doc['data']) . "\n";
        }

        return $this->call($this->host . '/' . $index . '/_bulk', 'POST', $body);
    }



    protected function call(string $url, string $method, ?string $body = null): stdClass|false
    {
        $ch = curl_init();

        if (strtolower($method) === 'get') {
            $url .= '?' . $body;
        } else {
            curl_setopt_array($ch, [
                CURLOPT_CUSTOMREQUEST => $method,
                CURLOPT_POSTFIELDS => $body,
            ]);
        }

        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_PORT => $this->port,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_USERPWD => $this->user . ':' . $this->pass,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json'
            ],

        ]);

        $res = curl_exec($ch);

        if ($res !== false) {
            $res = json_decode($res);

            if (curl_getinfo($ch, CURLINFO_RESPONSE_CODE) != 200) {
                Log::error(sprintf(
                    "Error happend while proccessing elasticsearch request.\nURL - %s\nRequest - %s\nError - %s",
                    curl_getinfo($ch, CURLINFO_EFFECTIVE_URL),
                    $body ?? 'NONE',
                    json_encode($res->error)
                ));
            }

            return $res;
        }

        Log::error(sprintf(
            "cURL error happened during elasticsearch request.\nURL - %s\nRequest - %s\nError - %s",
            curl_getinfo($ch, CURLINFO_EFFECTIVE_URL),
            $body ?? 'NONE',
            curl_error($ch)
        ));

        return false;
    }
}