<?php

namespace Plugin\ESearch;

use Flarum\Extend;
use Illuminate\Contracts\Events\Dispatcher;
use Plugin\ESearch\Utils\LoadSettings;
use Plugin\ESearch\Controller\SearchController;

return [
    (new Extend\Frontend('forum'))
        ->js(__DIR__ . '/js/dist/forum.js'),
    (new Extend\Frontend('admin'))
        ->js(__DIR__ . '/js/dist/admin.js'),
    (new Extend\Locales(__DIR__ . '/locale')),
    (new Extend\Routes('api'))
        ->get('/es/discussions', 'es.discussions.index', SearchController::class),
    function (Dispatcher $events) {
        $events->subscribe(LoadSettings::class);
        $events->subscribe(SearchDispatcher::class);
    }
];
