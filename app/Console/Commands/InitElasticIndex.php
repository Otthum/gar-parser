<?php

namespace App\Console\Commands;

use App\Services\Elastic\ElasticSearchService;
use Illuminate\Console\Command;

class InitElasticIndex extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'elastic:init-index';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Создаёт индекс для адресов в elasticsearch';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $body = [
            "settings" => [
                "index" => [
                    "number_of_shards" => 2,
                    "number_of_replicas" => 1
                ],
                "analysis" => [
                    "analyzer" => [
                        "autocomplete_analyzer" => [
                            "tokenizer" => "autocomplete_tokenizer",
                            "filter" => ["lowercase"]
                        ],
                        "autocomplete_search" => [
                            "tokenizer" => "whitespace",
                            "filter" => ["lowercase"],
                            "char_filter" => [
                                "dash_map"
                            ]
                        ]
                    ],
                    "tokenizer" => [
                        "autocomplete_tokenizer" => [
                            "type" => "edge_ngram",
                            "min_gram" => 1,
                            "max_gram" => 20,
                            "token_chars" => ["letter", "digit", "custom"],
                            "custom_token_chars" => ["/"],
                        ]
                    ],
                    "char_filter" => [
                        "dash_map" => [
                            "type" => "mapping",
                            "mappings" => [
                                "- => \\u0020", # Для поиска городов вроде Улан-Удэ, Ростов-на-Дону и т.д.
                            ]
                        ],
                    ]
                ]
            ],
            "mappings" => [
                "properties" => [
                    "id" => [
                        "type" => "long"
                    ],
                    "uuid" => [
                        "type" => "keyword"
                    ],
                    "level" => [
                        "type" => "integer"
                    ],
                    "number" => [
                        "type" => "integer"
                    ],
                    "parent" => [
                        "type" => "keyword"
                    ],
                    "active" => [
                        "type" => "boolean"
                    ],
                    "address" => [
                        "type" => "text",
                        "analyzer" => "autocomplete_analyzer",
                        "search_analyzer" => "autocomplete_search",
                        "fields" => [
                            "raw" => [
                                "type" => "keyword"
                            ]
                        ]
                    ],
                ]
            ]
        ];

        $service = new ElasticSearchService;

        dump($service->createIndex('gar', $body));
    }
}
