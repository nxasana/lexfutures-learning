<?php

namespace AppBundle\Service;

//use Doctrine\ORM\EntityManager;
use AppBundle\Entity\User;
use AppBundle\Entity\Courtcase;
use AppBundle\Entity\Prediction;
use AppBundle\Entity\PredictionJudge;
use AppBundle\Entity\Jurisdiction;
use AppBundle\Entity\Season;
use AppBundle\Entity\Helper\PointsTableHelper;

class Util
{
    
    /**
     *
     * @var EntityManager
     */
    protected $em;
    
    public function __construct(\Doctrine\Common\Persistence\ObjectManager $entityManager)
    {
        
        $this->em = $entityManager;
        
    }
    
    public function getJudgesAccuracy(Jurisdiction $jurisdiction)
    {
        
        $stats = $judges = array();
        
        foreach ($jurisdiction->getJudges() as $judge) {
            
            $stats[$judge->getId()] = ["judge" => $judge, "correct" => 0, "incorrect" => 0 , "total" => 0];
            
        }
        
        foreach ($jurisdiction->getCourtcases() as $case) {
            
            if ($case->getRuling()) {
                
                $ruling = $case->getRuling();
                
                foreach ($case->getUserPredictions() as $prediction) {
                    
                    foreach ($case->getJudges() as $judge) {
                        
                        $guess = $this->isCorrect($judge, $ruling, $prediction);
                        
                        if ($guess === "correct") {
                            
                            $stats[$judge->getId()]["correct"] = $stats[$judge->getId()]["correct"] + 1;
                            
                            $stats[$judge->getId()]["total"] = $stats[$judge->getId()]["total"] + 1;
                            
                        } else if ($guess === "incorrect") {
                            
                            $stats[$judge->getId()]["incorrect"] = $stats[$judge->getId()]["incorrect"] + 1;
                            
                            $stats[$judge->getId()]["total"] = $stats[$judge->getId()]["total"] + 1;
                            
                        }
                        
                    }
                    
                }
                
            }
            
        }
        
        return $stats;
        
    }
    
    public function isCorrect($judge, $ruling, $prediction)
    {
        
        if (null === $ruling->getPredictionForJudge($judge->getId()) || null === $prediction->getPredictionForJudge($judge->getId()) || $judge->isRecused($prediction->getCourtcase()->getId())) {
            
            return "na";
            
        }
        
        if ($ruling->getPredictionForJudge($judge->getId())->getAppealUpheld() === $prediction->getPredictionForJudge($judge->getId())->getAppealUpheld()) {
            
            return "correct";
            
        } else {
            
            return "incorrect";
            
        }
        
        return "correct";
        
    }
    
    public function getPointsOverView(User $user, Jurisdiction $jurisdiction, Season $season)
    {
        
        $stats = ["points" => 0, "leaguePoints" => 0, "correctPredictions" => 0, "totalPredictions" => 0, "correctJudgePredictions" => 0, "totalJudgePredictions" => 0];
        
        $leaderboardStats = $this->leaderboardStats($user, $jurisdiction, $season);
                
        $stats["correctPredictions"] = $leaderboardStats["casesPredictedCorrect"];
        
        $stats["totalPredictions"] = $leaderboardStats["totalEntries"];
        
        $stats["points"] = $this->getUserFullscore($user, $jurisdiction, $season);
        
        $stats["leaguePoints"] = $leaderboardStats["leagueScore"];
        
        $stats["correctJudgePredictions"] = $leaderboardStats["individualJudges"];
        
        $stats["totalJudgePredictions"] = $leaderboardStats["totalJudges"];
        
        return $stats;
        
    }
    
    
    public function getUserFullscore($user, $jurisdiction, $season=null)
    {
        
        $score = 0;
        
        foreach ($user->getPredictions() as $prediction) {
            
            if (null === $season || $season === $prediction->getCourtcase()->getSeason()) {
            
                if ($prediction->getCourtcase()->getJurisdiction() === $jurisdiction && !$prediction->getIsRuling()) {

                    $score = $score + $prediction->getFullScore(); 

                }
            
            }
            
        }
        
        return $score;
        //getFullScore
    }
    
