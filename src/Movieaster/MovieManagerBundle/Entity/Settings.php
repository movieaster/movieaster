<?php

namespace Movieaster\MovieManagerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Settings
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Settings
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var boolean
     *
     * @ORM\Column(name="watched", type="boolean")
     */
    private $watched;

    /**
     * @var boolean
     *
     * @ORM\Column(name="favorites", type="boolean")
     */
    private $favorites;

    /**
     * @var boolean
     *
     * @ORM\Column(name="archive", type="boolean")
     */
    private $archive;

    /**
     * @var string
     *
     * @ORM\Column(name="archive_path", type="string", length=255)
     */
    private $archivePath;

    /**
     * @var boolean
     *
     * @ORM\Column(name="wishlist", type="boolean")
     */
    private $wishlist;


    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set watched
     *
     * @param boolean $watched
     * @return Settings
     */
    public function setWatched($watched)
    {
        $this->watched = $watched;
    
        return $this;
    }

    /**
     * Get watched
     *
     * @return boolean 
     */
    public function getWatched()
    {
        return $this->watched;
    }

    /**
     * Set favorites
     *
     * @param boolean $favorites
     * @return Settings
     */
    public function setFavorites($favorites)
    {
        $this->favorites = $favorites;
    
        return $this;
    }

    /**
     * Get favorites
     *
     * @return boolean 
     */
    public function getFavorites()
    {
        return $this->favorites;
    }

    /**
     * Set archive
     *
     * @param boolean $archive
     * @return Settings
     */
    public function setArchive($archive)
    {
        $this->archive = $archive;
    
        return $this;
    }

    /**
     * Get archive
     *
     * @return boolean 
     */
    public function getArchive()
    {
        return $this->archive;
    }

    /**
     * Set archivePath
     *
     * @param string $archivePath
     * @return Settings
     */
    public function setArchivePath($archivePath)
    {
        $this->archivePath = $archivePath;
    
        return $this;
    }

    /**
     * Get archivePath
     *
     * @return string 
     */
    public function getArchivePath()
    {
        return $this->archivePath;
    }

    /**
     * Set wishlist
     *
     * @param boolean $wishlist
     * @return Settings
     */
    public function setWishlist($wishlist)
    {
        $this->wishlist = $wishlist;
    
        return $this;
    }

    /**
     * Get wishlist
     *
     * @return boolean 
     */
    public function getWishlist()
    {
        return $this->wishlist;
    }
}