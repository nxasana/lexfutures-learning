<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PredictionJudge
 *
 * @ORM\Table(name="prediction_judge")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\PredictionJudgeRepository")
 */
class PredictionJudge
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
     * @var bool
     *
     * @ORM\Column(name="appeal_upheld", type="boolean", nullable=true)
     */
    private $appealUpheld;
    
    /**
     * @var bool
     *
     * @ORM\Column(name="is_ruling", type="boolean", nullable=true)
     */
    private $isRuling;
    
    /**
     * 
     * @ORM\ManyToOne(targetEntity="Prediction", inversedBy="PredictionJudges")
     * @ORM\JoinColumn(name="prediction_id", referencedColumnName="id")
     */
    private $prediction;
    
    /**
     * 
     * @ORM\ManyToOne(targetEntity="Judge")
     * @ORM\JoinColumn(name="judge_id", referencedColumnName="id")
     */
    private $judge;
    
    /**
     * @var bool
     *
     * @ORM\Column(name="is_recused", type="boolean", nullable=true)
     */
    private $isRecused;
    
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
     * Set appealUpheld
     *
     * @param boolean $appealUpheld
     *
     * @return PredictionJudge
     */
    public function setAppealUpheld($appealUpheld)
    {
        $this->appealUpheld = $appealUpheld;

        return $this;
    }

    /**
     * Get appealUpheld
     *
     * @return boolean
     */
    public function getAppealUpheld()
    {
        return $this->appealUpheld;
    }

    /**
     * Set isRuling
     *
     * @param boolean $isRuling
     *
     * @return PredictionJudge
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
     * Set prediction
     *
     * @param \AppBundle\Entity\Prediction $prediction
     *
     * @return PredictionJudge
     */
    public function setPrediction(\AppBundle\Entity\Prediction $prediction = null)
    {
        $this->prediction = $prediction;

        return $this;
    }

    /**
     * Get prediction
     *
     * @return \AppBundle\Entity\Prediction
     */
    public function getPrediction()
    {
        return $this->prediction;
    }

    /**
     * Set judge
     *
     * @param \AppBundle\Entity\Judge $judge
     *
     * @return PredictionJudge
     */
    public function setJudge(\AppBundle\Entity\Judge $judge = null)
    {
        $this->judge = $judge;

        return $this;
    }

    /**
     * Get judge
     *
     * @return \AppBundle\Entity\Judge
     */
    public function getJudge()
    {
        return $this->judge;
    }

    /**
     * Set isRecused
     *
     * @param boolean $isRecused
     *
     * @return PredictionJudge
     */
    public function setIsRecused($isRecused)
    {
        $this->isRecused = $isRecused;

        return $this;
    }

    /**
     * Get isRecused
     *
     * @return boolean
     */
    public function getIsRecused()
    {
        return $this->isRecused;
    }
}
