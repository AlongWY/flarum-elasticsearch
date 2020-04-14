<?php

namespace Plugin\ESearch\Utils;

use Flarum\Discussion\Discussion;
use Flarum\Post\Post;
use Flarum\Settings\SettingsRepositoryInterface;

use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;
use Elasticsearch\Common\Exceptions\ElasticsearchException;


class SearchUtils
{
    /**
     * @var SettingsRepositoryInterface
     */
    protected $settings;

    /**
     * @var string $settingsPrefix
     */
    public $settingsPrefix = 'alongwy-elasticsearch.';
    /**
     * @var Client
     */
    private $client;

    /**
     * LoadSettingsFromDatabase constructor
     *
     * @param SettingsRepositoryInterface $settings
     */
    public function __construct(SettingsRepositoryInterface $settings)
    {
        $this->settings = $settings;
        $hosts = [
            [
                'host' => $this->settings->get($this->settingsPrefix . 'host', "localhost"),
                'port' => $this->settings->get($this->settingsPrefix . 'port', 9200),
                'scheme' => $this->settings->get($this->settingsPrefix . 'scheme', "http")
            ],
        ];

        try {
            $client = ClientBuilder::create()
                ->setHosts($hosts)
                ->build();


            $indexParams['index'] = "flarum";
            $exists = $client->indices()->exists($indexParams);
            if (!($exists)) {
                $client->indices()->create([
                    'index' => 'flarum',
                    'body' => [
                        'settings' => [
                            'number_of_shards' => $this->settings->get($this->settingsPrefix . 'number_of_shards', 5),
                            'number_of_replicas' => $this->settings->get($this->settingsPrefix . 'number_of_replicas', 1)
                        ],
                        "mappings" => [
                            'properties' => [
                                'title' => [
                                    "type" => "text",
                                    "analyzer" => "ik_max_word",
                                    "search_analyzer" => "ik_smart"
                                ],
                                "content" => [
                                    "type" => "text",
                                    "analyzer" => "ik_max_word",
                                    "search_analyzer" => "ik_smart"
                                ],
                                'discId' => [
                                    'type' => 'integer'
                                ],
                                "count" => [
                                    'type' => 'integer'
                                ],
                                "time" => [
                                    "type" => "date"
                                ],
                                "discTime" => [
                                    "type" => "date"
                                ],
                            ]
                        ]
                    ]
                ]);
            }
            $this->client = $client;
        } catch (ElasticsearchException $e) {
            $this->client = null;
        }
    }

    function getESearch(): Client
    {
        return $this->client;
    }

    // 构造文档
    function buildESDocument(Discussion $discussion, Post $post, $count)
    {
        $data = [
            'index' => 'flarum',
            'id' => $post->id,
            'body' => [
                'doc' => [
                    "discId" => $discussion->id,
                    "title" => $discussion->title,
                    "content" => $post->content,
                    "time" => strtotime($post->created_at),
                    "discTime" => strtotime($post->discussion->created_at),
                    "count" => $count
                ],
                "doc_as_upsert" => true
            ]
        ];
        return $data;
    }
}
