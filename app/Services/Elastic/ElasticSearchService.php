<?php

namespace App\Services\Elastic;


class ElasticSearchService
{
    protected const PER_PAGE = 10;


    protected ElasticSearchClient $client;


    public function __construct()
    {
        $this->client = new ElasticSearchClient(
            config('app.elastic_user'),
            config('app.elastic_pass'),
            config('app.elastic_host'),
            config('app.elastic_port'),
        );
    }


    public function searchAddress(string $q, array $filters = [], int $page = 1)
    {
        $should = [
            ["term" => [
                "address.raw" => [
                    "value" => $q,
                    "boost" => 1.0
                ]
            ]],
            ["match" => [
                "address" => [
                    "query" => $q,
                    "operator" => "AND"
                ]
            ]]
        ];

        $mustNot = [
            ["terms" => [
                "level" => $filters['exclude_levels'] ?? []
            ]],
        ];

        $must = [];

        if (isset($filters['is_active'])) {
            $must[] = [
                'term' => [
                    'is_active' => filter_var($filters['is_active'], FILTER_VALIDATE_BOOL)
                ]
            ];
        }

        $query = [
            "function_score" => [
                "query" => [
                    "bool" => [
                        "should" => $should,
                        "must_not" => $mustNot,
                        "must" => $must
                    ]
                ],
                "field_value_factor" => [
                    "field" => "level",
                    "factor" => 1,
                    "modifier" => "reciprocal",
                    "missing" => 1
                ]
            ]
        ];

        $res = $this->client->search('gar', [
            "query" => $query,
            "sort" => [
                ["_score" => ["order" => "desc"]],
                ["parent" => ["order" => "asc"]],
                ["level" => ["order" => "asc"]],
                ["number" => ["order" => "asc"]],
                ["address.raw" => ["order" => "asc"]]
            ],
            'size' => self::PER_PAGE,
            'from' => ($page - 1) * self::PER_PAGE
        ]);

        if ($res === false) {
            return null;
        }

        $source = [];
        foreach ($res->hits->hits as $item) {
            $source[] = $item->_source;
        }
        return $source;
    }

    public function createIndex(string $name, array $body)
    {
        return $this->client->createIndex($name, $body);
    }

    public function indexDocuments(string $index, array $docs)
    {
        return $this->client->indexBulk($index, $docs);
    }
}
