<?php

namespace Movieaster\MovieManagerBundle\Component\TMDb;

use Movieaster\MovieManagerBundle\Component\TMDb\TMDb;

class TMDbFactory
{
	public static function createYAML()
	{
		$apiKey = '8ca09729fd84b0a658bdb5f37e80a9bb';
		$lang = 'de'; //TODO read from moviaster config
		
		return new TMDb($apiKey, TMDb::YAML, $lang);
	}
}

?>