<?php

namespace Movieaster\MovieManagerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Wishlist
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Wishlist
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
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var string $nameOriginal
     *
     * @ORM\Column(name="original_name", type="string", length=255)
     */
    private $nameOriginal;
    
    /**
     * @var string $nameAlternative
     *
     * @ORM\Column(name="alternative_name", type="string", length=255)
     */
    private $nameAlternative;    

    /**
     * @var date $released
     *
     * @ORM\Column(name="released", type="date")
     */
    private $released;    
    
    /**
     * @var string $overview
     *
     * @ORM\Column(name="overview", type="string", length=1024)
     */
    private $overview;    
    
    /**
     * @var string $imdb
     *
     * @ORM\Column(name="imdb", type="string", length=100)
     */
    private $imdbId;    

    /**
     * @var string $tmdb
     *
     * @ORM\Column(name="tmdb", type="string", length=100)
     */
    private $tmdbId;    
    
    /**
     * @var string $homepage
     *
     * @ORM\Column(name="homepage", type="string", length=100)
     */
    private $homepage;        
    
    /**
     * @var string $trailer
     *
     * @ORM\Column(name="trailer", type="string", length=100)
     */
    private $trailer;        
    
    /**
     * @var string $ratingTmdb
     *
     * @ORM\Column(name="tmdb_rating", type="integer")
     */
    private $ratingTmdb;         

    /**
     * @var string $votesTmdb
     *
     * @ORM\Column(name="tmdb_votes", type="integer")
     */
    private $votesTmdb;         
    
    /**
     * @var string $genres
     *
     * @ORM\Column(name="genres", type="string", length=1024)
     */
    private $genres;

    /**
     * @var string $directors
     *
     * @ORM\Column(name="directors", type="string", length=1024)
     */
    private $directors; 
    
    /**
     * @var string $writers
     *
     * @ORM\Column(name="writers", type="string", length=1024)
     */
    private $writers;    
        
    /**
     * @var string $actors
     *
     * @ORM\Column(name="actors", type="string", length=1024)
     */
    private $actors;
        
    /**
     * @var string $thumb
     *
     * @ORM\Column(name="thumb", type="string", length=100)
     */
    private $thumb;
    
    /**
     * @var string $thumbInline
     *
     * @ORM\Column(name="thumb_inline", type="text")
     */
    private $thumbInline;
    
    /**
     * @var string $poster
     *
     * @ORM\Column(name="poster", type="string", length=100)
     */
    private $poster;
        
    /**
     * @var string $backdrop1
     *
     * @ORM\Column(name="backdrop1", type="string", length=100)
     */
    private $backdrop1;

    /**
     * @var string $backdrop2
     *
     * @ORM\Column(name="backdrop2", type="string", length=100)
     */
    private $backdrop2;
    
    /**
     * @var string $backdrop3
     *
     * @ORM\Column(name="backdrop3", type="string", length=100)
     */
    private $backdrop3;
    
    public function __construct() {
        //$this->genres = new \Doctrine\Common\Collections\ArrayCollection();
        //$this->actors = new \Doctrine\Common\Collections\ArrayCollection();
        $this->name = "";
        $this->nameOriginal = "";
        $this->nameAlternative = "";
        $this->released = new \DateTime("0000-00-00");
        $this->overview = "";
        $this->imdbId = "";
        $this->tmdbId = "";    
        $this->homepage = "";        
        $this->trailer = "";        
        $this->ratingTmdb = 0;
        $this->votesTmdb = 0;         
        $this->genres = "";
        $this->directors = ""; 
        $this->writers = "";
        $this->actors = "";
        $this->thumb = "";
        $this->thumbInline = "";
        $this->poster = "";
        $this->backdrop1 = "";
        $this->backdrop2 = "";
        $this->backdrop3 = "";    }    
        
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
     * Set name
     *
     * @param string $name
     * @return Movie
     */
    public function setName($name)
    {
        $this->name = $name;
    
        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set original_name
     *
     * @param string $originalName
     * @return Movie
     */
    public function setOriginalName($originalName)
    {
        $this->original_name = $originalName;
    
        return $this;
    }

    /**
     * Get original_name
     *
     * @return string 
     */
    public function getOriginalName()
    {
        return $this->original_name;
    }

    /**
     * Set nameOriginal
     *
     * @param string $nameOriginal
     * @return Movie
     */
    public function setNameOriginal($nameOriginal)
    {
        $this->nameOriginal = $nameOriginal;
    
        return $this;
    }

    /**
     * Get nameOriginal
     *
     * @return string 
     */
    public function getNameOriginal()
    {
        return $this->nameOriginal;
    }

    /**
     * Set nameAlternative
     *
     * @param string $nameAlternative
     * @return Movie
     */
    public function setNameAlternative($nameAlternative)
    {
        $this->nameAlternative = $nameAlternative;
    
        return $this;
    }

    /**
     * Get nameAlternative
     *
     * @return string 
     */
    public function getNameAlternative()
    {
        return $this->nameAlternative;
    }

    /**
     * Set released
     *
     * @param \DateTime $released
     * @return Movie
     */
    public function setReleased($released)
    {
        $this->released = $released;
    
        return $this;
    }

    /**
     * Get released
     *
     * @return \DateTime 
     */
    public function getReleased()
    {
        return $this->released;
    }

    /**
     * Set overview
     *
     * @param string $overview
     * @return Movie
     */
    public function setOverview($overview)
    {
        $this->overview = $overview;
    
        return $this;
    }

    /**
     * Get overview
     *
     * @return string 
     */
    public function getOverview()
    {
        return $this->overview;
    }

    /**
     * Set imdbId
     *
     * @param string $imdbId
     * @return Movie
     */
    public function setImdbId($imdbId)
    {
        $this->imdbId = $imdbId;
    
        return $this;
    }

    /**
     * Get imdbId
     *
     * @return string 
     */
    public function getImdbId()
    {
        return $this->imdbId;
    }

    /**
     * Set tmdbId
     *
     * @param string $tmdbId
     * @return Movie
     */
    public function setTmdbId($tmdbId)
    {
        $this->tmdbId = $tmdbId;
    
        return $this;
    }

    /**
     * Get tmdbId
     *
     * @return string 
     */
    public function getTmdbId()
    {
        return $this->tmdbId;
    }

    /**
     * Set homepage
     *
     * @param string $homepage
     * @return Movie
     */
    public function setHomepage($homepage)
    {
        $this->homepage = $homepage;
    
        return $this;
    }

    /**
     * Get homepage
     *
     * @return string 
     */
    public function getHomepage()
    {
        return $this->homepage;
    }

    /**
     * Set trailer
     *
     * @param string $trailer
     * @return Movie
     */
    public function setTrailer($trailer)
    {
        $this->trailer = $trailer;
    
        return $this;
    }

    /**
     * Get trailer
     *
     * @return string 
     */
    public function getTrailer()
    {
        return $this->trailer;
    }

    /**
     * Set ratingTmdb
     *
     * @param integer $ratingTmdb
     * @return Movie
     */
    public function setRatingTmdb($ratingTmdb)
    {
        $this->ratingTmdb = $ratingTmdb;
    
        return $this;
    }

    /**
     * Get ratingTmdb
     *
     * @return integer 
     */
    public function getRatingTmdb()
    {
        return $this->ratingTmdb;
    }

    /**
     * Set votesTmdb
     *
     * @param integer $votesTmdb
     * @return Movie
     */
    public function setVotesTmdb($votesTmdb)
    {
        $this->votesTmdb = $votesTmdb;
    
        return $this;
    }

    /**
     * Get votesTmdb
     *
     * @return integer 
     */
    public function getVotesTmdb()
    {
        return $this->votesTmdb;
    }

    /**
     * Set genres
     *
     * @param string $genres
     * @return Movie
     */
    public function setGenres($genres)
    {
        $this->genres = $genres;
    
        return $this;
    }

    /**
     * Get genres
     *
     * @return string 
     */
    public function getGenres()
    {
        return $this->genres;
    }

    /**
     * Set directors
     *
     * @param string $directors
     * @return Movie
     */
    public function setDirectors($directors)
    {
        $this->directors = $directors;
    
        return $this;
    }

    /**
     * Get directors
     *
     * @return string 
     */
    public function getDirectors()
    {
        return $this->directors;
    }

    /**
     * Set writers
     *
     * @param string $writers
     * @return Movie
     */
    public function setWriters($writers)
    {
        $this->writers = $writers;
    
        return $this;
    }

    /**
     * Get writers
     *
     * @return string 
     */
    public function getWriters()
    {
        return $this->writers;
    }

    /**
     * Set actors
     *
     * @param string $actors
     * @return Movie
     */
    public function setActors($actors)
    {
        $this->actors = $actors;
    
        return $this;
    }

    /**
     * Get actors
     *
     * @return string 
     */
    public function getActors()
    {
        return $this->actors;
    }

    /**
     * Set thumb
     *
     * @param string $thumb
     * @return Movie
     */
    public function setThumb($thumb)
    {
        $this->thumb = $thumb;
    
        return $this;
    }

    /**
     * Get thumb
     *
     * @return string 
     */
    public function getThumb()
    {
        return $this->thumb;
    }

    /**
     * Set thumbInline
     *
     * @param string $thumbInline
     * @return Movie
     */
    public function setThumbInline($thumbInline)
    {
        $this->thumbInline = $thumbInline;
    
        return $this;
    }

    /**
     * Get thumbInline
     *
     * @return string 
     */
    public function getThumbInline()
    {
        return $this->thumbInline;
    }

    /**
     * Set poster
     *
     * @param string $poster
     * @return Movie
     */
    public function setPoster($poster)
    {
        $this->poster = $poster;
    
        return $this;
    }

    /**
     * Get poster
     *
     * @return string 
     */
    public function getPoster()
    {
        return $this->poster;
    }

    /**
     * Set backdrop1
     *
     * @param string $backdrop1
     * @return Movie
     */
    public function setBackdrop1($backdrop1)
    {
        $this->backdrop1 = $backdrop1;
    
        return $this;
    }

    /**
     * Get backdrop1
     *
     * @return string 
     */
    public function getBackdrop1()
    {
        return $this->backdrop1;
    }

    /**
     * Set backdrop2
     *
     * @param string $backdrop2
     * @return Movie
     */
    public function setBackdrop2($backdrop2)
    {
        $this->backdrop2 = $backdrop2;
    
        return $this;
    }

    /**
     * Get backdrop2
     *
     * @return string 
     */
    public function getBackdrop2()
    {
        return $this->backdrop2;
    }

    /**
     * Set backdrop3
     *
     * @param string $backdrop3
     * @return Movie
     */
    public function setBackdrop3($backdrop3)
    {
        $this->backdrop3 = $backdrop3;
    
        return $this;
    }

    /**
     * Get backdrop3
     *
     * @return string 
     */
    public function getBackdrop3()
    {
        return $this->backdrop3;
    }

}