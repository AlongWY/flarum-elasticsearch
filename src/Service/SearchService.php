<?php

namespace Plugin\ESearch\Service;

use Flarum\Discussion\Discussion;
use Flarum\Post\Post;
use Flarum\Search\SearchResults;
use Illuminate\Database\Eloquent\Collection;
use Plugin\ESearch\Utils\SearchUtils;

class SearchService
{

    private $searchUtils;

    /**
     * SearchService constructor.
     * @param $searchUtils
     */
    public function __construct(SearchUtils $searchUtils)
    {
        $this->searchUtils = $searchUtils;
    }


    function search($query, $limit, $offset, $sort)
    {
        $convertData = $this->convertDiscussion($query, $limit + 1, $offset, $sort);

        $discussions = new Collection();
        if (count($convertData) > 0) {
            $query = Discussion::query()->whereIn("id", $this->getDiscussionIds($convertData));

            if ($sort !== null) {
                // 最新回复
                if (array_key_exists("lastPostedAt", $sort)) {
                    $query = $query->orderBy("last_posted_at", $sort["lastPostedAt"]);
                }

                // 热门话题
                if (array_key_exists("commentCount", $sort)) {
                    $query = $query->orderBy("comment_count", $sort["commentCount"]);
                }

                // 近期话题
                if (array_key_exists("createdAt", $sort)) {
                    $query = $query->orderBy("created_at", $sort["createdAt"]);
                }

                // 历史话题
                if (array_key_exists("createdAt", $sort)) {
                    $query = $query->orderBy("created_at", $sort["createdAt"]);
                }

            }
            $discussions = $query->get();
            $this->loadRelevantPosts($discussions, $convertData);
        }
        $areMoreResults = $limit > 0 && count($convertData) > $limit;
        if ($areMoreResults) {
            $discussions->pop();
        }
        return new SearchResults($discussions, $areMoreResults);
    }

    function getDiscussionIds($convertData)
    {
        $temp = [];
        foreach ($convertData as $data) {
            array_push($temp, $data["id"]);
        }
        return $temp;
    }

    // 转换数据
    function convertDiscussion($query, $limit, $offset, $sort)
    {
        $search = $this->searchUtils->getESearch();
        if ($search == null) return []; // do nothing
        $tempData = [];
        $params = [
            "scroll" => "30s",          // 设置游标查询过期时间，不应该太长
            "size" => $limit,           // 返回多少数量的文档，作用于单个分片
            "from" => $offset,
            'index' => 'flarum',
            'body' => [
                "query" => [
                    'multi_match' => [
                        'query' => $query,
                        'fields' => ["title", "content"]
                    ]
                ],
                // TODO 排序
                // lastPostedAt
                // commentCount
                // createdAt
                // "ratings" => $sort
            ]
        ];

        $searched = $search->search($params);

        foreach ($searched['hits']['hits'] as $item) {
            $discId = $item["_source"]["discId"];
            if (!key_exists($discId, $tempData)) {
                $tempData[$discId] = array("id" => $discId, "postIds" => array());
            }
            array_push($tempData[$discId]["postIds"], intval($item["_id"]));
        }

        return $tempData;
    }

    // 处理帖子回复内容
    function loadRelevantPosts(Collection $discussions, $relevantPostIds)
    {
        $postIds = [];
        foreach ($relevantPostIds as $ids) {
            $postIds = array_merge($postIds, array_slice($ids["postIds"], 0, 2));
        }

        $posts = $postIds ? Post::query()->whereIn("id", $postIds)->get() : [];
        $temp = [];
        foreach ($posts as $post) {
            array_push($temp, $post);
        }

        foreach ($discussions as $discussion) {
            $discussion->relevantPosts = array_filter($temp, function ($post) use ($discussion) {
                return $post["discussion_id"] == $discussion->id;
            });
        }
    }

    // 根据话题id获取所有的帖子
    function getPostsByDiscussionId($discussionId)
    {
        return Post::query()->where("discussion_id", $discussionId)->where("type", "comment")->get();
    }

    // 添加帖子到索引
    function addPostToIndex(Post $post)
    {
        $search = $this->searchUtils->getESearch();
        if ($search == null) return; // do nothing
        $posts = $this->getPostsByDiscussionId($post->discussion_id);
        foreach ($posts as $item) {
            $doc = $this->searchUtils->buildESDocument($post->discussion, $item, $posts->count());
            $search->update($doc);
        }
    }

    // 删除帖子到索引
    function deletePostToIndex(Post $post)
    {
        $search = $this->searchUtils->getESearch();
        if ($search == null) return; // do nothing
        // 删除当前记录
        $search->delete([
            'index' => 'flarum',
            'id' => $post->id,
        ]);
        // 更新其他记录的count
        $posts = $this->getPostsByDiscussionId($post->discussion_id);
        foreach ($posts as $item) {
            $doc = $this->searchUtils->buildESDocument($post->discussion, $item, $posts->count());
            $search->update($doc);
        }
    }

    // 话题重命名
    function renameDiscussion(Discussion $discussion)
    {
        // 获取索引
        $search = $this->searchUtils->getESearch();
        if ($search == null) return; // do nothing
        // 更新所有记录
        $posts = $this->getPostsByDiscussionId($discussion->id);
        foreach ($posts as $item) {
            $doc = $this->searchUtils->buildESDocument($discussion, $item, $posts->count());
            $search->update($doc);
        }
    }

    // 删除话题
    function deleteDiscussion(Discussion $discussion)
    {
        // 获取索引
        $search = $this->searchUtils->getESearch();
        if ($search == null) return; // do nothing
        // 更新所有记录
        $posts = $this->getPostsByDiscussionId($discussion->id);
        foreach ($posts as $item) {
            $search->delete([
                'index' => 'flarum',
                'id' => $item->id,
            ]);
        }
    }

    // 添加话题
    function addDiscussion(Discussion $discussion)
    {
        // 获取索引
        $search = $this->searchUtils->getESearch();
        if ($search == null) return; // do nothing
        // 更新所有记录
        $posts = $this->getPostsByDiscussionId($discussion->id);
        foreach ($posts as $item) {
            $doc = $this->searchUtils->buildESDocument($discussion, $item, $posts->count());
            $search->update($doc);
        }
    }
}
