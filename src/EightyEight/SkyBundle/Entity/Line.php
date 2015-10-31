<?php

namespace EightyEight\SkyBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Line
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="EightyEight\SkyBundle\Entity\LineRepository")
 */
class Line
{
    /**
     * @ORM\ManyToOne(targetEntity="EightyEight\SkyBundle\Entity\Constellation", inversedBy="lines", cascade={"persist", "remove"}, fetch="EAGER")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $constellation;

    /**
     * @ORM\ManyToOne(targetEntity="EightyEight\SkyBundle\Entity\Star", inversedBy="lines", cascade={"persist", "remove"}, fetch="EAGER")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $start;

    /**
     * @ORM\ManyToOne(targetEntity="EightyEight\SkyBundle\Entity\Star", cascade={"persist", "remove"}, fetch="EAGER")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $end;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;


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
     * Set constellation
     *
     * @param \EightyEight\SkyBundle\Entity\Constellation $constellation
     *
     * @return Line
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

    /**
     * Set start
     *
     * @param \EightyEight\SkyBundle\Entity\Star $start
     *
     * @return Line
     */
    public function setStart(\EightyEight\SkyBundle\Entity\Star $start = null)
    {
        $this->start = $start;

        return $this;
    }

    /**
     * Get start
     *
     * @return \EightyEight\SkyBundle\Entity\Star
     */
    public function getStart()
    {
        return $this->start;
    }

    /**
     * Set end
     *
     * @param \EightyEight\SkyBundle\Entity\Star $end
     *
     * @return Line
     */
    public function setEnd(\EightyEight\SkyBundle\Entity\Star $end = null)
    {
        $this->end = $end;

        return $this;
    }

    /**
     * Get end
     *
     * @return \EightyEight\SkyBundle\Entity\Star
     */
    public function getEnd()
    {
        return $this->end;
    }
}
