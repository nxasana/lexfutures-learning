<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Occupation
 *
 * @ORM\Table(name="occupation")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\OccupationRepository")
 */
class Occupation
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
     * Many Occupations have One CategoryType.
     * @ORM\ManyToOne(targetEntity="CategoryType", inversedBy="occupations")
     * @ORM\JoinColumn(name="category_type_id", referencedColumnName="id")
     */
    private $categoryType;
    
    /**
     * Return a string version of this object
     * 
     * @return String
     */
    public function __toString()
    {
        
        return $this->getName();
        
    }
    
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
     * Set name
     *
     * @param string $name
     *
     * @return Occupation
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
     * Set categoryType
     *
     * @param \AppBundle\Entity\CategoryType $categoryType
     *
     * @return Occupation
     */
    public function setCategoryType(\AppBundle\Entity\CategoryType $categoryType = null)
    {
        $this->categoryType = $categoryType;

        return $this;
    }

    /**
     * Get categoryType
     *
     * @return \AppBundle\Entity\CategoryType
     */
    public function getCategoryType()
    {
        return $this->categoryType;
    }
}
