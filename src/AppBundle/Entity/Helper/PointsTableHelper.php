<?php

namespace AppBundle\Entity\Helper;

use Doctrine\Common\Collections\ArrayCollection;

class PointsTableHelper
{
    
    private $user;
    
    private $points;
    
    private $totalPoints;
    
    private $maxPoints;

    
    public function __construct()
    {
        
    }
        
    public function setUser($user)
    {
        $this->user = $user;
        
        return $this;
    }
    
    public function getUser()
    {
        return $this->user;
    }
    
    public function setPoints($url)
    {
        $this->points = $url;
        
        return $this;
    }
    
    public function getPoints()
    {
        return $this->points;
    }
    
    public function setTotalPoints($url)
    {
        $this->totalPoints = $url;
        
        return $this;
    }
    
    public function getTotalPoints()
    {
        return $this->totalPoints;
    }
    
    public function setMaxPoints($url)
    {
        $this->maxPoints = $url;
        
        return $this;
    }
    
    public function getMaxPoints()
    {
        return $this->maxPoints;
    }
}
