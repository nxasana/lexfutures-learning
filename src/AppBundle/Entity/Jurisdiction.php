<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Gedmo\Mapping\Annotation as Gedmo;

use Symfony\Component\Validator\Constraints as Assert;

use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Jurisdiction
 *
 * @ORM\Table(name="jurisdiction")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\JurisdictionRepository")
 */
class Jurisdiction
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
     * @var string
     *
     * @ORM\Column(name="anname", type="string", length=255, nullable=false)
     * 
     * @Assert\NotBlank(
     *      message = "Name is required"
     * )
     */
    private $anname;
    
    /**
     * @var string
     *
     * @ORM\Column(name="court", type="string", length=255, nullable=true)
     * 
     */
    private $court;
    
    /**
     * @var string
     *
     * @ORM\Column(name="flag", type="string", length=255, nullable=false)
     *
     */
    private $flag;
    
    /**
     * One Jurisduction has many Judges
     * @ORM\OneToMany(targetEntity="Judge", mappedBy="jurisdiction")
     */
    private $judges;
    
    /**
     * One Jurisduction has many Courtcases
     * @ORM\OneToMany(targetEntity="Courtcase", mappedBy="jurisdiction")
     */
    private $courtcases;
    
    /**
     * One Jurisduction has many Seasons
     * @ORM\OneToMany(targetEntity="Season", mappedBy="jurisdiction")
     */
    private $seasons;
    
    /**
     * One Jurisdiction has one current Season
     * @ORM\OneToOne(targetEntity="Season")
     * @ORM\JoinColumn(name="current_season_id", referencedColumnName="id")
     */
    private $currentSeason;

    
    /**
     * @ORM\ManyToOne(targetEntity="File")
     * @ORM\JoinColumn(name="biopic_id", referencedColumnName="id")
     */
    private $biopic;
    
    /**
     * @var bool
     *
     * @ORM\Column(name="is_active", type="boolean", nullable=false)
     */
    private $isActive;
    
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
     * @return Jurisdiction
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
     * Set flag
     *
     * @param string $flag
     *
     * @return Jurisdiction
     */
    public function setFlag($flag)
    {
        $this->flag = $flag;

        return $this;
    }

    /**
     * Get flag
     *
     * @return string
     */
    public function getFlag()
    {
        return $this->flag;
    }

    /**
     * Set anname
     *
     * @param string $anname
     *
     * @return Jurisdiction
     */
    public function setAnname($anname)
    {
        $this->anname = $anname;

        return $this;
    }

    /**
     * Get anname
     *
     * @return string
     */
    public function getAnname()
    {
        return $this->anname;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->judges = new \Doctrine\Common\Collections\ArrayCollection();
        
        $this->isActive = 0;
    }

    /**
     * Add judge
     *
     * @param \AppBundle\Entity\Judge $judge
     *
     * @return Jurisdiction
     */
    public function addJudge(\AppBundle\Entity\Judge $judge)
    {
        $this->judges[] = $judge;

        return $this;
    }

    /**
     * Remove judge
     *
     * @param \AppBundle\Entity\Judge $judge
     */
    public function removeJudge(\AppBundle\Entity\Judge $judge)
    {
        $this->judges->removeElement($judge);
    }

    /**
     * Get judges
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getJudges()
    {
        return $this->judges;
    }
    
    public function getFreeJudges(\AppBundle\Entity\Courtcase $courtcase)
    {
        
        $freeJudges = $caseJudges = array();
        
        foreach ($courtcase->getJudges() as $casejudge) {
            
            $caseJudges[] = $casejudge->getId();
            
        }
        
        foreach ($this->getJudges() as $judge) {
            
            if (!in_array($judge->getId(), $caseJudges)) {
                
                $freeJudges[] = $judge;
                
            }
            
        }
        
        return $freeJudges;
        
    }
    
    public function getActiveJudges()
    {
        
        $judges = array();
        
        foreach ($this->getJudges() as $judge) {
            
            if (!$judge->getIsRetired()) {
            
                $judges[] = $judge;
            
            }
            
        }
        
        return $judges;
        
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     *
     * @return Jurisdiction
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
     * @return Jurisdiction
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
     * Add courtcase
     *
     * @param \AppBundle\Entity\Courtcase $courtcase
     *
     * @return Jurisdiction
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
     * Set court
     *
     * @param string $court
     *
     * @return Jurisdiction
     */
    public function setCourt($court)
    {
        $this->court = $court;

        return $this;
    }

    /**
     * Get court
     *
     * @return string
     */
    public function getCourt()
    {
        return $this->court;
    }

    /**
     * Set biopic
     *
     * @param \AppBundle\Entity\File $biopic
     *
     * @return Jurisdiction
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
     * Set isActive
     *
     * @param boolean $isActive
     *
     * @return Jurisdiction
     */
    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;

        return $this;
    }

    /**
     * Get isActive
     *
     * @return boolean
     */
    public function getIsActive()
    {
        return $this->isActive;
    }

    /**
     * Set slug
     *
     * @param string $slug
     *
     * @return Jurisdiction
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
     * Add season
     *
     * @param \AppBundle\Entity\Season $season
     *
     * @return Jurisdiction
     */
    public function addSeason(\AppBundle\Entity\Season $season)
    {
        $this->seasons[] = $season;

        return $this;
    }

    /**
     * Remove season
     *
     * @param \AppBundle\Entity\Season $season
     */
    public function removeSeason(\AppBundle\Entity\Season $season)
    {
        $this->seasons->removeElement($season);
    }

    /**
     * Get seasons
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getSeasons()
    {
        return $this->seasons;
    }

    /**
     * Set currentSeason
     *
     * @param \AppBundle\Entity\Season $currentSeason
     *
     * @return Jurisdiction
     */
    public function setCurrentSeason(\AppBundle\Entity\Season $currentSeason = null)
    {
        $this->currentSeason = $currentSeason;

        return $this;
    }

    /**
     * Get currentSeason
     *
     * @return \AppBundle\Entity\Season
     */
    public function getCurrentSeason()
    {
        return $this->currentSeason;
    }
}