    public function getDashboardStats($user)
    {
        
        $cases = $this->getDashboardCases();
        
        $registrations = $this->findRecentRegistrations("ROLE_FRONT_USER", $user);
        
        $predictions = $this->findRecentPredictions($user);
        
        $logins = $this->findRecentLogins("ROLE_FRONT_USER", $user);
        
        $stats = array(
            'totalPredictions' => $this->countPredictions(),
            'recentlyHeardCases' => $cases["recent"],
            'upcomingCases' => $cases["upcoming"],
            'decidedCases' => $cases["decided"],
            'totalUsers' => $this->findByRole("ROLE_FRONT_USER"),
            'recentRegistrations' => $registrations,
            'recentPredictions' => $predictions,
            'recentLogins' => $logins
        );
        
        return $stats;
        
    }
    
    public function findRecentPredictions($user)
    {
        
        $qb = $this->em->createQueryBuilder();
        $qb->select('u')
            ->from('AppBundle:Prediction', 'u')
            ->andWhere("u.created > '".$user->getLoginOffsetTimer()->format('Y-m-d H:i:s')."'");

        $results = $qb->getQuery()->getResult();
        
        if (null == $results) {
            
            return 0;
            
        } else {
            
            return count($results);
            
        }
        
    }
    
    public function findRecentLogins($role, $user)
    {
                        
        $qb = $this->em->createQueryBuilder();
        $qb->select('u')
            ->from('AppBundle:User', 'u')
            ->where('u.roles LIKE :roles')
            ->andWhere("u.lastLogin > '".$user->getLoginOffsetTimer()->format('Y-m-d H:i:s')."'")
            ->setParameter('roles', '%"'.$role.'"%');

        $results = $qb->getQuery()->getResult();
        
        if (null == $results) {
            
            return 0;
            
        } else {
            
            return count($results);
            
        }
        
    }
    
    public function findRecentRegistrations($role, $user)
    {
                        
        $qb = $this->em->createQueryBuilder();
        $qb->select('u')
            ->from('AppBundle:User', 'u')
            ->where('u.roles LIKE :roles')
            ->andWhere("u.created > '".$user->getLoginOffsetTimer()->format('Y-m-d H:i:s')."'")
            ->setParameter('roles', '%"'.$role.'"%');

        $results = $qb->getQuery()->getResult();
        
        if (null == $results) {
            
            return 0;
            
        } else {
            
            return count($results);
            
        }
    }
    
    /**
    * @param string $role
    *
    * @return array
    */
    public function findByRole($role)
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('u')
            ->from('AppBundle:User', 'u')
            ->where('u.roles LIKE :roles')
            ->setParameter('roles', '%"'.$role.'"%');

