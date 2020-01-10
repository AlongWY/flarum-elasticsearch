<?php

namespace Plugin\ESearch\Utils;

use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;

use Flarum\Discussion\Discussion;
use Flarum\Post\Post;

class SearchUtils
{
    private $client;

    function __construct()
    {
        $hosts = [
            [
                'host' => getenv('ES_HOST'),
                'port' => getenv('ES_PORT'),
                'scheme' => getenv('ES_SCHEME')
            ],
        ];
        $client = ClientBuilder::create()
            ->setHosts($hosts)
            ->build();
        $this->client = $client;
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
            'type' => 'post',
            'id' => $post->id,
            'body' => [
                "discId" => $discussion->id,
                "title" => $discussion->title,
                "content" => $post->content,
                "time" => strtotime($post->created_at),
                "discTime" => strtotime($post->discussion->created_at),
                "count" => $count
            ]
        ];
        return $data;
    }
}
