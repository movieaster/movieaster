<?php

namespace Movieaster\MovieManagerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;


/**
 * Movie controller.
 *
 * @Route("/movie")
 */
class MovieController extends Controller {
	
    /**
     * Placeholder for root path.
     *
     * @Route("/", name="movie")
     */
    public function indexAction() {
	    return new Response("not in use");
    }
    
    /**
     * Lists all Movie ids.
     *
     * @Route("/ids", name="movie_ids")
     */
    public function idsAction() {
        $em = $this->getDoctrine()->getEntityManager();
		//$movies = $em->getRepository('MovieasterMovieManagerBundle:Movie')->findBy(array('found' => true), array('id' => 'DESC'));
	    $result = $em->createQuery('SELECT m.id, m.archived, m.watched, m.favorites FROM MovieasterMovieManagerBundle:Movie m WHERE m.found=:found')->setParameter('found', true)->execute();

        $values = array();
        $values["newest"] = array();
        $values["watched"] = array();
        $values["favorites"] = array();
        $values["archived"] = array();
        foreach($result as $movie) {
			if($movie["archived"]) {
				$values["archived"][] = $movie["id"];
			} else if($movie["watched"]) {
				$values["watched"][] = $movie["id"];
			} else {
				$values["newest"][] = $movie["id"];
			}
			// special folder with symbolic links
			if($movie["favorites"]) {
				$values["favorites"][] = $movie["id"];
			}
		}        
		return $this->toJsonResponse($values);
    }    

    /**
     * Get All Movie Infos for the requested Movies.
     *
     * @Route("/infos", name="movie_infos")
     */
    public function infosAction() {
        $ids = explode(",", $_REQUEST['ids']);//$request->query->get('ids'));
        $em = $this->getDoctrine()->getEntityManager();	
		$repo = $em->getRepository('MovieasterMovieManagerBundle:Movie');	
		$movies = $repo->findBy(array('id' => $ids));
		$values = array();
	    foreach($movies as $movie) {
			$values[] = $this->entityToJson($movie);
		}
		return $this->toJsonResponse($values);
    }

    /**
     * Finds and displays a Movie entity.
     *
     * @Route("/{id}/json", name="movie_json")
     */
    public function jsonAction($id) {
        $movie = $this->loadMovie($id, $this->getDoctrine()->getEntityManager());
        return $this->toJsonResponse($this->entityToJson($movie));
    }
    
    /**
     * Switch watched flag action.
     *
     * @Route("/{id}/switch/watched", name="switch_watched_flag")
     */
    public function switchWatchedAction($id) {     
        $em = $this->getDoctrine()->getEntityManager();
	    $movie = $this->loadMovie($id, $em);
        
        //TODO: move to watched folder configured
        
		$movie->setWatched(!$movie->getWatched());
		$em->flush();
		
        return $this->idsAction();
    }       

    /**
     * Switch favorites flag action.
     *
     * @Route("/{id}/switch/favorites", name="switch_favorites_flag")
     */
    public function switchFavoritesAction($id) {     
        $em = $this->getDoctrine()->getEntityManager();
	    $movie = $this->loadMovie($id, $em);
        
        //TODO: create symlink in configured folder
        
		$movie->setFavorites(!$movie->getFavorites());
		$em->flush();
		
        return $this->idsAction();
    }
    
    /**
     * Switch archived flag action.
     *
     * @Route("/{id}/switch/archived", name="switch_archived_flag")
     */
    public function switchArchivedAction($id) {
		$em = $this->getDoctrine()->getEntityManager();
        $movie = $this->loadMovie($id, $em);
        
        //TODO: move to configured archive folder/drive        
		$movie->setArchived(!$movie->getArchived());
		$movie->setWatched(false);
		if($movie->getFavorites()) {
			//remove favorites symlink
			$movie->setFavorites(false);
		}
		$em->flush();
		
        return $this->idsAction();
    }    

    private function loadMovie($id, $em) {
        $movie = $em->getRepository('MovieasterMovieManagerBundle:Movie')->find($id);
        if (!$movie) {
            throw $this->createNotFoundException('Unable to find Movie entity.');
        }
        return $movie;
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
		$countBackdrops = 0;
		if($entity->getBackdrop1() != "") {
			$countBackdrops++;
		}
		if($entity->getBackdrop2() != "") {
			$countBackdrops++;
		}
		if($entity->getBackdrop2() != "") {
			$countBackdrops++;
		}
        $values["b"] = $countBackdrops;
        $values["p"] = $entity->getPath()->getName();
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