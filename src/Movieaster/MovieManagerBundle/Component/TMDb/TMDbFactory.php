<?php

namespace Movieaster\MovieManagerBundle\Component\TMDb;

use Movieaster\MovieManagerBundle\Component\TMDb\TMDb;

class TMDbFactory
{
	public static function createInstance()
	{
		$apiKey = '8ca09729fd84b0a658bdb5f37e80a9bb';
		$lang = 'de'; //TODO read from moviaster config
		
		return new TMDb($apiKey, $lang);
	}
	
	// Deprecated
	public static function createYAML()
	{
		return TMDbFactory::createInstance();	
	}
	
	public static function createMovieInfo($name, $year)
	{
		return TMDbFactory::createInstance();	
	}	
}

?>