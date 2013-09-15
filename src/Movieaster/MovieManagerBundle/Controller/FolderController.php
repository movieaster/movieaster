<?php

namespace Movieaster\MovieManagerBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Finder\Finder;
use Movieaster\MovieManagerBundle\Component\TMDb\TMDb;
use Movieaster\MovieManagerBundle\Component\TMDb\TMDbFactory;
use Movieaster\MovieManagerBundle\Entity\Movie;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOException;

/**
 * Folder controller.
 *
 * @Route("/folder")
 */
class FolderController extends Controller
{
    /**
     * Placeholder for root path.
     *
     * @Route("/", name="folder")
     */
    public function indexAction()
    {
	    return new Response("not in use");
    }
    	
   	/**
     * Refresh all Folder entity.
     *
     * @Route("/refresh", name="folder_refresh")
     */
    public function refreshAction()
    {
		$modeArchived = false;
		$logger = $this->get('logger');
		$logger->debug("Refresh all Movies:");

   		$em = $this->getDoctrine()->getEntityManager();
		$paths = $em->getRepository('MovieasterMovieManagerBundle:Path')->findAll();

		// just for the statistics
		$countNew = 0;
		$countOld = 0;
		
		foreach ($paths as $path) {
			$logger->debug("Refresh Path: " . $path->getPath());
			
			// mark all still existing movie folder records for an update check
			$em->createQuery('UPDATE MovieasterMovieManagerBundle:Movie f SET f.updated=:updated WHERE f.path=:path and f.archived=:archived')->setParameter('updated', false)->setParameter('archived', $modeArchived)->setParameter('path', $path->getId())->execute();

			// check the filesystem for changes
			$finder = new Finder();
			$finder->files()->in(__DIR__)->in($path->getPath())->directories()->depth('== 0');
			foreach ($finder as $file) {
				if($file->getFilename() != "@eaDir")  {
					$logger->debug("folder: " . $file->getFilename() . " (archived: " . $modeArchived . ")");
			 		$enity = $em->getRepository('MovieasterMovieManagerBundle:Movie')->findOneBy(array('nameFolder' => $file->getFilename(), 'path' => $path->getId(), 'archived' => $modeArchived));
			 		if ($enity) {
				 		$logger->debug("still exist in DB");
			 			$enity->setUpdated(true);
			 			$em->flush();
			 			$countOld++;
			 		} else {
				 		$logger->debug("create new DB record");
			 			$folder = new Movie();
						$folder->setNameFolder($file->getFilename());			 			
			 			$folder->setUpdated(true);
			 			$folder->setFound(false);
			 			$folder->setArchived($modeArchived);			 			
			 			$folder->setPath($path);
			 			$em->persist($folder);
						$em->flush();
						$countNew++;
					}
					$logger->debug("updated.");
				}
			}
		}
		
		$logger->debug("remove old db folder/movie records:");
		// remove old db folder/movie records
		$countDelete = 0;
		$deleteFolders = $em->getRepository('MovieasterMovieManagerBundle:Movie')->findBy(array('updated' => false, 'archived' => $modeArchived));
		foreach ($deleteFolders as $deleteFolder) {
			$logger->debug("deleteFolder: " . $deleteFolder->getName());
			$countDelete++;
			$em->remove($deleteFolder);
			$em->flush();
			$logger->debug("Folder deleted");
		}
		$logger->debug("all old db records removed");

		$logger->debug("Done (countNew: " . $countNew . " / countOld: " . $countOld . " / countDelete: " . $countDelete . ")");
		// return statistics
		return $this->toJsonResponse(array('n' => $countNew, 'o' => $countOld, 'd' => $countDelete));
    }

    /**
     * Finds next ToDo entry (Folder without stored Movie).
     *
     * @Route("/todo/next", name="folder_next_todo")
     */
    public function todoNextAction()
    {
	    $modeArchived = false;

	    $logger = $this->get('logger');
		$logger->debug("Check for ToDo Folders:");
	    		
        $em = $this->getDoctrine()->getEntityManager();
		$entity = $em->getRepository('MovieasterMovieManagerBundle:Movie')->findOneBy(array('found' => false, 'archived' => $modeArchived));
        if (!$entity) {
	        $logger->debug("No ToDo found!");
	        $values = array("i" => -1);
        } else {
	        $logger->debug("ToDo Folder found: " . $entity->getNameFolder());
	        $values = array("i" => $entity->getId(), "n" => $entity->getNameFolder());
        }        
		return $this->toJsonResponse($values);
    }    

