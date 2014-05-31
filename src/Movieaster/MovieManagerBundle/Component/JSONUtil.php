<?php

namespace Movieaster\MovieManagerBundle\Component;

use Symfony\Component\HttpFoundation\Response;

class JSONUtil
{
    public static function createJsonResponse($data)
    {
        $response = new Response();
        $callbackFunction = $_REQUEST['callback'];
        $content = "";
        if($callbackFunction != null) {
            $content .= $callbackFunction . "(";
        }
        $content .= json_encode($data, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
        if($callbackFunction != null) {
            $content .= ");";
        }
        $response->setContent($content);
        return $response;
    }
}
?>