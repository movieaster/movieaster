<?php

namespace Movieaster\MovieManagerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;
use Movieaster\MovieManagerBundle\Component\TMDb\TMDb;
use Movieaster\MovieManagerBundle\Component\TMDb\TMDbFactory;
use Movieaster\MovieManagerBundle\Entity\Wishlist;
use Movieaster\MovieManagerBundle\Component\JSONUtil;

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
        return JSONUtil::createJsonResponse($values);
    }    

    /**
     * Get All Movie Infos for the requested Movies.
     *
     * @Route("/infos", name="whislist_movie_infos")
     */
    public function infosAction()
    {
        $ids = explode(",", $_REQUEST['ids']);
        $em = $this->getDoctrine()->getEntityManager();    
        $repo = $em->getRepository('MovieasterMovieManagerBundle:Wishlist');    
        $movies = $repo->findBy(array('id' => $ids));
        $values = array();
        foreach($movies as $movie) {
            $values[] = $this->entityToJson($movie);
        }
        return JSONUtil::createJsonResponse($values);
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
    public function tmdbQueryAction()
    {
        $request = $this->getRequest();
        $query = $request->query->get('term');
        $tmdbResult = TMDbFactory::createInstance()->searchMovie($query);       
        $values = array();
        foreach ($tmdbResult["results"] as $movie) {
            $tmdID = $movie['id'];
            $nameLocalized = $movie['title'];
            $nameOriginal = $movie['original_title'];
            $value = $nameLocalized;
            $label = $value;
            if($label != $nameOriginal) {
                $label .= " / " . $nameOriginal;
            }
            $values[] = array('id' => $tmdID, 'label' => $label, 'value' => $value);
        }
        return JSONUtil::createJsonResponse($values);
    }    

    /**
     * create new wishlist item.
     *
     * @Route("/{id}/create", name="create_wishlist_item")
     */
    public function tmdbCreateAction($id)
    {
        $movieInfo = TMDbFactory::createMovieInfoById($id);
        $wishlist = new Wishlist();
        $wishlist->setName($movieInfo->name);
        $wishlist->setNameOriginal($movieInfo->nameOriginal);
        $wishlist->setNameAlternative($movieInfo->nameAlternative);
        $wishlist->setReleased($movieInfo->released);
        $wishlist->setOverview($movieInfo->overview);
        $wishlist->setImdbId($movieInfo->imdbId);
        $wishlist->setTmdbId($movieInfo->tmdbId);
        $wishlist->setHomepage($movieInfo->homepage);
        $wishlist->setTrailer($movieInfo->trailer);
        $wishlist->setRatingTmdb($movieInfo->ratingTmdb);
        $wishlist->setVotesTmdb($movieInfo->votesTmdb);
        $wishlist->setGenres($movieInfo->genres);
        $wishlist->setActors($movieInfo->actors);
        $wishlist->setDirectors($movieInfo->directors);
        $wishlist->setWriters($movieInfo->writers);
        $wishlist->setThumbInline($movieInfo->thumbInline);
        $wishlist->setThumb($movieInfo->thumb);
        $wishlist->setPoster($movieInfo->poster);
        $wishlist->setBackdrop1($movieInfo->backdrop1);
        $wishlist->setBackdrop2($movieInfo->backdrop2);
        $wishlist->setBackdrop3($movieInfo->backdrop3);
        $em = $this->getDoctrine()->getEntityManager();
        $em->persist($wishlist);
        $em->flush();            
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
        $values["tr"] = str_replace("http://www.youtube.com/watch?v=",
                                    "http://www.youtube-nocookie.com/embed/", $entity->getTrailer());
        $values["b1"] = $entity->getBackdrop1();
        $values["b2"] = $entity->getBackdrop2();
        $values["b3"] = $entity->getBackdrop3();
        return $values; 
    }

}