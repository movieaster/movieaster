<?php

namespace Movieaster\MovieManagerBundle\Component\TMDb;

use Movieaster\MovieManagerBundle\Component\TMDb\TMDb;
use Movieaster\MovieManagerBundle\Component\StringUtil;

class MovieInfo
{
    public $id = "";
    public $nameFolder = "";
    public $path = "";
    public $updated = "";
    public $found = "";
    public $name = "";
    public $nameOriginal = "";
    public $nameAlternative = "";
    public $released = "";
    public $overview = "";
    public $imdbId = "";
    public $tmdbId = "";
    public $homepage = "";
    public $trailer = "";
    public $ratingTmdb = "";
    public $votesTmdb = "";
    public $genres = "";
    public $directors = "";
    public $writers = "";
    public $actors = "";
    public $thumb = "";
    public $thumbInline = "";
    public $poster = "";
    public $backdrop1 = "";
    public $backdrop2 = "";
    public $backdrop3 = "";
}

class TMDbFactory
{
	public static function createInstance()
	{
		$apiKey = '8ca09729fd84b0a658bdb5f37e80a9bb';
		$lang = 'de'; //TODO read from moviaster config
		return new TMDb($apiKey, $lang);
	}
	
	public static function createMoviesByNameAndYear($name, $year)
	{
		//TODO
	}
	
	public static function createMoviesByName($name)
	{
		//TODO
	}
	
	public static function createMovieInfoById($tmdbId)
	{
		$movieInfo = new MovieInfo();
		$tmdbResult = TMDbFactory::createInstance()->getMovie($tmdbId);
		if($tmdbResult == "Nothing found." || $tmdbResult["title"] == "") {
			return $movieInfo;
		}
		$movieInfo->name = $tmdbResult["title"];
		$movieInfo->nameOriginal = $tmdbResult["original_title"];
		$movieInfo->nameAlternative = StringUtil::createNullSaveString("tagline", $tmdbResult);
		$movieInfo->released = new \DateTime($tmdbResult["release_date"]);
		$movieInfo->overview = StringUtil::createNullSaveString("overview", $tmdbResult);
		$movieInfo->imdbId = StringUtil::createNullSaveString("imdb_id", $tmdbResult);
		$movieInfo->tmdbId = StringUtil::createNullSaveString("id", $tmdbResult);
		$movieInfo->homepage = StringUtil::createNullSaveString("homepage", $tmdbResult);
		$movieInfo->trailer = StringUtil::createNullSaveString("trailer", $tmdbResult);
		$movieInfo->ratingTmdb = StringUtil::createNullSaveString("vote_average", $tmdbResult);
		$movieInfo->votesTmdb = StringUtil::createNullSaveString("vote_count", $tmdbResult);
		$movieImages = TMDbFactory::createInstance()->getMovieImages($tmdbId);
		$movieInfo->backdrop1 = TMDbFactory::getBackdropUrl($movieImages, 1);
		$movieInfo->backdrop2 = TMDbFactory::getBackdropUrl($movieImages, 2);
		$movieInfo->backdrop3 = TMDbFactory::getBackdropUrl($movieImages, 3);
		$movieInfo->poster = TMDbFactory::createInstance()->getImageUrl($tmdbResult["poster_path"], TMDb::IMAGE_POSTER, "original");
		$movieInfo->thumb = TMDbFactory::createInstance()->getImageUrl($tmdbResult["poster_path"], TMDb::IMAGE_POSTER, "w92");
		$movieInfo->thumbInline = StringUtil::createBase64Image($movieInfo->thumb);
		$movieInfo->genres = StringUtil::createCommaSeparatedList("name", $tmdbResult["genres"]);
		$cast = TMDbFactory::createInstance()->getMovieCast($tmdbId);
		$movieInfo->actors = StringUtil::createCommaSeparatedList("name", $cast["cast"]);
		$movieInfo->directors = StringUtil::createCommaSeparatedListbyJob("name", $cast["crew"], "Director");
		$movieInfo->writers = StringUtil::createCommaSeparatedListbyJob("name", $cast["crew"], "Editor");
		return $movieInfo;
	}
	
	public static function getBackdropUrl($movieImages, $num) {
		$numBackdrops = count($movieImages["backdrops"]);
		$numPosters = count($movieImages["posters"]);
		if($numBackdrops >= $num) {
			$imageFilePath = $movieImages["backdrops"][$num-1]["file_path"];
		} else if($num > $numBackdrops && $numPosters >= $num-$numBackdrops) {
			$imageFilePath = $movieImages["posters"][$num-$numBackdrops-1]["file_path"];
		} else {
			return "";
		}
		return TMDbFactory::createInstance()->getImageUrl($imageFilePath, TMDb::IMAGE_BACKDROP, "original");
	}

}

?>