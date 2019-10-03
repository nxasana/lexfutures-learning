<?php

namespace AppBundle\Entity;

use Gedmo\Mapping\Annotation as Gedmo;

use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Validator\Constraints as Assert;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * Courtcase
 *
 * @ORM\Table(name="courtcase")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\CourtcaseRepository")
 */
class Courtcase
{
    
    const STATUS_DRAFT = 1;
    const STATUS_PUBLISHED = 2;
    const STATUS_DECIDED = 3;
    const STATUS_DELETED = 4;
    
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
     * @Gedmo\Slug(fields={"name"})
     * @ORM\Column(length=128, unique=true)
     */
    private $slug;
    
    /**
     * @var bool
     *
     * @ORM\Column(name="is_league", type="boolean", nullable=true)
     */
    private $isLeague;
    
    /**
     * @var bool
     *
     * @ORM\Column(name="is_paused", type="boolean", nullable=true)
     */
    private $isPaused;
    
    /**
     * @var bool
     *
     * @ORM\Column(name="removed_from_role", type="boolean", nullable=true)
     */
    private $removedFromRole;
    
    /**
     * One Courtcase has Many Notes.
     * @ORM\OneToMany(targetEntity="Note", mappedBy="courtcase")
     */
    private $notes;
    
    /**
     * @var \DateTime
     * 
     * @ORM\Column(name="scheduled_date", type="datetime", nullable=true)
     * 
     * @Assert\NotBlank(
     *      message = "Deadline for offers date required"
     * )
     */
    private $scheduledDate;
    
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
     * @var int
     *
     * @ORM\Column(name="status", type="integer")
     */
    private $status;
    
    /**
     * 
     * @ORM\ManyToOne(targetEntity="Jurisdiction", inversedBy="courtcases")
     * @ORM\JoinColumn(name="jurisdiction_id", referencedColumnName="id")
     * 
     */
    private $jurisdiction;
    
    /**
     * 
     * @ORM\ManyToOne(targetEntity="Season", inversedBy="courtcases")
     * @ORM\JoinColumn(name="season_id", referencedColumnName="id")
     * 
     */
    private $season;
    
    /**
     * 
     * @ORM\OneToMany(targetEntity="Prediction", mappedBy="courtcase")
     */
    private $predictions;
    
    /**
     * Many Courtcases have Many Judges.
     * @ORM\ManyToMany(targetEntity="Judge", inversedBy="courtcases")
     * @ORM\JoinTable(name="judges_courtcases")
     */
    private $judges;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        
        $this->status = self::STATUS_DRAFT;
        
        $this->judges = new \Doctrine\Common\Collections\ArrayCollection();
        
        $this->notes = new \Doctrine\Common\Collections\ArrayCollection();
        
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
     * @return Courtcase
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
     * Set created
     *
     * @param \DateTime $created
     *
     * @return Courtcase
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
     * @return Courtcase
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
     * Set scheduledDate
     *
     * @param \DateTime $scheduledDate
     *
     * @return Courtcase
     */
    public function setScheduledDate($scheduledDate)
    {
        $this->scheduledDate = $scheduledDate;

        return $this;
    }

    /**
     * Get scheduledDate
     *
     * @return \DateTime
     */
    public function getScheduledDate()
    {
        return $this->scheduledDate;
    }

    /**
     * Set status
     *
     * @param integer $status
     *
     * @return Courtcase
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return integer
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set jurisdiction
     *
     * @param \AppBundle\Entity\Jurisdiction $jurisdiction
     *
     * @return Courtcase
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
     * Add prediction
     *
     * @param \AppBundle\Entity\Prediction $prediction
     *
     * @return Courtcase
     */
    public function addPrediction(\AppBundle\Entity\Prediction $prediction)
    {
        $this->predictions[] = $prediction;

        return $this;
    }

    /**
     * Remove prediction
     *
     * @param \AppBundle\Entity\Prediction $prediction
     */
    public function removePrediction(\AppBundle\Entity\Prediction $prediction)
    {
        $this->predictions->removeElement($prediction);
    }

    /**
     * Get predictions
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPredictions()
    {
        return $this->predictions;
    }
    
    
    public function getStatusToString()
    {
        
        if ($this->getStatus() === self::STATUS_DECIDED || $this->getRuling()) {
            
            return "decided";
            
        }
        
        // TODO update this check to ensure that cases become 'upcoming' at midnight of the hearing date
        if ($this->getStatus() === self::STATUS_PUBLISHED) {
            
            if ($this->checkIfClosed()) {
                
                return 'upcoming';
                
            } else {
                
                return 'recently-heard';
                
            }
            
        }
        
        
        
    }
    
    public function checkIfClosed()
    {
        
        /*
        if ($this->getScheduledDate() > new \DateTime()) {
            
            return true;
            
        }
        */
        
        $current = new \DateTime();
        
        if ($this->getScheduledDate()->format("Y-m-d") > $current->format("Y-m-d")) {
            
            return true;
            
        }
        
