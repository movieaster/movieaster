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
	
	public static function createJsonResponseFound($found)
	{
		return JSONUtil::createJsonResponse(array("f" => $found));
	}
	
	public static  function createJsonMovieInfo($entity) {
		$values["i"] = $entity->getId();
		$values["c"] = $entity->getThumbInline();
		$values["t"] = $entity->getName();
		$values["t2"] = $entity->getNameOriginal();
		$values["t3"] = $entity->getNameAlternative();
		$values["y"] = $entity->getReleased()->format("Y");
		$values["r"] = $entity->getRatingTmdb();
		$values["v"] = $entity->getVotesTmdb();
		$values["g"] = $entity->getGenres();
		$values["a"] = $entity->getActors();
		$values["o"] = $entity->getOverview();
		$values["ti"] = $entity->getTmdbId();
		$values["ii"] = $entity->getImdbId();
		$values["h"] = $entity->getHomepage();
		$values["tr"] = str_replace("http://www.youtube.com/watch?v=",
									"http://www.youtube-nocookie.com/embed/", $entity->getTrailer());
		$values["b1"] = $entity->getBackdrop1();
		$values["b2"] = $entity->getBackdrop2();
		$values["b3"] = $entity->getBackdrop3();
		if(method_exists($entity,'getPath')) {
			$values["p"] = $entity->getPath()->getName();
		}
		return $values; 
	}

}
?>