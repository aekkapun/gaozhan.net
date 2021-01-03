<?php

use yii\web\UrlRule;

return [
    'bookmarks' => 'project/bookmarks',
    'projects/tags/<tags:\w+[\w-]+>' => 'project/list',
    'projects' => 'project/list',
    'projects/<uuid:\w+>/<slug>' => 'project/view',
    'top-100' => 'project/top-projects',
    'user' => 'user/view',

    'about' => 'site/about',
    'logout' => 'site/logout',
    'login' => 'site/login',
    'signup' => 'site/signup',
    'auth' => 'site/auth',
    'sitemap.xml' => 'site/sitemap',
    // API
    'api/1.0' => 'api1/docs/index',
    'api' => 'api1/docs/index',
    [
        'class' => \yii\rest\UrlRule::class,
        'controller' => ['1.0/projects' => 'api1/project'],
        'only' => ['index', 'create', 'view', 'update', 'vote', 'delete', 'screenshots'],
        'prefix' => 'api',
        'tokens' => ['{uuid}' => '<uuid:\\w+>',],
        'patterns' => [
            'PUT,PATCH {uuid}' => 'update',
            'DELETE {uuid}' => 'delete',
            'GET,HEAD {uuid}' => 'view',
            'POST' => 'create',
            'GET,HEAD' => 'index',
            '{uuid}' => 'options',
            '' => 'options',
        ],
        'extraPatterns' => [
            'PUT,PATCH {uuid}/vote' => 'vote',
            'POST {uuid}/uploadScreenshots' => 'screenshots'
        ],
        'ruleConfig' => [
            'class' => UrlRule::class,
            'defaults' => [
                'expand' => 'users',
            ]
        ],
    ],
    [
        'class' => \yii\rest\UrlRule::class,
        'controller' => ['1.0/users' => 'api1/user'],
        'only' => ['index', 'view'],
        'prefix' => 'api',
    ],
    [
        'class' => \yii\rest\UrlRule::class,
        'controller' => ['1.0/bookmarks' => 'api1/bookmark'],
        'only' => ['index', 'create', 'delete'],
        'prefix' => 'api',
        'tokens' => ['{uuid}' => '<uuid:\\w+>',],
        'ruleConfig' => [
            'class' => UrlRule::class,
            'defaults' => [
                'expand' => 'project',
            ]
        ],
    ],
];
