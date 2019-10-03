<?php

namespace AppBundle\Entity;

use Gedmo\Mapping\Annotation as Gedmo;

use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Season
 *
 * @ORM\Table(name="season")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\SeasonRepository")
 */
class Season
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
     * @Gedmo\Slug(fields={"name"})
     * @ORM\Column(length=128, unique=true)
     */
    private $slug;
    
    /**
     * @var \DateTime $created
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    private $created;

    /**
     * @var \DateTime $updated
     *
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(type="datetime")
     */
    private $updated;
    
    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     * 
     * @Assert\NotBlank(
     *      message = "Name is required"
     * )
     */
    private $name;

    /**
     * 
     * @ORM\ManyToOne(targetEntity="Jurisdiction", inversedBy="seasons")
     * @ORM\JoinColumn(name="jurisdiction_id", referencedColumnName="id")
     * 
     */
    private $jurisdiction;
    
    /**
     * One Season has many Courtcases
     * @ORM\OneToMany(targetEntity="Courtcase", mappedBy="season")
     */
    private $courtcases;
    
    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    public function __toString()
    {
        
        return $this->name;
        
    }
    
    /**
     * Set created
     *
     * @param \DateTime $created
     *
     * @return Season
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created
     *
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set updated
     *
     * @param \DateTime $updated
     *
     * @return Season
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;

        return $this;
    }

    /**
     * Get updated
     *
     * @return \DateTime
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Season
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
     * Set jurisdiction
     *
     * @param \AppBundle\Entity\Jurisdiction $jurisdiction
     *
     * @return Season
     */
    public function setJurisdiction(\AppBundle\Entity\Jurisdiction $jurisdiction = null)
    {
        $this->jurisdiction = $jurisdiction;

        return $this;
    }

    /**
     * Get jurisdiction
     *
     * @return \AppBundle\Entity\Jurisdiction
     */
    public function getJurisdiction()
    {
        return $this->jurisdiction;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->courtcases = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add courtcase
     *
     * @param \AppBundle\Entity\Courtcase $courtcase
     *
     * @return Season
     */
    public function addCourtcase(\AppBundle\Entity\Courtcase $courtcase)
    {
        $this->courtcases[] = $courtcase;

        return $this;
    }

    /**
     * Remove courtcase
     *
     * @param \AppBundle\Entity\Courtcase $courtcase
     */
    public function removeCourtcase(\AppBundle\Entity\Courtcase $courtcase)
    {
        $this->courtcases->removeElement($courtcase);
    }

    /**
     * Get courtcases
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCourtcases()
    {
        return $this->courtcases;
    }

    /**
     * Set slug
     *
     * @param string $slug
     *
     * @return Season
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * Get slug
     *
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }
}
