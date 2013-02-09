<?php

namespace Movieaster\MovieManagerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Movieaster\MovieManagerBundle\Component\TMDb\TMDb;
use Movieaster\MovieManagerBundle\Component\TMDb\TMDbFactory;
use Movieaster\MovieManagerBundle\Entity\Wishlist;

/**
 * Wishlist controller.
 *
 * @Route("/wishlist")
 */
class WishlistController extends Controller
{
	
    /**
     * Placeholder for root path.
     *
     * @Route("/", name="wishlist")
     */
    public function indexAction()
    {
	    return new Response("not in use");
    }
    
    /**
     * Lists all Wishlist Movie ids.
     *
     * @Route("/ids", name="wishlist_movie_ids")
     */
    public function idsAction()
    {
        $em = $this->getDoctrine()->getEntityManager();
	    $result = $em->createQuery('SELECT m.id FROM MovieasterMovieManagerBundle:Wishlist m')->execute();
        $values = array();
        foreach($result as $movie) {
			$values[] = $movie["id"];
		}        
		return $this->toJsonResponse($values);
    }    

    /**
     * Get All Movie Infos for the requested Movies.
     *
     * @Route("/infos", name="whislist_movie_infos")
     */
    public function infosAction()
    {
        $ids = explode(",", $_REQUEST['ids']);//$request->query->get('ids'));
        $em = $this->getDoctrine()->getEntityManager();	
		$repo = $em->getRepository('MovieasterMovieManagerBundle:Wishlist');	
		$movies = $repo->findBy(array('id' => $ids));
		$values = array();
	    foreach($movies as $movie) {
			$values[] = $this->entityToJson($movie);
		}
		return $this->toJsonResponse($values);
    }
    
    /**
     * Remove a Wishlist entity.
     *
     * @Route("/{id}/remove", name="wishlist_remove")
     */
    public function removeAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $entity = $em->getRepository('MovieasterMovieManagerBundle:Wishlist')->find($id);
        if(!$entity) {
            throw $this->createNotFoundException('Unable to find Wishlist entity.');
        }
        $em->remove($entity);
        $em->flush();       
		return $this->idsAction();
    }    
    
    /**
     * Finds and displays a Folder entity.
     *
     * @Route("/tmdb_query", name="wishlist_tmdb_search_query")
     */
    public function tndbQueryAction()
    {
	    $request = $this->getRequest();
	    $query = $request->query->get('term');

		$tmdbYAML = TMDbFactory::createYAML();
				
		$moviesResultString = $tmdbYAML->searchMovie($query, TMDb::JSON);
		$moviesResult = json_decode($moviesResultString, true);
		$values = array();
	    foreach ($moviesResult as &$value) {
		    $tmdID = $value['id'];
			$nameLocalized = $value['name'];
			$nameOriginal = $value['original_name'];
			$value = $nameLocalized;
			$label = $value;
			if($label != $nameOriginal) {
				$label .= " / " . $nameOriginal;
			}
		    $values[] = array('id' => $tmdID, 'label' => $label, 'value' => $value);
	    }
		return $this->toJsonResponse($values);
    }    

    /**
     * create new wishlist item.
     *
     * @Route("/{id}/create", name="create_wishlist_item")
     */
    public function tmdbCreateAction($id)
    {
		$logger = $this->get('logger');
		$logger->debug("==========>Get Meta Infos for TMDb ID: " . $id);
	    
        $em = $this->getDoctrine()->getEntityManager();

		$tmdbYAML = TMDbFactory::createYAML();
		$movieInfoString = $tmdbYAML->getMovie($id, TMDb::TMDB,TMDb::JSON);
		$movieInfo = json_decode($movieInfoString, true);
				
		if(count($movieInfo) >= 0 && $movieInfo[0] != "Nothing found." && $movieInfo[0]["original_name"] != "") {
			$logger->debug("==========>TMDb movies info: ", $movieInfo);					
			$movieInfo = $movieInfo[0];
			//create new movie record
			$wishlist = new Wishlist();
			$wishlist->setName("".$movieInfo["name"]);
			$wishlist->setNameOriginal("".$movieInfo["original_name"]);
			$wishlist->setNameAlternative("".$movieInfo["alternative_name"]);    
			$wishlist->setReleased(new \DateTime($movieInfo["released"]));
			$wishlist->setOverview("".$movieInfo["overview"]);
			$wishlist->setImdbId("".$movieInfo["imdb_id"]);
			$wishlist->setTmdbId("".$movieInfo["id"]);
			$wishlist->setHomepage("".$movieInfo["homepage"]);
			$wishlist->setTrailer("".$movieInfo["trailer"]);
			$wishlist->setRatingTmdb("".$movieInfo["rating"]);
			$wishlist->setVotesTmdb("".$movieInfo["votes"]);
			$genres = $wishlist->getGenres();	
			for($i=0;$i<count($movieInfo["genres"])-1;$i++) {
				$name = $movieInfo["genres"][$i]["name"];
				if($genres != "") {
					$genres .= ", ";
				}
				$genres .= $name;
			}
			$wishlist->setGenres($genres);
			$actors = $wishlist->getActors();
			$directors = $wishlist->getDirectors();
			$writers = $wishlist->getWriters();
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
			$wishlist->setActors($actors);
			$wishlist->setDirectors($directors);
			$wishlist->setWriters($writers);
			$wishlist->setThumbInline("");				
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
			$wishlist->setThumb($thumbUrl);
			$wishlist->setPoster($folderUrl);
			$wishlist->setBackdrop1($backdropUrls[0]);
			$wishlist->setBackdrop2($backdropUrls[1]);
			$wishlist->setBackdrop3($backdropUrls[2]);
			
			$imgUrl = $wishlist->getThumb();
			if($imgUrl != "") {
				$content = file_get_contents($imgUrl); 
				if ($content !== false) {
					$wishlist->setThumbInline("data:image/" . substr($imgUrl, -3) . ";base64," . base64_encode($content));
					$em->flush();
					$found = 1;
				}
			}
			
			$em->persist($wishlist);
			$em->flush();			
    	}
    	return $this->idsAction();
	}	
	        
	private function entityToJson($entity) {
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
        $values["tr"] = str_replace("http://www.youtube.com/watch?v=", "http://www.youtube-nocookie.com/embed/", $entity->getTrailer());
        $values["b"] = -1;
        $values["b1"] = $entity->getBackdrop1();
        $values["b2"] = $entity->getBackdrop2();
        $values["b3"] = $entity->getBackdrop3();
		return $values; 
	}
	
	private function toJsonResponse($data) {
		$response = new JsonResponse();
		$response->setData($data);
		$callbackFunction = $_REQUEST['callback']; //$request->query->get('callback');
		if($callbackFunction != null) {
			$response->setCallback($callbackFunction);
		}
		return $response;
	}
    
}