    /**
     * refresh TmDB meta for a new folder.
     *
     * @Route("/{id}/download/meta", name="download_meta")
     */
    public function tmdbMetaAction($id)
    {
		$modeArchived = false;
			    
		$logger = $this->get('logger');
		$logger->debug("Get TmDB Meta Infos for Folder ID: " . $id);

		$em = $this->getDoctrine()->getEntityManager();

        $movie = $em->getRepository('MovieasterMovieManagerBundle:Movie')->find($id);
 		if($movie) {
	 		$logger->debug("Get TmDB Meta Infos for Folder: " . $movie->getNameFolder());
			$tmdbApi = TMDbFactory::createInstance();
			$logger->debug("TmDB API.");

	 		//add new movies
	 		preg_match('/(?P<name>\w+) \((?P<year>\d+)\)/', $movie->getNameFolder(), $movieFolderInfo);
			$moviesResult = $tmdbApi->searchMovie($movieFolderInfo["name"], 1, FALSE, $movieFolderInfo["year"], NULL);
			
			$logger->debug("TmDB movies result: ", $moviesResult);
			$tmdbId = $moviesResult["results"][0]["id"];
			
			$movieInfo = $tmdbApi->getMovie($tmdbId);
			if($movieInfo != "Nothing found." && $movieInfo["original_title"] != "") {
				$logger->debug("TmDB movies info: ", $movieInfo);					
				$movie->setFound(true);
				//create new movie record
				$movie->setName($movieInfo["title"]);
				$movie->setNameOriginal($movieInfo["original_title"]);
				if(array_key_exists("tagline", $movieInfo)) {
					$movie->setNameAlternative("".$movieInfo["tagline"]);    
				}
				$movie->setReleased(new \DateTime($movieInfo["release_date"]));
				if(array_key_exists("overview", $movieInfo)) {
					$movie->setOverview($movieInfo["overview"]);
				}
				if(array_key_exists("imdb_id", $movieInfo)) {
					$movie->setImdbId("".$movieInfo["imdb_id"]);
				}
				if(array_key_exists("id", $movieInfo)) {
					$movie->setTmdbId("".$movieInfo["id"]);
				}
				if(array_key_exists("homepage", $movieInfo)) {
					$movie->setHomepage($movieInfo["homepage"]);
				}
				if(array_key_exists("trailer", $movieInfo)) {
					$movie->setTrailer($movieInfo["trailer"]);
				}
				if(array_key_exists("vote_average", $movieInfo)) {
					$movie->setRatingTmdb("".$movieInfo["vote_average"]);
				}
				if(array_key_exists("vote_count", $movieInfo)) {
					$movie->setVotesTmdb("".$movieInfo["vote_count"]);
				}
				$movie->setThumbInline("");				
				
				$movieImages = $tmdbApi->getMovieImages($tmdbId);
				$imageFilePaths = array();
				$idx = 0;
				$imageFilePaths[$idx++] = $movieInfo["backdrop_path"];
				for($i=0;$i<count($movieImages["backdrops"])-1;$i++) {
					$imageFilePaths[$idx++] = $movieImages["backdrops"][$i]["file_path"];
				}
				for($i=0;$i<count($movieImages["posters"])-1;$i++) {
					$imageFilePaths[$idx++] = $movieImages["posters"][$i]["file_path"];
				}	
				$movie->setBackdrop1($tmdbApi->getImageUrl($imageFilePaths[0], TMDb::IMAGE_BACKDROP, "original"));			
				if($idx > 1) { 
					$movie->setBackdrop2($tmdbApi->getImageUrl($imageFilePaths[1], TMDb::IMAGE_BACKDROP, "original"));
				}
				if($idx > 2) { 
					$movie->setBackdrop3($tmdbApi->getImageUrl($imageFilePaths[2], TMDb::IMAGE_BACKDROP, "original"));
				}
				$thumbUrl = $tmdbApi->getImageUrl($movieInfo["poster_path"], TMDb::IMAGE_POSTER, "w92");
				$folderUrl = $tmdbApi->getImageUrl($movieInfo["poster_path"], TMDb::IMAGE_POSTER, "original");
				$movie->setThumb($thumbUrl);
				$movie->setPoster($folderUrl);
				
				$genres = $movie->getGenres();	
				for($i=0;$i<count($movieInfo["genres"])-1;$i++) {
					$name = $movieInfo["genres"][$i]["name"];
					if($genres != "") {
						$genres .= ", ";
					}
					$genres .= $name;
				}
				$movie->setGenres($genres);
				$actors = $movie->getActors();
				$directors = $movie->getDirectors();
				$writers = $movie->getWriters();
				
				$cast = $tmdbApi->getMovieCast($tmdbId);
				//print_r($cast);
				for($i=0;$i<count($cast["cast"])-1;$i++) {;
					if($i != 0) {
						$actors .= ", ";
					}
					$actors .= $cast["cast"][$i]["name"];
				}
				for($i=0;$i<count($cast["crew"])-1;$i++) {
					$name = $cast["crew"][$i]["name"];
					if($cast["crew"][$i]["job"] == "Director") {
						if($directors != "") {
							$directors .= ", ";
						}
						$directors .= $name;
					} else if($cast["crew"][$i]["job"] == "Editor") {
						if($writers != "") {
							$writers .= ", ";
						}
						$writers .= $name;
					}
				}
				$movie->setActors($actors);
				$movie->setDirectors($directors);
				$movie->setWriters($writers);
				$logger->debug("persist new Movie.");
										
				$em->persist($movie);
				$em->flush();
		        $values = array("f" => 1, "i" => $movie->getId(), "n" => $movie->getName());
			} else {
				$logger->debug("remove not found Movie folder: " . $movie->getNameFolder());
		        $values = array("f" => 0,  "e" => "TMDb not found", "n" => $movie->getNameFolder());
				$em->remove($movie);
				$em->flush();
			}
		} else {
			$logger->error("Fatal Error: DB enity " . $id . " not found");
			$values = array("f" => 0,  "e" => "Fatal Error: DB enity " . $id . " not found");
		}
		return $this->toJsonResponse($values);
    }    

