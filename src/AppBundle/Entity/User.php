<?php

namespace AppBundle\Entity;

use Gedmo\Mapping\Annotation as Gedmo;

use FOS\UserBundle\Model\User as BaseUser;

use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Validator\Constraints as Assert;

use Doctrine\Common\Collections\ArrayCollection;


/**
 * @ORM\Entity
 * @ORM\Table(name="fos_user")
 */
class User extends BaseUser
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    
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
     * @ORM\Column(name="first_name", type="string", length=255, nullable=true)
     * 
     * @Assert\NotBlank(
     *      message = "First Name is required"
     * )
     */
    private $firstName;
    
    /**
     * @var string
     *
     * @ORM\Column(name="last_name", type="string", length=255, nullable=true)
     * 
     * @Assert\NotBlank(
     *      message = "Last Name is required"
     * )
     */
    private $lastName;
    
    /**
     * 
     * @ORM\ManyToOne(targetEntity="EducationLevel")
     * @ORM\JoinColumn(name="education_level_id", referencedColumnName="id")
     * 
     */
    private $educationLevel;
    
    /**
     * 
     * @ORM\ManyToOne(targetEntity="Occupation")
     * @ORM\JoinColumn(name="occupation_id", referencedColumnName="id")
     * 
     */
    private $occupation;
    
    /**
     * 
     * @ORM\OneToMany(targetEntity="Prediction", mappedBy="user")
     */
    private $predictions;
    
    /**
     * @var bool
     *
     * @ORM\Column(name="accept_terms", type="boolean", nullable=false)
     */
    private $acceptTerms;
    
    /**
     * @var bool
     *
     * @ORM\Column(name="research_terms", type="boolean", nullable=true)
     */
    private $researchTerms;
    
    /**
     * @var bool
     *
     * @ORM\Column(name="marketing_terms", type="boolean", nullable=true)
     */
    private $marketingTerms;
    
    /**
     * @var bool
     *
     * @ORM\Column(name="isFixture", type="boolean", nullable=true)
     */
    private $isFixture;
    
    /**
     * @var bool
     *
     * @ORM\Column(name="unsub", type="boolean", nullable=true)
     */
    private $unsub;
    
    /**
     * @var string
     *
     * @ORM\Column(name="country", type="string", length=255, nullable=true)
     * 
     */
    private $country;
    
    /**
     * One User has Many Notes.
     * @ORM\OneToMany(targetEntity="Note", mappedBy="creator")
     */
    private $notes;
    
    /**
     * @var \DateTime $loginOffsetTimer

     * @ORM\Column(type="datetime", nullable=true)
     */
    private $loginOffsetTimer;
    
    public function __construct()
    {
        
        parent::__construct();
        
        $this->addRole("ROLE_FRONT_USER");
        
        $this->setAcceptTerms(false);
        
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
    
    public function getCategory()
    {
        
        if (null !== $this->getOccupation()) {
        
            if ($this->getOccupation()->getId() === 4 || $this->getOccupation()->getId() === 5 || $this->getOccupation()->getId() === 7) {

                return "Student";

            } else if ($this->getOccupation()->getId() === 9) {

                return "Other";

            } else {

                return "Law Professionals";

            }
            
        } else {
            
            return "Other";
            
        }
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     *
     * @return User
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
     * @return User
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
     * Set firstName
     *
     * @param string $firstName
     *
     * @return User
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * Get firstName
     *
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * Set lastName
     *
     * @param string $lastName
     *
     * @return User
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * Get lastName
     *
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }
    
    /**
     * Overriding the parent setEmail to set the users username to their email
     * @param type $email
     */
    public function setEmail($email){
        
        parent::setEmail($email);
        
        $this->username = $email;
        
    }

    /**
     * Set educationLevel
     *
     * @param \AppBundle\Entity\EducationLevel $educationLevel
     *
     * @return User
     */
    public function setEducationLevel(\AppBundle\Entity\EducationLevel $educationLevel = null)
    {
        $this->educationLevel = $educationLevel;

        return $this;
    }

    /**
     * Get educationLevel
     *
     * @return \AppBundle\Entity\EducationLevel
     */
    public function getEducationLevel()
    {
        return $this->educationLevel;
    }

    /**
     * Set occupation
     *
     * @param \AppBundle\Entity\Occupation $occupation
     *
     * @return User
     */
    public function setOccupation(\AppBundle\Entity\Occupation $occupation = null)
    {
        $this->occupation = $occupation;

        return $this;
    }

    /**
     * Get occupation
     *
     * @return \AppBundle\Entity\Occupation
     */
    public function getOccupation()
    {
        return $this->occupation;
    }

    /**
     * Set acceptTerms
     *
     * @param boolean $acceptTerms
     *
     * @return User
     */
    public function setAcceptTerms($acceptTerms)
    {
        $this->acceptTerms = $acceptTerms;

        return $this;
    }

    /**
     * Get acceptTerms
     *
     * @return boolean
     */
    public function getAcceptTerms()
    {
        return $this->acceptTerms;
    }

    /**
     * Set researchTerms
     *
     * @param boolean $researchTerms
     *
     * @return User
     */
    public function setResearchTerms($researchTerms)
    {
        $this->researchTerms = $researchTerms;

        return $this;
    }

    /**
     * Get researchTerms
     *
     * @return boolean
     */
    public function getResearchTerms()
    {
        return $this->researchTerms;
    }

    /**
     * Set marketingTerms
     *
     * @param boolean $marketingTerms
     *
     * @return User
     */
    public function setMarketingTerms($marketingTerms)
    {
        $this->marketingTerms = $marketingTerms;

        return $this;
    }

    /**
     * Get marketingTerms
     *
     * @return boolean
     */
    public function getMarketingTerms()
    {
        return $this->marketingTerms;
    }

    /**
     * Add prediction
     *
     * @param \AppBundle\Entity\Prediction $prediction
     *
     * @return User
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
    
    public function getFullname()
    {
        
        return trim($this->firstName." ".$this->lastName);
        
    }
    
    public function printname($length = 16)
    {
        
        if (strlen($this->getFullname()) <= $length) {
            
            return $this->getFullname();
            
        } else {
            
            return substr($this->getFullname(), 0, $length-3)."...";
            
        }
        
    }

    /**
     * Add note
     *
     * @param \AppBundle\Entity\Note $note
     *
     * @return User
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
     * Set isFixture
     *
     * @param boolean $isFixture
     *
     * @return User
     */
    public function setIsFixture($isFixture)
    {
        $this->isFixture = $isFixture;

        return $this;
    }

    /**
     * Get isFixture
     *
     * @return boolean
     */
    public function getIsFixture()
    {
        return $this->isFixture;
    }

    /**
     * Set country
     *
     * @param string $country
     *
     * @return User
     */
    public function setCountry($country)
    {
        $this->country = $country;

        return $this;
    }

    /**
     * Get country
     *
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * Set loginOffsetTimer
     *
     * @param \DateTime $loginOffsetTimer
     *
     * @return User
     */
    public function setLoginOffsetTimer($loginOffsetTimer)
    {
        $this->loginOffsetTimer = $loginOffsetTimer;

        return $this;
    }

    /**
     * Get loginOffsetTimer
     *
     * @return \DateTime
     */
    public function getLoginOffsetTimer()
    {
        return $this->loginOffsetTimer;
    }
    
    

    /**
     * Set unsub
     *
     * @param boolean $unsub
     *
     * @return User
     */
    public function setUnsub($unsub)
    {
        $this->unsub = $unsub;

        return $this;
    }

    /**
     * Get unsub
     *
     * @return boolean
     */
    public function getUnsub()
    {
        if (null === $this->unsub) {
            
            return false;
            
        } else {
        
            return $this->unsub;
            
        }
    }
}
