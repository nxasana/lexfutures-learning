<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Validator\Constraints as Assert;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * CategoryType
 *
 * @ORM\Table(name="category_type")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\CategoryTypeRepository")
 */
class CategoryType
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
     * One CategoryType has Many Categories.
     * @ORM\OneToMany(targetEntity="Occupation", mappedBy="categoryType")
     */
    private $occupations;
    
    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->occupations = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return CategoryType
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
     * Add occupation
     *
     * @param \AppBundle\Entity\Occupation $occupation
     *
     * @return CategoryType
     */
    public function addOccupation(\AppBundle\Entity\Occupation $occupation)
    {
        $this->occupations[] = $occupation;

        return $this;
    }

    /**
     * Remove occupation
     *
     * @param \AppBundle\Entity\Occupation $occupation
     */
    public function removeOccupation(\AppBundle\Entity\Occupation $occupation)
    {
        $this->occupations->removeElement($occupation);
    }

    /**
     * Get occupations
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getOccupations()
    {
        return $this->occupations;
    }
}