        return false;
        
    }
    
    /**
     * Add judge
     *
     * @param \AppBundle\Entity\Judge $judge
     *
     * @return Courtcase
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
    
    public function getUserPredictions()
    {
        
        $results = array();
        
        foreach ($this->getPredictions() as $prediction) {
            
            if (!$prediction->getIsRuling()) {
                
                $results[] = $prediction;
                
            }
            
        }
        
        return $results;
        
    }
    
    public function getRuling()
    {
        
        foreach ($this->getPredictions() as $prediction) {
            
            if ($prediction->getIsRuling()) {
                
                return $prediction;
                
            }
            
        }
        
        return null;
        
    }
    
    
    public function getCrowdPrediction()
    {
        
        $crowd = "";
        
        $upheld = $notupheld = $total = 0;
        
        foreach ($this->getPredictions() as $prediction) {
            
            if (!$prediction->getIsRuling()) {
                
                $total += 1;
                
                $predictionScores = $this->scorifyPrediction($prediction);
                
                $upheld = $upheld + $predictionScores["upheld"];
                
                $notupheld = $notupheld + $predictionScores["dismissed"];
                
            }
            
        }
        
        if ($total === 0) {
            
            return "";
            
        } else {
            
            if ($upheld > $notupheld) {
                
                $upheld = ceil($upheld / $total);
                
                $notupheld = floor($notupheld / $total);
                
                return $upheld ." - ".$notupheld . " Appeal will be upheld";

            } else if ($upheld < $notupheld) {
                
                $upheld = floor($upheld / $total);
                
                $notupheld = ceil($notupheld / $total);
                
                return $upheld ." - ".$notupheld . " Appeal will be dismissed";

            } else {
                
                $upheld = ceil($upheld / $total);
                
                $notupheld = ceil($notupheld / $total);
                
                return $upheld ." - ".$notupheld . " Lower court decision";

            }
            
        }
        
        
    }
    
    private function scorifyPrediction($prediction)
    {
        
        //$results = array("upheld" => 0, "dismissed" => 0);
        
        $upheld = $notupheld = 0;
        
        foreach ($prediction->getPredictionJudges() as $predictionJudge) {
            
            if (!$predictionJudge->getJudge()->isRecused($this->getId())) {
                
                if ($predictionJudge->getAppealUpheld()) {

                    $upheld = $upheld + 1;

                } else {

                    $notupheld = $notupheld + 1;

                }
                
            }
        }
        
        return array("upheld" => $upheld, "dismissed" => $notupheld);
        
    }
    
    public function publicationStatusToString()
    {
        
        if ($this->getStatus() === self::STATUS_DRAFT) {
            
            return "draft";
            
        } else if ($this->getStatus() === self::STATUS_PUBLISHED) {
            
            return "published";
            
        } else if ($this->getStatus() === self::STATUS_DECIDED) {
            
            return "decided";
            
        } else if ($this->getStatus() === self::STATUS_DELETED) {
            
            return "deleted";
            
        }
        
    }

    /**
     * Set slug
     *
     * @param string $slug
     *
     * @return Courtcase
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
     * Add note
     *
     * @param \AppBundle\Entity\Note $note
     *
     * @return Courtcase
     */
    public function addNote(\AppBundle\Entity\Note $note)
    {
        $this->notes[] = $note;

        return $this;
    }

    /**
     * Remove note
     *
     * @param \AppBundle\Entity\Note $note
     */
    public function removeNote(\AppBundle\Entity\Note $note)
    {
        $this->notes->removeElement($note);
    }

    /**
     * Get notes
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getNotes()
    {
        return $this->notes;
    }

    /**
     * Set isLeague
     *
     * @param boolean $isLeague
     *
     * @return Courtcase
     */
    public function setIsLeague($isLeague)
    {
        $this->isLeague = $isLeague;

        return $this;
    }

    /**
     * Get isLeague
     *
     * @return boolean
     */
    public function getIsLeague()
    {
        return $this->isLeague;
    }

    /**
     * Set removedFromRole
     *
     * @param boolean $removedFromRole
     *
     * @return Courtcase
     */
    public function setRemovedFromRole($removedFromRole)
    {
        $this->removedFromRole = $removedFromRole;

        return $this;
    }

    /**
     * Get removedFromRole
     *
     * @return boolean
     */
    public function getRemovedFromRole()
    {
        return $this->removedFromRole;
    }

    /**
     * Set isPaused
     *
     * @param boolean $isPaused
     *
     * @return Courtcase
     */
    public function setIsPaused($isPaused)
    {
        $this->isPaused = $isPaused;

        return $this;
    }

    /**
     * Get isPaused
     *
     * @return boolean
     */
    public function getIsPaused()
    {
        return $this->isPaused;
    }

    /**
     * Set season
     *
     * @param \AppBundle\Entity\Season $season
     *
     * @return Courtcase
     */
    public function setSeason(\AppBundle\Entity\Season $season = null)
    {
        $this->season = $season;

        return $this;
    }

    /**
     * Get season
     *
     * @return \AppBundle\Entity\Season
     */
    public function getSeason()
    {
        return $this->season;
    }
}
