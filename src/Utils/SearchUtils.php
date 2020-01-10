<?php

namespace Plugin\ESearch\Utils;

use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;

use Flarum\Discussion\Discussion;
use Flarum\Post\Post;

class SearchUtils
{
    function getESearch(): Client
    {
        return ClientBuilder::create()->build();
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
