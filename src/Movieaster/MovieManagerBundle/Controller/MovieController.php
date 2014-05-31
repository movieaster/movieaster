<?php

namespace Movieaster\MovieManagerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;
use Movieaster\MovieManagerBundle\Component\JSONUtil;

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
        $query = "SELECT m.id, m.archived, m.watched, m.favorites"
                . " FROM MovieasterMovieManagerBundle:Movie m WHERE m.found=:found";
        $movies = $em->createQuery($query)->setParameter('found', true)->execute();
        $values = $this->toStatusMap($movies);
        return JSONUtil::createJsonResponse($values);
    }

    /**
     * Get All Movie Infos for the requested Movies.
     *
     * @Route("/infos", name="movie_infos")
     */
    public function infosAction() {
        $ids = explode(",", $_REQUEST['ids']);
        $movies = $this->getMovieRepository()->findBy(array('id' => $ids));
        $values = array();
        foreach($movies as $movie) {
            $values[] = $this->entityToJson($movie);
        }
        return JSONUtil::createJsonResponse($values);
    }

    /**
     * Finds and displays a Movie entity.
     *
     * @Route("/{id}/json", name="movie_json")
     */
    public function jsonAction($id) {
        $movie = $this->loadMovie($id);
        return JSONUtil::createJsonResponse($this->entityToJson($movie));
    }
    
    /**
     * Switch watched flag action.
     *
     * @Route("/{id}/switch/watched", name="switch_watched_flag")
     */
    public function switchWatchedAction($id) {     
        //TODO: move to watched folder configured
        $movie = $this->loadMovie($id);
        $movie->setWatched(!$movie->getWatched());
        $this->update($movie);
        return $this->idsAction();
    }       

    /**
     * Switch favorites flag action.
     *
     * @Route("/{id}/switch/favorites", name="switch_favorites_flag")
     */
    public function switchFavoritesAction($id) {
        //TODO: create symlink in configured folder
        $movie = $this->loadMovie($id);
        $movie->setFavorites(!$movie->getFavorites());
        $this->update($movie);
        return $this->idsAction();
    }
    
    /**
     * Switch archived flag action.
     *
     * @Route("/{id}/switch/archived", name="switch_archived_flag")
     */
    public function switchArchivedAction($id) {
        //TODO: move to configured archive folder/drive        
        $movie = $this->loadMovie($id);
        $movie->setArchived(!$movie->getArchived());
        $movie->setWatched(false);
        if ($movie->getFavorites()) {
            //TODO: remove favorites symlink
            $movie->setFavorites(false);
        }
        $this->update($movie);
        return $this->idsAction();
    }    

    private function getMovieRepository() {
        $em = $this->getDoctrine()->getEntityManager();    
        return $em->getRepository('MovieasterMovieManagerBundle:Movie');    
    }
    
    private function update($movie) {
        $em = $this->getDoctrine()->getEntityManager();
        $em->flush();
    }
    
    private function loadMovie($id) {
        $repo = $this->getMovieRepository();
        $movie = $repo->find($id);
        if(!$movie) {
            throw $this->createNotFoundException('Unable to find Movie entity.');
        }
        return $movie;
    }

    private function toStatusMap($movies) {
        $statusMap = array();
        $statusMap["newest"] = array();
        $statusMap["watched"] = array();
        $statusMap["favorites"] = array();
        $statusMap["archived"] = array();
        foreach($movies as $movie) {
            $movieId = $movie["id"];
            if($movie["archived"]) {
                $statusMap["archived"][] = $movieId;
            } else if($movie["watched"]) {
                $statusMap["watched"][] = $movieId;
            } else {
                $statusMap["newest"][] = $movieId;
            }
            // special folder with symbolic links
            if($movie["favorites"]) {
                $statusMap["favorites"][] = $movieId;
            }
        }
        return $statusMap;
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
        $values["tr"] = str_replace("http://www.youtube.com/watch?v=",
                                    "http://www.youtube-nocookie.com/embed/", $entity->getTrailer());
        $values["b1"] = $entity->getBackdrop1();
        $values["b2"] = $entity->getBackdrop2();
        $values["b3"] = $entity->getBackdrop3();
        $values["p"] = $entity->getPath()->getName();
        return $values; 
    }

}