<?php

namespace AppBundle\Entity;

use Gedmo\Mapping\Annotation as Gedmo;

use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Validator\Constraints as Assert;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * Judge
 *
 * @ORM\Table(name="judge")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\JudgeRepository")
 */
class Judge
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
     * @var bool
     *
     * @ORM\Column(name="is_retired", type="boolean", nullable=true)
     */
    private $isRetired;
    
    /**
     * 
     * @ORM\ManyToOne(targetEntity="Jurisdiction", inversedBy="Judges")
     * @ORM\JoinColumn(name="jurisdiction_id", referencedColumnName="id")
     * 
     */
    private $jurisdiction;
    
    /**
     * 
     * @ORM\ManyToOne(targetEntity="JusticeTitle")
     * @ORM\JoinColumn(name="justice_title_id", referencedColumnName="id")
     * 
     */
    private $justiceTitle;
    
    /**
     * Many Judges have Many Courtcases.
     * @ORM\ManyToMany(targetEntity="Courtcase", mappedBy="judges")
     */
    private $courtcases;
    
    /**
     * @ORM\ManyToOne(targetEntity="File")
     * @ORM\JoinColumn(name="biopic_id", referencedColumnName="id")
     */
    private $biopic;
    
    /**
     * @ORM\ManyToOne(targetEntity="File")
     * @ORM\JoinColumn(name="biodoc_id", referencedColumnName="id")
     */
    private $biodoc;
    
    /**
     * @var string
     *
     * @ORM\Column(name="profile_line_1", type="text", nullable=true)
     */
    private $profileLine1;
    
    /**
     * @var string
     *
     * @ORM\Column(name="profile_line_2", type="text", nullable=true)
     */
    private $profileLine2;
    
    /**
     * @var string
     *
     * @ORM\Column(name="profile_line_3", type="text", nullable=true)
     */
    private $profileLine3;
    
    /**
     * @var string
     *
     * @ORM\Column(name="profile_line_4", type="text", nullable=true)
     */
    private $profileLine4;
    
    /**
     * @var string
     *
     * @ORM\Column(name="profile_line_5", type="text", nullable=true)
     */
    private $profileLine5;
    
    /**
     * @Gedmo\Slug(fields={"name"})
     * @ORM\Column(length=128, unique=true)
     */
    private $slug;
    
    /**
     * @var string
     *
     * @ORM\Column(name="recusals", type="text", nullable=false)
     */
    private $recusals;
    
    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
    
    public function __construct()
    {
    
        $this->courtcases = new \Doctrine\Common\Collections\ArrayCollection();
        
        $this->recusals = "";
        
    }
    
    public function isRecused($id)
    {
        
        $recusals = unserialize($this->getRecusals());
        
        if ($recusals && count($recusals) > 0) {
            
            if (in_array($id, $recusals)) {
                
                return true;
                
            } 
            
            
        } 
        
        return false;
        
    }
    
    /**
     * Set name
     *
     * @param string $name
     *
     * @return Judge
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
     * @return Judge
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
     * Set justiceTitle
     *
     * @param \AppBundle\Entity\JusticeTitle $justiceTitle
     *
     * @return Judge
     */
    public function setJusticeTitle(\AppBundle\Entity\JusticeTitle $justiceTitle = null)
    {
        $this->justiceTitle = $justiceTitle;

        return $this;
    }

    /**
     * Get justiceTitle
     *
     * @return \AppBundle\Entity\JusticeTitle
     */
    public function getJusticeTitle()
    {
        return $this->justiceTitle;
    }

    /**
     * Add courtcase
     *
     * @param \AppBundle\Entity\Courtcase $courtcase
     *
     * @return Judge
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
     * Set created
     *
     * @param \DateTime $created
     *
     * @return Judge
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
     * @return Judge
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
     * Set biopic
     *
     * @param \AppBundle\Entity\File $biopic
     *
     * @return Judge
     */
    public function setBiopic(\AppBundle\Entity\File $biopic = null)
    {
        $this->biopic = $biopic;

        return $this;
    }

    /**
     * Get biopic
     *
     * @return \AppBundle\Entity\File
     */
    public function getBiopic()
    {
        return $this->biopic;
    }

    /**
     * Set profileLine1
     *
     * @param string $profileLine1
     *
     * @return Judge
     */
    public function setProfileLine1($profileLine1)
    {
        $this->profileLine1 = $profileLine1;

        return $this;
    }

    /**
     * Get profileLine1
     *
     * @return string
     */
    public function getProfileLine1()
    {
        return $this->profileLine1;
    }

    /**
     * Set profileLine2
     *
     * @param string $profileLine2
     *
     * @return Judge
     */
    public function setProfileLine2($profileLine2)
    {
        $this->profileLine2 = $profileLine2;

        return $this;
    }

    /**
     * Get profileLine2
     *
     * @return string
     */
    public function getProfileLine2()
    {
        return $this->profileLine2;
    }

    /**
     * Set profileLine3
     *
     * @param string $profileLine3
     *
     * @return Judge
     */
    public function setProfileLine3($profileLine3)
    {
        $this->profileLine3 = $profileLine3;

        return $this;
    }

    /**
     * Get profileLine3
     *
     * @return string
     */
    public function getProfileLine3()
    {
        return $this->profileLine3;
    }

    /**
     * Set profileLine4
     *
     * @param string $profileLine4
     *
     * @return Judge
     */
    public function setProfileLine4($profileLine4)
    {
        $this->profileLine4 = $profileLine4;

        return $this;
    }

    /**
     * Get profileLine4
     *
     * @return string
     */
    public function getProfileLine4()
    {
        return $this->profileLine4;
    }

    /**
     * Set profileLine5
     *
     * @param string $profileLine5
     *
     * @return Judge
     */
    public function setProfileLine5($profileLine5)
    {
        $this->profileLine5 = $profileLine5;

        return $this;
    }

    /**
     * Get profileLine5
     *
     * @return string
     */
    public function getProfileLine5()
    {
        return $this->profileLine5;
    }

    /**
     * Set slug
     *
     * @param string $slug
     *
     * @return Judge
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

    /**
     * Set isRetired
     *
     * @param boolean $isRetired
     *
     * @return Judge
     */
    public function setIsRetired($isRetired)
    {
        $this->isRetired = $isRetired;

        return $this;
    }

    /**
     * Get isRetired
     *
     * @return boolean
     */
    public function getIsRetired()
    {
        return $this->isRetired;
    }

    /**
     * Set recusals
     *
     * @param string $recusals
     *
     * @return Judge
     */
    public function setRecusals($recusals)
    {
        $this->recusals = $recusals;

        return $this;
    }

    /**
     * Get recusals
     *
     * @return string
     */
    public function getRecusals()
    {
        return $this->recusals;
    }

    /**
     * Set biodoc
     *
     * @param \AppBundle\Entity\File $biodoc
     *
     * @return Judge
     */
    public function setBiodoc(\AppBundle\Entity\File $biodoc = null)
    {
        $this->biodoc = $biodoc;

        return $this;
    }

    /**
     * Get biodoc
     *
     * @return \AppBundle\Entity\File
     */
    public function getBiodoc()
    {
        return $this->biodoc;
    }
}