        return $qb->getQuery()->getResult();
    }
    
    public function getDashboardCases() {
        
        $cases = $this->em->getRepository('AppBundle:Courtcase')->findAll();
        
        $results = array("recent" => array(), "upcoming" => array(), "decided" => array());
        
        foreach ($cases as $case) {
            
            if (($case->getStatusToString() === 'upcoming') && (!$case->getRemovedFromRole())) {
                
                $results["upcoming"][] = $case;
                
            } 
            
            if (($case->getStatusToString() === 'recently-heard') && (!$case->getRemovedFromRole())) {
                
                $results["recent"][] = $case;
                
            } 
            
            if (($case->getStatusToString() === 'decided') && (!$case->getRemovedFromRole())) {
                
                $results["decided"][] = $case;
                
            } 
            
        }
        
        return $results;
                
    }
    
    public function countPredictions()
    {
        /*
        $query = $this->em->createQuery('SELECT u FROM AppBundle:Prediction u LEFT JOIN AppBundle:CourtCase b ON b.id=u.courtcase_id WHERE u.isRuling IS NULL AND b.status!=4');
        
        $predictions = $query->getResult();
        
        return count($predictions);
        */
        
        $qb = $this->em->createQueryBuilder();
        
        $qb->select('u')
            ->from('AppBundle:Prediction', 'u')
            ->leftJoin('u.courtcase', 'c')
            ->where('c.status != ?1')
            ->setParameter(1, 4);
        
        $query = $qb->getQuery();
        
        $result = $query->getResult();
        
        return count($result);
        
    }
    
    function generateRandomString($length = 10) {
        
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        
        $charactersLength = strlen($characters);
        
        $randomString = '';
        
        for ($i = 0; $i < $length; $i++) {
            
            $randomString .= $characters[rand(0, $charactersLength - 1)];
            
        }
        
        return $randomString;
        
    }
    
    public function test()
    {
        
        $case = $this->em->getRepository('AppBundle:Courtcase')->findOneById(1);
        
        return $case;
        
    }
    
    public function findBoardPosition(User $user, $leaderBoard)
    {
        
        $counter = 1;
        
        foreach ($leaderBoard as $leader) {
            
            if ($leader["id"] === $user->getId()) {
                
                return $counter;
                
            }
            
            $counter += 1;
        }
        
        return count($leaderBoard);
    }
    
    public function newPointsTable(User $user, Jurisdiction $jurisdiction, $leaderboard)
    {
        
        $categoryData = null;
        
        if ($user->getOccupation()->getCategoryType()->getId() === 1) {
            
            $categoryData = $leaderboard["lawProfessionals"];
                    
        } else if ($user->getOccupation()->getCategoryType()->getId() === 2) {
            
            $categoryData = $leaderboard["students"];
            
        } else if ($user->getOccupation()->getCategoryType()->getId() === 3) {
            
            $categoryData = $leaderboard["academics"];
            
        } else if ($user->getOccupation()->getCategoryType()->getId() === 4) {
            
            $categoryData = $leaderboard["other"];
            
        }
                
        $pointsTable = [
            "total" => ["position" => $this->findBoardPosition($user, $leaderboard["overall"]["aggregate"]), "total" => count($leaderboard["overall"]["aggregate"])],
            "totalleague" => ["position" => $this->findBoardPosition($user, $leaderboard["overall"]["average"]), "total" => count($leaderboard["overall"]["average"])],
            "category" => ["name" => $user->getCategory(), "position" => $this->findBoardPosition($user, $categoryData["aggregate"]), "total" => count($categoryData["aggregate"])],
            "categoryleague" => ["name" => $user->getCategory(), "position" => $this->findBoardPosition($user, $categoryData["average"]), "total" => count($categoryData["average"])]
            ];
        
        return $pointsTable;
        /*
        $totalPlayers = $categoryPlayers = $pointsTable = array(); // ids of all the users that have made a prediction
        
        // get all the players that have submitted predictions
        foreach ($jurisdiction->getCourtcases() as $courtcase) {
            
            foreach ($courtcase->getPredictions() as $prediction) {
                
                if (!$prediction->getIsRuling() && !in_array($prediction->getUser()->getId(), $totalPlayers)) {
                    
                    $totalPlayers[] = $prediction->getUser()->getId();
                    
                }
                
                if (!$prediction->getIsRuling() && !in_array($prediction->getUser()->getId(), $categoryPlayers)) {
                    
                    if ($prediction->getUser()->getCategory() === $user->getCategory()) {
                        
                        $categoryPlayers[] = $prediction->getUser()->getId();
                        
                    }
                }
                
            }
            
        }
        
        var_dump($totalPlayers);
        
        echo "<br/>======================<br/>";
        
        var_dump($categoryPlayers);
        
        echo "<br/>======================<br/>";
        
        die();
         * 
         */
    }
    
    public function pointsTable(User $user, Jurisdiction $jurisdiction)
    {
        
        $players = $pointsTable = array(); // ids of all the users that have made a prediction
        
        // get all the players that have submitted predictions
        foreach ($jurisdiction->getCourtcases() as $courtcase) {
            
            foreach ($courtcase->getPredictions() as $prediction) {
                
                if (!$prediction->getIsRuling() && !in_array($prediction->getUser()->getId(), $players)) {
                    
                    $players[] = $prediction->getUser()->getId();
                    
                }
                
            }
            
        }
        
        
        foreach ($players as $playerid) {
            
            $player = $this->em->getRepository('AppBundle:User')->findOneById($playerid);
            
            $stats = $this->leaderboardStats($player, $jurisdiction);
            
            $helpertron = new PointsTableHelper();
            
            $helpertron->setUser($player);
            
            $helpertron->setPoints($stats["score"]);
            
            $helpertron->setTotalPoints($stats["maxScore"]);
            
            if ($stats["maxScore"] == 0) {
                
                $helpertron->setMaxPoints(0); // this is average
                
            } else {
            
                $helpertron->setMaxPoints($stats["score"]/$stats["maxScore"]); // this is average
            
            }
            
            $pointsTable[] = $helpertron;
            
        }
        
        $results = array("pos" => 0, "posTotal" => 0, "posOverall" => 0, "aggPos" => 0, "aggPosTotal" => 0);
        
        /*
        foreach ($pointsTable as $pointsHelper) {
            
            echo $pointsHelper->getUser()->getId().":".$pointsHelper->getPoints().":".$pointsHelper->getTotalPoints().":".$pointsHelper->getMaxPoints()."<br/>";
            
        }
        */
        usort($pointsTable, array($this, 'gamesSortRev'));
        
        //echo "============<br/>";
        
        $counter = 0;
        
        foreach ($pointsTable as $pointsHelper) {
            
            $counter = $counter + 1;
            
            if ($pointsHelper->getUser() === $user) {
                
                $results["pos"] = $counter;
                
                //echo $pointsHelper->getUser()->getId();
                
            }
            
            //echo $pointsHelper->getUser()->getId().":".$pointsHelper->getPoints().":".$pointsHelper->getTotalPoints().":".$pointsHelper->getMaxPoints()."<br/>";
            
        }
        
        $results["posTotal"] = $counter;
        $results["posOverall"] = $counter;
        
        usort($pointsTable, array($this, 'gamesSortRevAggregate'));
        
        $counter = 0;
        
        foreach ($pointsTable as $pointsHelper) {
            
            $counter = $counter + 1;
            
            if ($pointsHelper->getUser() === $user) {
                
                $results["aggPos"] = $counter;
                
                
            }
            
        }
        
        $results["aggPosTotal"] = $counter;
        
        return $results;
        
    }
    
    public function predictionRelevant($type, Prediction $prediction)
    {
        
        if ($prediction->getIsRuling()) {
            
            return false;
            
        }
        
        if ($type === 'overall') {
            
            if ($prediction->getCourtcase()->getIsLeague() || true) {
                
                return true; // for the type 'overall' all predictions are relevant
                
            }
            
            
        } else if ($type === 'lawProfessionals') {
            
            if (null !== $prediction && null !== $prediction->getUser()->getOccupation() && $prediction->getUser()->getOccupation()->getCategoryType()->getId() === 1 && ($prediction->getCourtcase()->getIsLeague() || true)) {
                
                return true;
                
            }
            
        } else if ($type === 'students') {
            
            if (null !== $prediction && null !== $prediction->getUser()->getOccupation() && $prediction->getUser()->getOccupation()->getCategoryType()->getId() === 2 && ($prediction->getCourtcase()->getIsLeague() || true)) {
                
                return true;
                
            }
            
        } else if ($type === 'academics') {
            
            if (null !== $prediction && null !== $prediction->getUser()->getOccupation() && $prediction->getUser()->getOccupation()->getCategoryType()->getId() === 3 && ($prediction->getCourtcase()->getIsLeague() || true)) {
                
                return true;
                
            }
            
        } else if ($type === 'other') {
            
            if (null !== $prediction && null !== $prediction->getUser()->getOccupation() && $prediction->getUser()->getOccupation()->getCategoryType()->getId() === 4 && ($prediction->getCourtcase()->getIsLeague() || true)) {
                
                return true;
                
            }
            
        } else if ($type === 'open') {
              
            return true;

        }
        
        return false;
        
    }
    
    public function getLeaderBoard($type, $limit=10, Jurisdiction $jurisdiction, $season=null)
    {
        
        $leaderboard = array();
        
        $players = array(); // ids of all the users that have made a prediction
        
        $leaderboard['aggregate'] = array();
        $leaderboard['average'] = array();
        
        if (null === $season) {
            
            $season = $jurisdiction->getCurrentSeason();
            
        }
                
        // get all the players that have submitted predictions
        //foreach ($jurisdiction->getCourtcases() as $courtcase) 
        foreach ($season->getCourtcases() as $courtcase) {
            
            // we are only interested in the predictions of courtcases that have a ruling
            // we are also only interested in cases that are league (except for in the open category)
            if ($courtcase->getRuling() !== null && ($courtcase->getIsLeague() || $type === "open" || true)) {
                
                foreach ($courtcase->getPredictions() as $prediction) {
                    
                    if (!$prediction->getIsRuling() && !in_array($prediction->getUser()->getId(), $players) && $this->predictionRelevant($type, $prediction)) {
                        if ($prediction->getUser()->isEnabled()) {
                            $players[] = $prediction->getUser()->getId();
                        }
                    } 
                }
                
            }
        }
                
        foreach ($players as $playerid) {
            
            $player = $this->em->getRepository('AppBundle:User')->findOneById($playerid);
            
            if (null === $player || $player->getIsFixture()) {
                
                continue;
                
            }
            
            $leaderboardDetails = $this->getLeaderBoardDetails($player, $jurisdiction, $type, $season);
            
            $leaderboard['aggregate'][] = array(
                'id' => $player->getId(),
                'name' => $player->getFullname(),
                'totalPredictions' => $leaderboardDetails["predictions"],
                'correctCourtPredictions' => $leaderboardDetails["correct"],
                'points' => $leaderboardDetails["allscore"],
                'avePoints' => $leaderboardDetails["avescore"],
                //'allpoints' => $leaderboardDetails["allscore"],
            );
            
            $leaderboard['average'][] = array(
                'id' => $player->getId(),
                'name' => $player->getFullname(),
                'totalPredictions' => $leaderboardDetails["predictions"],
                'correctCourtPredictions' => $leaderboardDetails["correct"],
                'points' => $leaderboardDetails["score"],
                'avePoints' => $leaderboardDetails["avescore"],
                //'allpoints' => $leaderboardDetails["allscore"],
            );
        }
        
        if (count($leaderboard['aggregate']) === 0) {
            
            $leaderboard['aggregate'] = $this->randomLeaderBoard($jurisdiction, 'aggregate');
            
        }
        
        if (count($leaderboard['average']) === 0) {
            
            $leaderboard['average'] = $this->randomLeaderBoard($jurisdiction, 'average');
            
        }
        
        usort($leaderboard['aggregate'], array($this, 'pointsSortRev'));
        
        usort($leaderboard['average'], array($this, 'pointsSortRev'));
                
        return $leaderboard;
    }
    
    public function randomLeaderBoard($jurisdiction, $type)
    {
        
        $players = array(); // ids of all the users that have made a prediction
        
        $leaderboard['aggregate'] = array();
        $leaderboard['average'] = array();
        
        // get all the players that have submitted predictions
        foreach ($jurisdiction->getCourtcases() as $courtcase) {
            
            // we are only interested in the predictions of courtcases that have a ruling
            // we are also only interested in cases that are league (except for in the open category)
            if (true) {
            
                foreach ($courtcase->getPredictions() as $prediction) {

                    if (!$prediction->getIsRuling() && !in_array($prediction->getUser()->getId(), $players) ) {

                        $players[] = $prediction->getUser()->getId();

                    } 

                }
                
            }
        }
        
        foreach ($players as $playerid) {
            
            $player = $this->em->getRepository('AppBundle:User')->findOneById($playerid);
            
            if (null === $player || $player->getIsFixture()) {
                
                continue;
                
            }
            
            $predictions = $this->countUserPredictions($player, $jurisdiction, true);
            
            
            $leaderboard['aggregate'][] = array(
                'id' => rand(102003,902003),
                'name' => $player->getFullname(),
                'totalPredictions' => $predictions,
                'correctCourtPredictions' => 0,
                'points' => 0,
                'avePoints' => 0,
                'allpoints' => 0,
            );
            
            $leaderboard['average'][] = array(
                'id' => rand(102003,902003),
                'name' => $player->getFullname(),
                'totalPredictions' => $predictions,
                'correctCourtPredictions' => 0,
                'points' => 0,
                'avePoints' => 0,
                'allpoints' => 0,
            );
            
            
        }
        
        if ($type === 'aggregate') {

            return $leaderboard['aggregate'];

        }

        if ($type === 'average') {

            return $leaderboard['average'];

        }
        
    }
    
    public function countUserPredictions($player, $jurisdiction, $leagueOnly)
    {
        
        $predictions = array();
        
        foreach ($player->getPredictions() as $prediction) {
            
            if ($prediction->getCourtcase()->getJurisdiction() === $jurisdiction) {
                
                $predictions[] = $prediction;
                
            }
        }
        
        return count($predictions);
    }
    
    public function getLeaderBoardDetails($user, $jurisdiction, $type, $season)
    {
        
        $predictions = array();
        
        $decidedPredictions = array();
        
        $score = $maxscore = $correct = $allscore = 0;
        
        foreach ($user->getPredictions() as $prediction) {            
            
            if ($prediction->getCourtcase()->getJurisdiction() === $jurisdiction && !$prediction->getIsRuling() && $prediction->getCourtcase()->getSeason() === $season) {
                                
                $predictions[] = $prediction;
                
                $score = $score + $prediction->getScore();
                
                $allscore = $allscore + $prediction->getFullScore();
                
                //echo $prediction->getScore()."<br/>";
                
                $maxscore = $maxscore + $prediction->getMaxScore();
                
                if ($prediction->isPredictionCorrect()) {
                    
                    $correct = $correct + 1;
                    
                }
                
                if ($prediction->getCourtcase()->getRuling()) {
                    
                    $decidedPredictions[] = $prediction;
                    
                }
                
            }
            
        }
        
        $avescore = round($score / count($decidedPredictions), 2);
        
        $leaderboardDetails = array("score" => $score, "predictions" => count($predictions), "correct" => $correct, 'avescore' => $avescore, "allscore" => $allscore);
        
        return $leaderboardDetails;
        
    }
    
    private static function gamesSort($a, $b) {
        if ($a->getPoints() == $b->getPoints()) {
            return 0;
        }
        return ($a->getPoints() < $b->getPoints()) ? -1 : 1;
    }

    private static function gamesSortRev($a, $b) {
        if ($a->getPoints() == $b->getPoints()) {
            return 0;
        }
        return ($a->getPoints() > $b->getPoints()) ? -1 : 1;
    }
    
    private static function gamesSortRevAggregate($a, $b) {
        if ($a->getMaxPoints() == $b->getMaxPoints()) {
            return 0;
        }
        return ($a->getMaxPoints() > $b->getMaxPoints()) ? -1 : 1;
    }
    
    private static function pointsSortRev($a, $b) {
        if ($a["points"] == $b["points"]) {
            return 0;
        }
        return ($a["points"] > $b["points"]) ? -1 : 1;
    }
    
    private static function pointsMaxSortRev($a, $b) {
        if ($a["avePoints"] == $b["avePoints"]) {
            return 0;
        }
        return ($a["avePoints"] > $b["avePoints"]) ? -1 : 1;
    }
    
    
    public function leaderboardStats(User $user, Jurisdiction $jurisdiction, $season=null)
    {
        
        $leaderboard = ["casesPredictedCorrect" => 0, "totalCases" => 0, "individualJudges" => 0, "totalJudges" => 0, "score" => 0, "maxScore" => 0, "totalEntries" => 0, "leagueScore" => 0];
        
        //$leaderboard["casesPredictedCorrect"] = 0;
        
        if (null === $season) {
        
            foreach ($jurisdiction->getCourtcases() as $courtcase) {

                if (null !== $courtcase->getRuling()) {

                    $leaderboard["totalCases"] = $leaderboard["totalCases"] + 1;

                    if ($this->getUserPrediction($user, $courtcase)) {

                        $caseResults = $this->getCaseResults($user, $courtcase);

                        $leaderboard["totalEntries"] = $leaderboard["totalEntries"] + $caseResults["totalEntries"];

                        $leaderboard["casesPredictedCorrect"] = $leaderboard["casesPredictedCorrect"] + $caseResults["casesPredictedCorrect"];

                        $leaderboard["individualJudges"] = $leaderboard["individualJudges"] + $caseResults["individualJudges"];

                        $leaderboard["totalJudges"] = $leaderboard["totalJudges"] + $caseResults["totalJudges"];

                        $leaderboard["score"] = $leaderboard["score"] + $caseResults["score"];

                        $leaderboard["leagueScore"] = $leaderboard["leagueScore"] + $caseResults["leagueScore"];

                        $leaderboard["maxScore"] = $leaderboard["maxScore"] + $caseResults["maxScore"];

                    }
                }
            }
        
        } else {
            
            foreach ($season->getCourtcases() as $courtcase) {

                if (null !== $courtcase->getRuling()) {

                    $leaderboard["totalCases"] = $leaderboard["totalCases"] + 1;

                    if ($this->getUserPrediction($user, $courtcase)) {

                        $caseResults = $this->getCaseResults($user, $courtcase);

                        $leaderboard["totalEntries"] = $leaderboard["totalEntries"] + $caseResults["totalEntries"];

                        $leaderboard["casesPredictedCorrect"] = $leaderboard["casesPredictedCorrect"] + $caseResults["casesPredictedCorrect"];

                        $leaderboard["individualJudges"] = $leaderboard["individualJudges"] + $caseResults["individualJudges"];

                        $leaderboard["totalJudges"] = $leaderboard["totalJudges"] + $caseResults["totalJudges"];

                        $leaderboard["score"] = $leaderboard["score"] + $caseResults["score"];

                        $leaderboard["leagueScore"] = $leaderboard["leagueScore"] + $caseResults["leagueScore"];

                        $leaderboard["maxScore"] = $leaderboard["maxScore"] + $caseResults["maxScore"];

                    }
                }
            }
            
        }
        
        return $leaderboard;
        
    }
    
    public function getCaseResults(User $user, Courtcase $courtcase)
    {
        
        //$caseResults = ["casesPredictedCorrect" => 0, "totalCases" => 0, "individualJudges" => 0, "totalJudges" => 0];
        
        $prediction = $this->getUserPrediction($user, $courtcase);
        
        $caseResults = $prediction->generateResults();
        
        return $caseResults;
        
    }
    
    public function getUserPrediction(User $user, Courtcase $case)
    {
        
        $qb = $this->em->createQueryBuilder();
            
        $query = $this->em->createQuery(
            "
            SELECT dls
            FROM AppBundle:Prediction dls
            WHERE dls.user = :id AND dls.courtcase = :courtcase
            "
        );
        
        $query->setParameter('id', $user->getId());
        
        $query->setParameter('courtcase', $case->getId());
        
        $results = $query->getResult();
        
        if (count($results) === 1) {
            
            return $results[0];
            
        } else {
            
            return null;
            
        }
        
    }
    
    public function validatePrediction(Prediction $prediction, $postArray)
    {
        
        $validPredictions = 0;
        
        foreach ($postArray as $judgePrediction) {
            
            if ($judgePrediction !== null && trim($judgePrediction) !== "") {
                
                $validPredictions = $validPredictions + 1;
                
            }
            
        }
        
        
        if ($validPredictions >= $prediction->getRequiredPredictions()) {
            
            return true;
            
        } else {
            
            return false;
            
        }
        
        return false;
        
    }
    
    public function updatePrediction(Prediction $prediction, $postArray)
    {
        
        $this->em->persist($prediction);
        
        foreach ($postArray as $key => $value) 
        {

            //echo $key."|".$value."<br/>";
            //$this->updateJudgePrediction($prediction, $key, $value);
            $predictionJudge = $prediction->getPredictionForJudge($this->explodeKeyFromString($key, 1, "_"));
            
            if ($value === "0") {
                
                $predictionJudge->setAppealUpheld(false);
                
            } else if ($value === "1") {
                
                $predictionJudge->setAppealUpheld(true);
                
            } else {
                
                $predictionJudge->setAppealUpheld(null);
                
            }
            
            $this->em->persist($predictionJudge);
            //echo $predictionJudge->getJudge()->getName()."<br/>";
            
        }
        
        $this->em->flush();
    }
    
    public function updateJudgePrediction()
    {
        
        $prediction->getPredictionForJudge();
        
    }
    
    public function explodeKeyFromString($str, $key, $delimiter)
    {
        
        $parts = explode($delimiter, $str);
        
        return (int)$parts[$key];
        
    }
    
    public function initPrediction(Courtcase $case, User $user=null)
    {
        
        $prediction = new Prediction();
        
        $prediction->setCourtcase($case);
        
        $prediction->setUser($user);
        
        foreach ($case->getJudges() as $judge) {
            
            $predictionJudge = new PredictionJudge();
            
            $predictionJudge->setJudge($judge);
            
            $predictionJudge->setPrediction($prediction);
            
            $prediction->addPredictionJudge($predictionJudge);
            
        }
        
        return $prediction;
        
    }
    
    public function finishPrediction(Prediction $prediction)
    {
        
        return $prediction;
        
    }
}