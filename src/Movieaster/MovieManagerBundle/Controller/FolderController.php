<?php

namespace Movieaster\MovieManagerBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
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
			$tmdbYAML = TMDbFactory::createYAML();
			$logger->debug("TmDB YAML API.");

	 		//add new movies
			$moviesResultString = $tmdbYAML->searchMovie($movie->getNameFolder(), TMDb::JSON);
			$moviesResult = json_decode($moviesResultString, true);
			
			$logger->debug("TmDB movies result: ", $moviesResult);
			
			$tmdID = $moviesResult['0']['id'];
			$movieInfoString = $tmdbYAML->getMovie($tmdID, TMDb::TMDB, TMDb::JSON);
			$movieInfo = json_decode($movieInfoString, true);
					
			if(count($movieInfo) >= 0 && $movieInfo[0] != "Nothing found." && $movieInfo[0]["original_name"] != "") {
				$logger->debug("TmDB movies info: ", $movieInfo);					
				$movie->setFound(true);
				$movieInfo = $movieInfo[0];
				//create new movie record
				$movie->setName("".$movieInfo["name"]);
				$movie->setNameOriginal("".$movieInfo["original_name"]);
				$movie->setNameAlternative("".$movieInfo["alternative_name"]);    
				$movie->setReleased(new \DateTime($movieInfo["released"]));
				$movie->setOverview("".$movieInfo["overview"]);
				$movie->setImdbId("".$movieInfo["imdb_id"]);
				$movie->setTmdbId("".$movieInfo["id"]);
				$movie->setHomepage("".$movieInfo["homepage"]);
				$movie->setTrailer("".$movieInfo["trailer"]);
				$movie->setRatingTmdb("".$movieInfo["rating"]);
				$movie->setVotesTmdb("".$movieInfo["votes"]);
				$movie->setThumbInline("");				
				$thumbUrl="";
				$folderUrl="";
				$backdropUrls=array();
				for($i=0;$i<count($movieInfo["posters"])-1;$i++) {
					if($folderUrl == "" && $movieInfo["posters"][$i]["image"]["size"] == "original") {
						$folderUrl = $movieInfo["posters"][$i]["image"]["url"];
					}
					if($thumbUrl == "" && $movieInfo["posters"][$i]["image"]["size"] == "thumb") {
						$thumbUrl = $movieInfo["posters"][$i]["image"]["url"];
					}
				}
				$backdropUrls[0]="";
				$backdropUrls[1]="";
				$backdropUrls[2]="";
				for($i=0,$countBackdrops=0; $countBackdrops<4 && $i<count($movieInfo["backdrops"])-1; $i++) {
					if($movieInfo["backdrops"][$i]["image"]["size"] == "original") {
						$backdropUrls[$countBackdrops]= $movieInfo["backdrops"][$i]["image"]["url"];
						$countBackdrops++;								
					}
				}
				$movie->setThumb($thumbUrl);
				$movie->setPoster($folderUrl);
				$movie->setBackdrop1($backdropUrls[0]);
				$movie->setBackdrop2($backdropUrls[1]);
				$movie->setBackdrop3($backdropUrls[2]);
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
				for($i=0;$i<count($movieInfo["cast"])-1;$i++) {
					$name = $movieInfo["cast"][$i]["name"];
					if($movieInfo["cast"][$i]["job"] == "Actor" && $movieInfo["cast"][$i]["profile"] != "") {
						if($actors != "") {
							$actors .= ", ";
						}
						$actors .= $name;
					} else if($movieInfo["cast"][$i]["job"] == "Director") {
						if($directors != "") {
							$directors .= ", ";
						}
						$directors .= $name;
					} else if($movieInfo["cast"][$i]["job"] == "Editor") {
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
	    return 0;
	    /*
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
		*/
	}
	
    private function loadMovie($id, $em)
    {
        $movie = $em->getRepository('MovieasterMovieManagerBundle:Movie')->find($id);
        if (!$movie) {
            throw $this->createNotFoundException('Unable to find Movie entity.');
        }
        return $movie;
	}
		
	private function toJsonResponse($data)
	{
		$response = new JsonResponse();
		$response->setData($data);
		$callbackFunction = $_REQUEST['callback']; //$request->query->get('callback');
		if($callbackFunction != null) {
			$response->setCallback($callbackFunction);
		}
		return $response;
	}	
}