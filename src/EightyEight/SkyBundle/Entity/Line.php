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
}

