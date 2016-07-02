<?php

use Pagekit\Application;

return [

    'name' => 'sitemap',

    'type' => 'extension',

    'main' => function (Application $app) {

    },

    'autoload' => [

        'TomekKnapczyk\\Sitemap\\' => 'src'

    ],

    'routes' => [

        '/sitemap' => [
            'name' => '@sitemap/admin',
            'controller' => [
                'TomekKnapczyk\\Sitemap\\Controller\\SitemapController'
            ]
        ]

    ],

    'menu' => [

        'sitemap' => [

            'label' => 'Sitemap',

            'icon' => 'sitemap:icon.svg',

            'url' => '@sitemap/admin',

            'access' => 'system: manage settings'
        ]

    ],

    'permissions' => [

        'sitemap: manage settings' => [
            'title' => 'Manage settings'
        ],

    ],

    'settings' => '@sitemap/admin',

    'config' => [
        'frequency' => 'monthly'
    ],

    'events' => [

        'view.scripts' => function ($event, $scripts) {
            $scripts->register('sitemap-link', 'sitemap:app/bundle/link.js', '~panel-link');
            $scripts->register('sitemap-dashboard', 'sitemap:app/bundle/dashboard.js', '~dashboard');
        }

    ]

];
