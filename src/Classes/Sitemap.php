<?php

namespace TomekKnapczyk\Sitemap\Classes;

class Sitemap
{
    private $output_file = "sitemap.xml";
    
    private $site;

    private $frequency;

    private $priority;

    private $varsion;

    private $agent;

    private $site_scheme;

    private $site_host;

    private $scanned;

    private $pf;

    function __construct($frequency, $server_name)
    {
        $this->site = $server_name;

        $this->frequency = $frequency;

        $this->priority = "0.5";

        $this->agent = "Mozilla/5.0 Sitemap Generator Tomasz Knapczyk";

        $this->site_scheme = parse_url($this->site, PHP_URL_SCHEME);

        $this->site_host = parse_url($this->site, PHP_URL_HOST);
    }

    public function generate(){
        $this->pf = fopen($this->output_file, "w");
        if (!$this->pf)
        {
            return 0;
        }

        fwrite ($this->pf, "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n" .
                     "<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n".
                     "  <url>\n" .
                     "    <loc>" . $this->site . "</loc>\n" .
                     "    <changefreq>" . $this->frequency . "</changefreq>\n" .
                     "  </url>\n");

        $this->scanned = array();
        $this->scan($this->GetEffectiveURL($this->site));
        
        fwrite ($this->pf, "</urlset>\n");
        fclose ($this->pf);

        return 1;
    }

    private function GetURL($url)
    {

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_USERAGENT, $this->agent);

        $data = curl_exec($ch);

        curl_close($ch);

        return $data;
    }

    private function GetQuotedUrl($str)
    {
        if($str[0] != '"') return $str;

        $ret = "";

        $len = strlen($str);
        for($i = 1; $i < $len; $i++)
        {
            if($str[$i] == '"') break;
            $ret .= $str[$i];
        }
        
        return $ret;
    }


    private function GetHREFValue($anchor)
    {

        $split1 = explode("href=", $anchor);
        $split2 = explode(">", $split1[1]);
        $href_string = $split2[0];

        if ($href_string[0] == '"')
        {
            $url = $this->GetQuotedUrl($href_string);
        }
        else
        {
            $spaces_split = explode(" ", $href_string);
            $url = $spaces_split[0];
        }
        return $url;
    }

    private function GetEffectiveURL($url)
    {
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt ($ch, CURLOPT_USERAGENT, $this->agent);
        curl_exec($ch);

        $effective_url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);

        curl_close($ch);

        return $effective_url;
    }


    private function ValidateURL($url_base, $url)
    {
        $scheme = parse_url($url, PHP_URL_SCHEME);
            
        // $scheme = $parsed_url["scheme"];

        if (($scheme != $this->site_scheme) && ($scheme != "")) return false;
            
        $host = parse_url($url, PHP_URL_HOST);

        if (($host != $this->site_host) && ($host != "")) return false;

        if ($host == "")
        {
            if ($url[0] == '#')
            {
                return false;
            }
        
            if ($url[0] == '/')
            {
                $url = $this->site_scheme . "://" . $this->site_host . $url;
            }
            else
            {
            
                $path = parse_url($url_base, PHP_URL_PATH);
                
                if (substr ($path, -1) == '/')
                {
                    $url = $this->site_scheme . "://" . $this->site_host . $path . $url;
                }
                else
                {
                    $dirname = dirname ($path);

                    if ($dirname[0] != '/')
                    {
                        $dirname = "/$dirname";
                    }
        
                    if (substr ($dirname, -1) != '/')
                    {
                        $dirname = "$dirname/";
                    }

                    $url = $this->site_scheme . "://" . $this->site_host . $dirname . $url;
                }
            }
        }

        $url = $this->GetEffectiveURL($url); 

        if (in_array($url, $this->scanned)) return false;
        
        return $url;
    }

    // private function skipURL($url)
    // {
    //     global $skip_url;

    //     if (isset ($skip_url))
    //     {
    //         foreach ($skip_url as $v)
    //         {           
    //             if (substr ($url, 0, strlen ($v)) == $v) return true;
    //         }
    //     }

    //     return false;            
    // }


    private function scan($url)
    {
        array_push($this->scanned, $url);
        
        // Remove unneeded slashes
        if (substr ($url, -2) == "//") 
        {
            $url = substr ($url, 0, -2);
        }
        if (substr ($url, -1) == "/") 
        {
            $url = substr ($url, 0, -1);
        }

        $headers = get_headers($url, 1);

        if (strpos ($headers[0], "404") !== false)
        {
            return false;
        }
        if (strpos ($headers[0], "301") !== false)
        {
            $url = $headers["Location"];
            array_push ($this->scanned, $url);
        }

        if (is_array($headers["Content-Type"]))
        {
            $content = explode (";", $headers["Content-Type"][0]);
        }
        else
        {
            $content = explode (";", $headers["Content-Type"]);
        }

        if ($content[0] != "text/html")
        {
            return false;
        }
        
        $html = $this->GetURL($url);
        $html = trim($html);
        if ($html == "") return true;

        $html = str_replace ("\r", " ", $html);
        $html = str_replace ("\n", " ", $html);
        $html = str_replace ("<A ", "<a ", $html);
        $html = substr($html, strpos("<a ", $html));

        $a1 = explode ("<a ", $html);
        
        foreach ($a1 as $next_url)
        {
            $next_url = trim($next_url);

            if ($next_url == "") continue; 

            $next_url = $this->GetHREFValue($next_url); 
            
            if($next_url == '') continue;

            $next_url = $this->ValidateURL($url, $next_url);
            
            if ($next_url == false) continue;

            if ($this->scan($next_url))
            {
                fwrite ($this->pf, "  <url>\n" .
                             "    <loc>" . htmlentities($next_url) ."</loc>\n" .
                             "    <changefreq>" . $this->frequency . "</changefreq>\n" .
                             "    <priority>" . $this->priority . "</priority>\n" .
                             "  </url>\n"); 
            }
        }
        return true;
    }
}