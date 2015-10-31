<?php

namespace EightyEight\SkyBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Star
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="EightyEight\SkyBundle\Entity\StarRepository")
 */
class Star
{
    /**
     * @ORM\ManyToOne(targetEntity="EightyEight\SkyBundle\Entity\Constellation", inversedBy="stars", cascade={"persist", "remove"}, fetch="EAGER")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $constellation;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

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
     * @var float
     *
     * @ORM\Column(name="magnitute", type="float")
     */
    private $magnitute;

    /**
     * @var string
     *
     * @ORM\Column(name="bayer", type="string", length=1)
     */
    private $bayer;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=2)
     */
    private $type;

    /**
     * @var string
     *
     * @ORM\Column(name="spectrum", type="string", length=2)
     */
    private $spectrum;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;


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
     * Set rightAscension
     *
     * @param float $rightAscension
     *
     * @return Star
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
     * @return Star
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
     * Set magnitute
     *
     * @param float $magnitute
     *
     * @return Star
     */
    public function setMagnitute($magnitute)
    {
        $this->magnitute = $magnitute;

        return $this;
    }

    /**
     * Get magnitute
     *
     * @return float
     */
    public function getMagnitute()
    {
        return $this->magnitute;
    }

    /**
     * Set bayer
     *
     * @param string $bayer
     *
     * @return Star
     */
    public function setBayer($bayer)
    {
        $this->bayer = $bayer;

        return $this;
    }

    /**
     * Get bayer
     *
     * @return string
     */
    public function getBayer()
    {
        return $this->bayer;
    }

    /**
     * Set type
     *
     * @param string $type
     *
     * @return Star
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set spectrum
     *
     * @param string $spectrum
     *
     * @return Star
     */
    public function setSpectrum($spectrum)
    {
        $this->spectrum = $spectrum;

        return $this;
    }

    /**
     * Get spectrum
     *
     * @return string
     */
    public function getSpectrum()
    {
        return $this->spectrum;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Star
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
     * Set constellation
     *
     * @param \EightyEight\SkyBundle\Entity\Constellation $constellation
     *
     * @return Star
     */
    public function setConstellation(\EightyEight\SkyBundle\Entity\Constellation $constellation = null)
    {
        $this->constellation = $constellation;

        return $this;
    }

    /**
     * Get constellation
     *
     * @return \EightyEight\SkyBundle\Entity\Constellation
     */
    public function getConstellation()
    {
        return $this->constellation;
    }
}
