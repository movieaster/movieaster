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
use Movieaster\MovieManagerBundle\Component\JSONUtil;

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
		return JSONUtil::createJsonResponse(array('n' => $countNew, 'o' => $countOld, 'd' => $countDelete));
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
		return JSONUtil::createJsonResponse($values);
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
		$movie = $this->loadMovie($id);
		$logger->debug("Get TmDB Meta Infos for Folder: " . $movie->getNameFolder());
		
		// parse folder using the naming convention: "Movie Name (Year)"
		if(preg_match('/(?P<name>\w+) \((?P<year>\d+)\)/', $movie->getNameFolder(), $movieFolderInfo)) {
			$moviesResult = TMDbFactory::createInstance()->searchMovie($movieFolderInfo["name"], 1, FALSE, $movieFolderInfo["year"], NULL);
		} else {
			$moviesResult = TMDbFactory::createInstance()->searchMovie($movie->getNameFolder());
		}
		if(count($moviesResult["results"]) == 0) {
			$logger->debug("remove not found Movie folder: " . $movie->getNameFolder());
			$em->remove($movie);
			$em->flush();
			return JSONUtil::createJsonResponse(array("f" => 0, "e" => "TMDb not found", "n" => $movie->getNameFolder()));
		}
		$tmdbId = $moviesResult["results"][0]["id"];
		$movieInfo = TMDbFactory::createMovieInfoById($tmdbId);
		$movie->setFound(true);
		$movie->setName($movieInfo->name);
		$movie->setNameOriginal($movieInfo->nameOriginal);
		$movie->setNameAlternative($movieInfo->nameAlternative);
		$movie->setReleased($movieInfo->released);
		$movie->setOverview($movieInfo->overview);
		$movie->setImdbId($movieInfo->imdbId);
		$movie->setTmdbId($movieInfo->tmdbId);
		$movie->setHomepage($movieInfo->homepage);
		$movie->setTrailer($movieInfo->trailer);
		$movie->setRatingTmdb($movieInfo->ratingTmdb);
		$movie->setVotesTmdb($movieInfo->votesTmdb);
		$movie->setGenres($movieInfo->genres);
		$movie->setActors($movieInfo->actors);
		$movie->setDirectors($movieInfo->directors);
		$movie->setWriters($movieInfo->writers);
		$movie->setThumbInline($movieInfo->thumbInline);
		$movie->setThumb($movieInfo->thumb);
		$movie->setPoster($movieInfo->poster);
		$movie->setBackdrop1($movieInfo->backdrop1);
		$movie->setBackdrop2($movieInfo->backdrop2);
		$movie->setBackdrop3($movieInfo->backdrop3);
		$em->persist($movie);
		$em->flush();
		return JSONUtil::createJsonResponse(array("f" => 1, "i" => $movie->getId(), "n" => $movie->getName()));
	}

	/**
	 * download movie folder image thumbnail.
	 *
	 * @Route("/{id}/download/image/thumb", name="download_img_thumb")
	 */
	public function downloadImgThumbAction($id)
	{
		//TODO: remove methode
		return JSONUtil::createJsonResponse(array("f" => true));
	}

	/**
	 * download movie folder image.
	 *
	 * @Route("/{id}/download/image/folder", name="download_img_folder")
	 */
	public function downloadImgFolderAction($id)
	{
		$movie = $this->loadMovie($id);
		$found = $this->downloadImg($movie->getPoster(), $movie, 'folder.jpg');
		return JSONUtil::createJsonResponse(array("f" => $found));
	}
	
	/**
	 * download movie backdrop 1 image.
	 *
	 * @Route("/{id}/download/image/backdrop1", name="download_img_backdrop1")
	 */
	public function downloadImgBackdrop1Action($id)	 
	{	 
		$movie = $this->loadMovie($id);
		$found = $this->downloadImg($movie->getBackdrop1(), $movie, 'backdrop.jpg');
		return JSONUtil::createJsonResponse(array("f" => $found));
	}
	
	/**
	 * download movie backdrop 2 image.
	 *
	 * @Route("/{id}/download/image/backdrop2", name="download_img_backdrop2")
	 */
	public function downloadImgBackdrop2Action($id)
	{ 
		$movie = $this->loadMovie($id);
		$found = $this->downloadImg($movie->getBackdrop2(), $movie, 'backdrop1.jpg');
		return JSONUtil::createJsonResponse(array("f" => $found));
	}

	/**
	 * download movie backdrop 3 image.
	 *
	 * @Route("/{id}/download/image/backdrop3", name="download_img_backdrop3")
	 */
	public function downloadImgBackdrop3Action($id)
	{
		$movie = $this->loadMovie($id);
		$found = $this->downloadImg($movie->getBackdrop3(), $movie, 'backdrop2.jpg');
		return JSONUtil::createJsonResponse(array("f" => $found));
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
	
	private function loadMovie($id)
	{
		$em = $this->getDoctrine()->getEntityManager();
		$movie = $em->getRepository('MovieasterMovieManagerBundle:Movie')->find($id);
		if (!$movie) {
			throw $this->createNotFoundException('Unable to find Movie entity.');
		}
		return $movie;
	}

}