<?php

namespace Plugin\ESearch\Controller;

use Flarum\Api\Controller\ListDiscussionsController;
use Flarum\Discussion\Discussion;
use Flarum\Post\Post;
use Flarum\Discussion\Search\DiscussionSearcher;
use Psr\Http\Message\ServerRequestInterface;
use Tobscure\JsonApi\Document;
use Flarum\Http\UrlGenerator;

use Plugin\ESearch\Service\SearchService;
use Tobscure\JsonApi\Exception\InvalidParameterException;

class SearchController extends ListDiscussionsController
{

    private $searchService;

    /**
     * SearchController constructor.
     * @param SearchService $searchService
     * @param DiscussionSearcher $searcher
     * @param UrlGenerator $url
     */
    public function __construct(SearchService $searchService,
                                DiscussionSearcher $searcher, UrlGenerator $url)
    {
        parent::__construct($searcher, $url);
        $this->searchService = $searchService;
    }

    // 查询数据
    protected function data(ServerRequestInterface $request, Document $document)
    {
        // 关键词
        $query = array_get($this->extractFilter($request), 'q');
        // 分页数据
        $limit = $this->extractLimit($request);

        // fixme try catch
        $offset = $this->extractOffset($request);
        $sort = $this->extractSort($request);
        $load = array_merge($this->extractInclude($request), ['state']);

        $results = $this->searchService->search($query, $limit, $offset, $sort);

        $document->addPaginationLinks(
            $this->url->to('api')->route('es.discussions.index'),
            $request->getQueryParams(),
            $offset,
            $limit,
            $results->areMoreResults() ? null : 0
        );

        $results = $results->getResults()->load($load);

        if ($relations = array_intersect($load, ['firstPost', 'lastPost'])) {
            foreach ($results as $discussion) {
                foreach ($relations as $relation) {
                    if ($discussion->$relation) {
                        $discussion->$relation->discussion = $discussion;
                    }
                }
            }
        }

        return $results;
    }
}
