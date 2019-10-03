<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use AppBundle\Service\Util;

use AppBundle\Entity\Jurisdiction;
use AppBundle\Entity\Prediction;

class LeagueController extends Controller
{
    
    /**
     * @Route("/leaderboard/past-seasons/{slug}", name="lexfutures_leaderboard_past_seasons")
     * @param String $slug
     * @param Request $request
     */
    public function leaderboardPastSeasonsAction(Request $request, $slug)
    {
        
        $util = $this->get(Util::class);
        
        $em = $this->getDoctrine()->getManager();
        
        $jurisdiction = $this->getDoctrine()->getRepository('AppBundle:Jurisdiction')->findOneBySlug($slug);
        
        if (null === $jurisdiction) {
            
            return $this->redirectToRoute('lexfutures_dashboard');
            
        }
        
        $seasons = array();
        $exclusionSeasons = [1,3,5]; // an array of seasons ids that we're going to exclude.

        foreach ($jurisdiction->getSeasons() as $season) {
            
            if (!in_array($season->getId(), $exclusionSeasons)) {
                
                $seasons[] = $season;
                
            }
            
        }
        
        return $this->render('AppBundle:league:leaderboardPastSeasons.html.twig', [
            'jurisdiction' => $jurisdiction,
            'pastSeasons' => $seasons,
        ]);
        
    }
    
    /**
     * @Route("/leaderboard/{slug}/{id}", defaults={"id"=null}, name="lexfutures_leaderboard")
     * @param integer $id
     * @param Request $request
     */
    public function leaderboardAction(Request $request, $slug, $id)
    {
                
        $util = $this->get(Util::class);
        
        $em = $this->getDoctrine()->getManager();
        
        $jurisdiction = $this->getDoctrine()->getRepository('AppBundle:Jurisdiction')->findOneBySlug($slug);
        
        if (null === $jurisdiction) {
            
            return $this->redirectToRoute('lexfutures_dashboard');
            
        }
        
        $leaderboard = array();
        
        if (null === $id) {
            
            $season = $jurisdiction->getCurrentSeason();
            
        } else {
            
            $season =  $this->getDoctrine()->getRepository('AppBundle:Season')->findOneById((int) $id);// hardcoding this to test
            
        }
        
        if (null === $season || $season->getJurisdiction() !== $jurisdiction) {
            $season = $jurisdiction->getCurrentSeason();
        }
        //$season =  $this->getDoctrine()->getRepository('AppBundle:Season')->findOneById(1);// hardcoding this to test
        
        $leaderboard['overall'] = $util->getLeaderBoard('overall', 10, $jurisdiction, $season);
        $leaderboard['lawProfessionals'] = $util->getLeaderBoard('lawProfessionals', 10, $jurisdiction, $season);
        $leaderboard['students'] = $util->getLeaderBoard('students', 10, $jurisdiction, $season);
        $leaderboard['academics'] = $util->getLeaderBoard('academics', 10, $jurisdiction, $season);
        $leaderboard['other'] = $util->getLeaderBoard('other', 10, $jurisdiction, $season);
        $leaderboard['open'] = $util->getLeaderBoard('open', 10, $jurisdiction, $season);
        
        
        if ($this->getUser()) {
            
            $stats = $util->leaderboardStats($this->getUser(), $jurisdiction, $season);
            
            $pointsTable = $util->pointsTable($this->getUser(), $jurisdiction);
            
            $newPointsTable = $util->newPointsTable($this->getUser(), $jurisdiction, $leaderboard);
            
        } else {
            
            $stats = null;
            
            $pointsTable = $newPointsTable = null;
            
        }        
        
        return $this->render('AppBundle:league:leaderboard.html.twig', [
            'jurisdiction' => $jurisdiction, 
            'stats' => $stats,
            'pointstable' => $pointsTable,
            'newPointsTable' => $newPointsTable,
            'leaderboard' => $leaderboard,
            'season' => $season
        ]);
        
    }
    
    /**
     * @Route("/leaderboard/archived/{slug}/{leaderboardSlug}", name="lexfutures_leaderboard_archived")
     * @param string $slug
     * @param string $leaderboardSlug
     * @param Request $request
     */
    public function leaderboardArchivedAction(Request $request, $slug, $leaderboardSlug)
    {
        
        $util = $this->get(Util::class);
        
        $em = $this->getDoctrine()->getManager();
        
        $jurisdiction = $this->getDoctrine()->getRepository('AppBundle:Jurisdiction')->findOneBySlug($slug);
        
        $season = $this->getDoctrine()->getRepository('AppBundle:Season')->findOneBySlug($leaderboardSlug);
        
        if (null === $jurisdiction) {
            
            return $this->redirectToRoute('lexfutures_dashboard');
            
        }
        
        if (null === $season || $season->getJurisdiction() !== $jurisdiction) {
            
            return $this->redirectToRoute('lexfutures_leaderboard', ['slug' => $jurisdiction->getSlug()]);
            
        }
        
        $leaderboard = array();
                
        $leaderboard['overall'] = $util->getLeaderBoard('overall', 10, $jurisdiction, $season);
        $leaderboard['lawProfessionals'] = $util->getLeaderBoard('lawProfessionals', 10, $jurisdiction, $season);
        $leaderboard['students'] = $util->getLeaderBoard('students', 10, $jurisdiction, $season);
        $leaderboard['academics'] = $util->getLeaderBoard('academics', 10, $jurisdiction, $season);
        $leaderboard['other'] = $util->getLeaderBoard('other', 10, $jurisdiction, $season);
        $leaderboard['open'] = $util->getLeaderBoard('open', 10, $jurisdiction, $season);
        
        if ($this->getUser()) {
            
            $stats = $util->leaderboardStats($this->getUser(), $jurisdiction);
            
            $pointsTable = $util->pointsTable($this->getUser(), $jurisdiction);
            
            $newPointsTable = $util->newPointsTable($this->getUser(), $jurisdiction, $leaderboard);
            
        } else {
            
            $stats = null;
            
            $pointsTable = $newPointsTable = null;
            
        }        
        
        return $this->render('AppBundle:league:leaderboard.html.twig', [
            'jurisdiction' => $jurisdiction, 
            'stats' => $stats,
            'pointstable' => $pointsTable,
            'newPointsTable' => $newPointsTable,
            'leaderboard' => $leaderboard,
            'season' => $season
        ]);
        
    }
    
}
