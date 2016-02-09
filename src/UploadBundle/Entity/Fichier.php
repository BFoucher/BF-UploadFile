<?php

namespace UploadBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;


/**
 * Fichier
 *
 * @ORM\Table(name="fichier")
 * @ORM\Entity(repositoryClass="UploadBundle\Repository\FichierRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Fichier
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="datetime")
     */
    private $date;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="path", type="string", length=255)
     */
    private $path;

    /**
     * @var string
     *
     * @ORM\Column(name="sha", type="string", length=255)
     */
    private $sha;

    /**
     * @var string
     *
     * @ORM\Column(name="ip", type="string", length=20)
     */
    private $ip;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="lifetime", type="datetime" )
     */
    private $lifetime;

    /**
     * @var bool
     *
     * @ORM\Column(name="active", type="boolean", nullable=true)
     */
    private $active;

    /**
     * @var int
     *
     * @ORM\Column(name="download", type="integer", nullable=false)
     */
    private $download;

    /**
     * @var bool
     *
     * @ORM\Column(name="onedownload", type="boolean", nullable=false)
     */
    private $onedownload;

    /**
     * @var int
     *
     * @ORM\Column(name="size", type="integer", nullable=true)
     */
    private $size;

    /**
     * @var string
     *
     * @ORM\Column(name="mimetype", type="string", length=255, nullable=true)
     */
    private $mimetype;

    /**
    * TmpDir
    */
    private $tmpDir;


    /**
     * TmpFile for Form
     */
    private $tmpFile;

    public function __construct(){
        $this->lifetime = 10;
        $this->active = 0;
        $this->download = 0;
        $this->onedownload = 0;
    }

    public function getTmpFile()
    {
        return $this->tmpFile;
    }

    public function setTmpFile(UploadedFile $tmpFile = null)
    {
        $this->tmpFile = $tmpFile;
    }


    public function preUpload($directory)
    {
        if (null === $this->tmpFile) {
            return false;
        }
        if ($this->tmpFile->isValid()!=true){
            return $this->tmpFile->getErrorMessage();
        }
        //On crée un fichier au nom aléa
        $nameAlea =tempnam($directory, "");
        $nameAlea = basename($nameAlea);

        //On stock le nom du fichier local
        $this->tmpDir = $directory;
        $this->path = $nameAlea;

        //On stock le nom et la taille du fichier original
        $this->name  = $this->tmpFile->getClientOriginalName();
        $this->size  = $this->tmpFile->getClientSize();
        $this->mimetype  = $this->tmpFile->getMimeType();
        $this->sha = hash_file('sha512',$this->tmpFile->getPath());

        return true;
    }

    public function deletTmpFile(){
        unlink($this->tmpDir.$this->path);
    }

    /**
     * @ORM\PostPersist()
     * @ORM\PostUpdate()
     */
    public function upload()
    {
        if (null === $this->tmpFile) {
            return;
        }
        $this->tmpFile->move($this->tmpDir, $this->path);
    }




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
     * Set date
     *
     * @param \DateTime $date
     * @return Fichier
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime 
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Fichier
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
     * Set sha
     *
     * @param string $sha
     * @return Fichier
     */
    public function setSha($sha)
    {
        $this->sha = $sha;

        return $this;
    }

    /**
     * Get sha
     *
     * @return string 
     */
    public function getSha()
    {
        return $this->sha;
    }

    /**
     * Set ip
     *
     * @param string $ip
     * @return Fichier
     */
    public function setIp($ip)
    {
        $this->ip = $ip;

        return $this;
    }

    /**
     * Get ip
     *
     * @return string 
     */
    public function getIp()
    {
        return $this->ip;
    }

    /**
     * Set lifetime
     *
     * @param \DateTime $lifetime
     * @return Fichier
     */
    public function setLifetime($lifetime)
    {
        $this->lifetime = $lifetime;

        return $this;
    }

    /**
     * Get lifetime
     *
     * @return \DateTime 
     */
    public function getLifetime()
    {
        return $this->lifetime;

    }

    /**
     * Get Expired
     *
     * @return \boolean
     */
    public function getExpired()
    {
        return($this->lifetime>new \DateTime()?true:false);

    }

    /**
     * Get lifetimeinterval
     *
     * @return \string
     */
    public function getLifetimeInterval()
    {
        $now = new \DateTime();
        $interval = $now->diff($this->getLifetime());

        if ( $v = $interval->y >= 1 ) return $interval->y . ' Annee';
        if ( $v = $interval->m >= 1 ) return $interval->m . ' Mois';
        if ( $v = $interval->d >= 1 ) return $interval->d . ' Jours';
        if ( $v = $interval->h >= 1 ) return $interval->h . ' Heures';
        if ( $v = $interval->i >= 1 ) return $interval->i . ' Minutes';
        return $interval->s . ' s';
    }

    /**
     * Set active
     *
     * @param boolean $active
     * @return Fichier
     */
    public function setActive($active)
    {
        $this->active = $active;

        return $this;
    }

    /**
     * Get active
     *
     * @return boolean
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * Set path
     *
     * @param string $path
     * @return Fichier
     */
    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Get path
     *
     * @return string 
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Set download
     *
     * @param integer $download
     * @return Fichier
     */
    public function setDownload($download)
    {
        $this->download = $download;

        return $this;
    }

    /**
     * Get download
     *
     * @return integer 
     */
    public function getDownload()
    {
        return $this->download;
    }

    /**
     * Set onedownload
     *
     * @param integer $onedownload
     * @return Fichier
     */
    public function setOnedownload($onedownload)
    {
        $this->onedownload = $onedownload;

        return $this;
    }

    /**
     * Get onedownload
     *
     * @return integer 
     */
    public function getOnedownload()
    {
        return $this->onedownload;
    }

    /**
     *  Isopen
     * return True if file can be downlaod
     * else return false
     */
    public function isOpen(){
        if ($this->getOnedownload()==1 && $this->getDownload()!=0){
            return false;
        }
        return true;
    }

    /**
     * Set size
     *
     * @param integer $size
     * @return Fichier
     */
    public function setSize($size)
    {
        $this->size = $size;

        return $this;
    }

    /**
     * Get size
     *
     * @return integer 
     */
    public function getSize()
    {
        $size = $this->size;
        //if ($size  >= 1099511627776)return round($size/1099511627776,1) . ' To';
        //if ($size  >= 1073741824)return round($size/1073741824,1) . ' Go';
        if ( $size >= 1048576 ) return round($size/1048576,1) . ' Mo';
        if ( $size >= 1024 ) return round($size/1024,1) . ' Ko';
        return $size . ' octets';
    }

    /**
     * Set mimetype
     *
     * @param string $mimetype
     * @return Fichier
     */
    public function setMimetype($mimetype)
    {
        $this->mimetype = $mimetype;

        return $this;
    }

    /**
     * Get mimetype
     *
     * @return string 
     */
    public function getMimetype()
    {
        return $this->mimetype;
    }
}