    /**
     * download movie folder image thumbnail.
     *
     * @Route("/{id}/download/image/thumb", name="download_img_thumb")
     */
    public function downloadImgThumbAction($id)
    {
		$logger = $this->get('logger');
		$logger->debug("download Movie Thumb #id: " . $id);	
		$found = 0;
		$em = $this->getDoctrine()->getEntityManager();
		$movie = $this->loadMovie($id, $em);
		if($movie) {
			$logger->debug("download Movie Thumb: " . $movie->getName());	
			$imgUrl = $movie->getThumb();
			$logger->debug("download Movie Thumb imgUrl: " . $imgUrl);	
			if($imgUrl != "") {
				$content = file_get_contents($imgUrl); 
				if ($content !== false) {
					$movie->setThumbInline("data:image/" . substr($imgUrl, -3) . ";base64," . base64_encode($content));
					$em->flush();
					$found = 1;
				}
			}
		}
		$logger->debug("download Movie Thumb img found: " . $found);	
		return $this->toJsonResponse(array("f" => $found));
    }    

    /**
     * download movie folder image.
     *
     * @Route("/{id}/download/image/folder", name="download_img_folder")
     */
    public function downloadImgFolderAction($id)
    {
		$movie = $this->loadMovie($id, $this->getDoctrine()->getEntityManager());
		$found = $this->downloadImg($movie->getPoster(), $movie, 'folder.jpg');
		return $this->toJsonResponse(array("f" => $found)); 
    }    
    
    /**
     * download movie backdrop 1 image.
     *
     * @Route("/{id}/download/image/backdrop1", name="download_img_backdrop1")
     */
    public function downloadImgBackdrop1Action($id)     
    {     
		$movie = $this->loadMovie($id, $this->getDoctrine()->getEntityManager());
		$found = $this->downloadImg($movie->getBackdrop1(), $movie, 'backdrop.jpg');
		return $this->toJsonResponse(array("f" => $found)); 
    }

    /**
     * download movie backdrop 2 image.
     *
     * @Route("/{id}/download/image/backdrop2", name="download_img_backdrop2")
     */
    public function downloadImgBackdrop2Action($id)
    {     
		$movie = $this->loadMovie($id, $this->getDoctrine()->getEntityManager());
		$found = $this->downloadImg($movie->getBackdrop2(), $movie, 'backdrop1.jpg');
		return $this->toJsonResponse(array("f" => $found)); 
    }

    /**
     * download movie backdrop 3 image.
     *
     * @Route("/{id}/download/image/backdrop3", name="download_img_backdrop3")
     */
    public function downloadImgBackdrop3Action($id)
    {
		$movie = $this->loadMovie($id, $this->getDoctrine()->getEntityManager());
		$found = $this->downloadImg($movie->getBackdrop3(), $movie, 'backdrop2.jpg');
		return $this->toJsonResponse(array("f" => $found)); 
    }

    /**
     * download movie actors image.
     *
     * @Route("/download/image/actors", name="download_img_actors")
     */
    public function downloadImgActorsAction()
    { 
	    return new Response("TODO");
    }
    
    private function downloadImg($url, $movie, $filename)
    {
	    if($url == "") {
		    return 0;
	    }
		$targetPath = $movie->getPath()->getPath() . $movie->getName() . DIRECTORY_SEPARATOR  . $filename;
		if(!file_exists($targetPath)) {
			$content = file_get_contents($url); 
			if ($content !== false) {
				return file_put_contents($targetPath, $content);	
			}
		} else {
			return 1;
		}
		return 0;
	}
	
    private function loadMovie($id, $em)
    {
        $movie = $em->getRepository('MovieasterMovieManagerBundle:Movie')->find($id);
        if (!$movie) {
            throw $this->createNotFoundException('Unable to find Movie entity.');
        }
        return $movie;
	}
		
	private function toJsonResponse($data) {
		$response = new Response();
		$callbackFunction = $_REQUEST['callback']; //$request->query->get('callback');
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