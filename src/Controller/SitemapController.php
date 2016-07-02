<?php

namespace TomekKnapczyk\Sitemap\Controller;

use Pagekit\Application as App;
use TomekKnapczyk\Sitemap\Classes\Sitemap as Sitemap;

/**
 * @Access(admin=true)
 * @Access("sitemap: manage settings")
 */
class SitemapController
{
    public function indexAction()
    {
        return [
            '$view' => [
                'title' => __('Sitemap Settings'),
                'name'  => 'sitemap:views/admin/settings.php'
            ],
            '$data' => [
                'config' => App::module('sitemap')->config()
            ]
        ];
    }

    /**
     * @Route("/generate", methods="POST")
     * @Request({"config" : "array"}, csrf=true)
     */
    public function generateAction($config)
    {
        function siteURL()
        {
            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
            $domainName = $_SERVER['HTTP_HOST'].'/';
            return $protocol.$domainName;
        }
        
        define( 'SITE_URL', siteURL() );

        $map = new Sitemap($config['frequency'], SITE_URL);
        
        return $map->generate();
    }
}
