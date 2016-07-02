<?php

return [

    /*
     * Installation hook.
     */
    'install' => function ($app) {

    },

    'enable' => function ($app) {

    },

    'uninstall' => function ($app) {

        $app['config']->remove('sitemap');

    },

];