<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Prediction
 *
 * @ORM\Table(name="prediction")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\PredictionRepository")
 */
class Prediction
{
    
    const POINTS_CORRECT = 10;
    const POINTS_BONUS = 10;
    
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
     * 
     * @ORM\ManyToOne(targetEntity="Courtcase", inversedBy="Predictions")
     * @ORM\JoinColumn(name="courtcase_id", referencedColumnName="id")
     */
    private $courtcase;
    
    /**
     * 
     * @ORM\ManyToOne(targetEntity="User", inversedBy="Predictions")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $user;
    
    /**
     * 
     * @ORM\OneToMany(targetEntity="PredictionJudge", mappedBy="prediction", cascade={"persist"})
     */
    private $predictionJudges;
    
    /**
     * 
     * @ORM\OneToMany(targetEntity="Notification", mappedBy="prediction", cascade={"persist"})
     */
    private $notifications;
    
    /**
     * @var bool
     *
     * @ORM\Column(name="is_ruling", type="boolean", nullable=true)
     */
    private $isRuling;
    
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
        
        return $this->calcPrediction();
        
    }
    
    public function getRequiredPredictions()
    {
        
        $count = 0;
        
        foreach ($this->getPredictionJudges() as $predictionJudge) {
            
            if (!$predictionJudge->getJudge()->isRecused($this->getCourtcase()->getId())) {
                
                $count += 1;
                
            }
            
        }
        
        return $count;
        
    }
    
    /**
     * Set courtcase
     *
     * @param \AppBundle\Entity\Courtcase $courtcase
     *
     * @return Prediction
     */
    public function setCourtcase(\AppBundle\Entity\Courtcase $courtcase = null)
    {
        $this->courtcase = $courtcase;

        return $this;
    }

    /**
     * Get courtcase
     *
     * @return \AppBundle\Entity\Courtcase
     */
    public function getCourtcase()
    {
        return $this->courtcase;
    }

    /**
     * Set user
     *
     * @param \AppBundle\Entity\User $user
     *
     * @return Prediction
     */
    public function setUser(\AppBundle\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \AppBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->predictionJudges = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add predictionJudge
     *
     * @param \AppBundle\Entity\PredictionJudge $predictionJudge
     *
     * @return Prediction
     */
    public function addPredictionJudge(\AppBundle\Entity\PredictionJudge $predictionJudge)
    {
        $this->predictionJudges[] = $predictionJudge;

        return $this;
    }

    /**
     * Remove predictionJudge
     *
     * @param \AppBundle\Entity\PredictionJudge $predictionJudge
     */
    public function removePredictionJudge(\AppBundle\Entity\PredictionJudge $predictionJudge)
    {
        $this->predictionJudges->removeElement($predictionJudge);
    }

    /**
     * Get predictionJudges
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPredictionJudges()
    {
        return $this->predictionJudges;
    }
    
    
    public function getPredictionJudgesOrdered()
    {
        
        $ordered = array();
        
        // add the chief justices.
        foreach ($this->predictionJudges as $predictionJudge) {
            
            if ($predictionJudge->getJudge()->getJusticeTitle()->getPriority() == 1) {
                
                $ordered[] = $predictionJudge;
                
            }
            
        }
        
        // add the chief justices.
        foreach ($this->predictionJudges as $predictionJudge) {
            
            if ($predictionJudge->getJudge()->getJusticeTitle()->getPriority() == 2) {
                
                $ordered[] = $predictionJudge;
                
            }
            
        }
        
        // add the chief justices.
        foreach ($this->predictionJudges as $predictionJudge) {
            
            if ($predictionJudge->getJudge()->getJusticeTitle()->getPriority() == 3) {
                
                $ordered[] = $predictionJudge;
                
            }
            
        }
        
        return $ordered;
        
    }
    
    public function getPredictionForJudge($id)
    {
        
        foreach ($this->getPredictionJudges() as $predictionJudge) {
            
            if ($predictionJudge->getJudge()->getId() === $id) {
                
                return $predictionJudge;
                
            }
            
        }
        
        return null;
        
    }
    
    public function calcPrediction($resultType="txt")
    {
        
        $upheld = $notupheld = 0;
        
        $result = "";
        
        foreach ($this->getPredictionJudges() as $predictionJudge) {
            
            if (!$predictionJudge->getJudge()->isRecused($this->getCourtcase()->getId())) {
                
                if ($predictionJudge->getAppealUpheld()) {

                    $upheld = $upheld + 1;

                } else {

                    $notupheld = $notupheld + 1;

                }
                
            }
        }
        
        if ($resultType == "txt") {
            
            if ($upheld > $notupheld) {

                $result = $upheld ." - ".$notupheld . " Appeal will be upheld";

            } else if ($upheld < $notupheld) {

                $result = $upheld ." - ".$notupheld . " Appeal will be dismissed";

            } else {

                $result = $upheld ." - ".$notupheld . " Lower court decision";

            }

            if ($this->getIsRuling()) {

                $result = str_replace("will be ", "", $result);

            }
            
        } else if ($resultType == "txtSimple") {
            
            if ($upheld > $notupheld) {

                return "upheld";

            } else if ($upheld < $notupheld) {

                return "dismissed";

            } else {

                return null;

            }
            
        }
        
        return $result;
        
    }
    
    
    public function generateResults()
    {
        
        $caseResults = [
            "casesPredictedCorrect" => 0, 
            "totalEntries" => 1, 
            "totalCases" => 0, 
            "individualJudges" => 0, 
            "totalJudges" => 0, 
            "score" => 0, 
            "maxScore" => 0, 
            "uberMaxScore" => 0,
            "leagueScore" => 0,
            ];
        
        if ($this->isPredictionCorrect()) {
            
            $caseResults["casesPredictedCorrect"] = 1;
            
        }
        
        $results = $this->countCorrectJudges();
        
        $caseResults["individualJudges"] = $results["correct"];
        
        $caseResults["score"] = $results["score"];
        
        $caseResults["maxScore"] = $this->getMaxScore();        
        
        $caseResults["totalJudges"] = count($this->getPredictionJudges());
               
        $caseResults["leagueScore"] = $this->getScore();
        
        return $caseResults;
        
    }
    
    public function getMaxScore()
    {
        
        return count($this->getPredictionJudges()) * self::POINTS_CORRECT + self::POINTS_BONUS;
        
    }
    
    public function isPredictionCorrect($ruling = null)
    {
        
        if (null === $ruling) {
            
            if ($this->getCourtcase()->getRuling()) {

                if ($this->calcPrediction("txtSimple") === $this->getCourtcase()->getRuling()->calcPrediction("txtSimple")) {

                    return true;

                }
            }
        }
        
        if (null !== $ruling) {
               
            if ($this->calcPrediction("txtSimple") === $ruling->calcPrediction("txtSimple")) {

                return true;

            }

        }
        
        return false; 
        
    }
    
    public function countCorrectJudges($ruling = null)
    {
        
        $correct = 0;
        $points = 0;
        
        if (null === $ruling) {
            
            if ($this->getCourtcase()->getRuling()) {

                foreach ($this->getCourtcase()->getRuling()->getPredictionJudges() as $predictionJudge) {

                    if (null !== $this->getPredictionForJudge($predictionJudge->getJudge()->getId()) && ($this->getPredictionForJudge($predictionJudge->getJudge()->getId())->getAppealUpheld() === $predictionJudge->getAppealUpheld())) {

                        $correct = $correct + 1;

                        $points = $points + self::POINTS_CORRECT;

                    }

                }

            }

            if ($this->isPredictionCorrect()) {

                $points = $points + self::POINTS_BONUS;

            }
        
        }
        
        if (null !== $ruling) {
            
            foreach ($ruling->getPredictionJudges() as $predictionJudge) {

                if (null !== $this->getPredictionForJudge($predictionJudge->getJudge()->getId()) && ($this->getPredictionForJudge($predictionJudge->getJudge()->getId())->getAppealUpheld() === $predictionJudge->getAppealUpheld())) {

                    $correct = $correct + 1;

                    $points = $points + self::POINTS_CORRECT;

                }

            }
            
            if ($this->isPredictionCorrect($ruling)) {

                $points = $points + self::POINTS_BONUS;

            }
            
        }
        
        $results ["correct"] = $correct;
        $results ["score"] = $points;
        
        return $results;
        
    }
    
    public function getScore($ruling = null)
    {
        if (null === $ruling) {
            
            if ($this->getCourtcase()->getIsLeague()) {

                $results = $this->countCorrectJudges();

                return $results["score"];

            } else {

                return 0;

            }
            
        } else {
            
            if ($this->getCourtcase()->getIsLeague()) {

                $results = $this->countCorrectJudges($ruling);

                return $results["score"];

            } else {

                return 0;

            }
            
        }
    }
    
    public function getFullScore($ruling = null)
    {

        $results = $this->countCorrectJudges($ruling);

        return $results["score"];
        
    }
    
    /**
     * Set isRuling
     *
     * @param boolean $isRuling
     *
     * @return Prediction
     */
    public function setIsRuling($isRuling)
    {
        $this->isRuling = $isRuling;

        return $this;
    }

    /**
     * Get isRuling
     *
     * @return boolean
     */
    public function getIsRuling()
    {
        return $this->isRuling;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     *
     * @return Prediction
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
     * @return Prediction
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
     * Add notification
     *
     * @param \AppBundle\Entity\Notification $notification
     *
     * @return Prediction
     */
    public function addNotification(\AppBundle\Entity\Notification $notification)
    {
        $this->notifications[] = $notification;

        return $this;
    }

    /**
     * Remove notification
     *
     * @param \AppBundle\Entity\Notification $notification
     */
    public function removeNotification(\AppBundle\Entity\Notification $notification)
    {
        $this->notifications->removeElement($notification);
    }

    /**
     * Get notifications
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getNotifications()
    {
        return $this->notifications;
    }
}
