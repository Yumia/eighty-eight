<?php

namespace EightyEight\SkyBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Constellation
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="EightyEight\SkyBundle\Entity\ConstellationRepository")
 */
class Constellation
{
    /**
     * @ORM\OneToMany(targetEntity="EightyEight\SkyBundle\Entity\Star", mappedBy="constellation", cascade={"persist"})
     */
    private $stars;

    /**
     * @ORM\OneToMany(targetEntity="EightyEight\SkyBundle\Entity\Line", mappedBy="constellation", cascade={"persist"})
     */
    private $lines;

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
     * @ORM\Column(name="code", type="string", length=3)
     */
    private $code;

    /**
     * @var string
     *
     * @ORM\Column(name="latin", type="string", length=255)
     */
    private $latin;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var float
     *
     * @ORM\Column(name="right_ascension", type="float")
     */
    private $rightAscension;

    /**
     * @var float
     *
     * @ORM\Column(name="declination", type="float")
     */
    private $declination;

    /**
     * @var boolean
     *
     * @ORM\Column(name="zodiac", type="boolean")
     */
    private $zodiac;

    /**
     * @var integer
     *
     * @ORM\Column(name="hemisphere", type="smallint")
     */
    private $hemisphere;

    /**
     * @var string
     *
     * @ORM\Column(name="source", type="string", length=255)
     */
    private $source;


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
     * Set code
     *
     * @param string $code
     *
     * @return Constellation
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set latin
     *
     * @param string $latin
     *
     * @return Constellation
     */
    public function setLatin($latin)
    {
        $this->latin = $latin;

        return $this;
    }

    /**
     * Get latin
     *
     * @return string
     */
    public function getLatin()
    {
        return $this->latin;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Constellation
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
     * Set rightAscension
     *
     * @param float $rightAscension
     *
     * @return Constellation
     */
    public function setRightAscension($rightAscension)
    {
        $this->rightAscension = $rightAscension;

        return $this;
    }

    /**
     * Get rightAscension
     *
     * @return float
     */
    public function getRightAscension()
    {
        return $this->rightAscension;
    }

    /**
     * Set declination
     *
     * @param float $declination
     *
     * @return Constellation
     */
    public function setDeclination($declination)
    {
        $this->declination = $declination;

        return $this;
    }

    /**
     * Get declination
     *
     * @return float
     */
    public function getDeclination()
    {
        return $this->declination;
    }

    /**
     * Set zodiac
     *
     * @param boolean $zodiac
     *
     * @return Constellation
     */
    public function setZodiac($zodiac)
    {
        $this->zodiac = $zodiac;

        return $this;
    }

    /**
     * Get zodiac
     *
     * @return boolean
     */
    public function getZodiac()
    {
        return $this->zodiac;
    }

    /**
     * Set hemisphere
     *
     * @param integer $hemisphere
     *
     * @return Constellation
     */
    public function setHemisphere($hemisphere)
    {
        $this->hemisphere = $hemisphere;

        return $this;
    }

    /**
     * Get hemisphere
     *
     * @return integer
     */
    public function getHemisphere()
    {
        return $this->hemisphere;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->stars = new \Doctrine\Common\Collections\ArrayCollection();
        $this->lines = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set source
     *
     * @param string $source
     *
     * @return Constellation
     */
    public function setSource($source)
    {
        $this->source = $source;

        return $this;
    }

    /**
     * Get source
     *
     * @return string
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * Add star
     *
     * @param \EightyEight\SkyBundle\Entity\Star $star
     *
     * @return Constellation
     */
    public function addStar(\EightyEight\SkyBundle\Entity\Star $star)
    {
        $this->stars[] = $star;

        return $this;
    }

    /**
     * Remove star
     *
     * @param \EightyEight\SkyBundle\Entity\Star $star
     */
    public function removeStar(\EightyEight\SkyBundle\Entity\Star $star)
    {
        $this->stars->removeElement($star);
    }

    /**
     * Get stars
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getStars()
    {
        return $this->stars;
    }

    /**
     * Add line
     *
     * @param \EightyEight\SkyBundle\Entity\Line $line
     *
     * @return Constellation
     */
    public function addLine(\EightyEight\SkyBundle\Entity\Line $line)
    {
        $this->lines[] = $line;

        return $this;
    }

    /**
     * Remove line
     *
     * @param \EightyEight\SkyBundle\Entity\Line $line
     */
    public function removeLine(\EightyEight\SkyBundle\Entity\Line $line)
    {
        $this->lines->removeElement($line);
    }

    /**
     * Get lines
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getLines()
    {
        return $this->lines;
    }

    public function __toString()
    {
        return $this->getCode();
    }
}
