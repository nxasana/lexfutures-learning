<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use AppBundle\Service\Util;

use AppBundle\Entity\Jurisdiction;
use AppBundle\Entity\Prediction;

class CasesController extends Controller
{
    
    /**
     * @Route("/prediction/{slug}", name="lexfutures_prediction_edit")
     * @param string $slug
     * @param Request $request
     */
    public function predictionAction(Request $request, $slug)
    {
        
        $util = $this->get(Util::class);
        
        $em = $this->getDoctrine()->getManager();
        
        $case = $this->getDoctrine()->getRepository('AppBundle:Courtcase')->findOneBySlug($slug);
        
        if (null === $case) {
            
            $this->get('session')->getFlashBag()->add("errormsg", "We could not find that case!");
            
            return $this->redirectToRoute('lexfutures_dashboard');
            
        }
                
        if ($case->getStatusToString() === 'decided' || $case->getRemovedFromRole()) {
            
            $this->get('session')->getFlashBag()->add("errormsg", "You may no longer make a prediction for that case!");
            
            return $this->redirectToRoute('lexfutures_cases', ['slug' => $case->getJurisdiction()->getSlug()]);
            
        }
        
        if ($this->getUser()) {
            
            $prediction = $util->getUserPrediction($this->getUser(), $case);
        
            if (null === $prediction) {

                $prediction = $util->initPrediction($case, $this->getUser());

            } 
            
        } else {
            
            $prediction = $util->initPrediction($case, null);
            
        }
        
        
        $errors = array();
        
        if ($request->getMethod() == 'POST') {
            
            if ($case->getStatusToString() === 'decided' || $case->getRemovedFromRole()) {
            
                $this->get('session')->getFlashBag()->add("errormsg", "You may no longer make a prediction for that case!");

                return $this->redirectToRoute('lexfutures_cases', ['slug' => $case->getJurisdiction()->getSlug()]);

            }
            
            
            if ($util->validatePrediction($prediction, $_POST)) {
                
                $util->updatePrediction($prediction, $_POST);
                
                $em->persist($prediction);

                $em->flush();

                // TODO: add a flashMessage

                return $this->redirectToRoute('lexfutures_cases', ['slug' => $prediction->getCourtcase()->getJurisdiction()->getSlug()]);
                
            } else {
                
                $errors[] = "Please capture a prediection for each of the justices!";
                
            }
        }
        
        return $this->render('AppBundle:cases:prediction.html.twig', [
            'prediction' => $prediction,
            'jurisdiction' => $case->getJurisdiction(),
            'errors' => $errors,
        ]);
        
    }
    
    /**
     * 
     * Show the cases for the particular jurisdiction
     * 
     * @Route("/lex/bio/{slug}", name="lexfutures_jude_bio")
     */
    public function judgesBioAction(Request $request, $slug)
    {
        
        $util = $this->get(Util::class);
        
        $em = $this->getDoctrine()->getManager();
        
        $judge = $this->getDoctrine()->getRepository('AppBundle:Judge')->findOneBySlug($slug);
        
        if (null === $judge) {
            
            $this->get('session')->getFlashBag()->add("errormsg", "We could not find that judge!");
            
            return $this->redirectToRoute('lexfutures_dashboard');
            
        }
        
        return $this->render('AppBundle:cases:judgeBio.html.twig', [
            'judge' => $judge,
            'jurisdiction' => $judge->getJurisdiction(),
        ]);
        
    }
    
    /**
     * 
     * Show the cases for the particular jurisdiction
     * 
     * @Route("/lex/case/details/{slug}", name="lexfutures_case_deatils")
     */
    public function caseDetailsAction(Request $request, $slug)
    {
        
        $util = $this->get(Util::class);
        
        $em = $this->getDoctrine()->getManager();
        
        $case = $this->getDoctrine()->getRepository('AppBundle:Courtcase')->findOneBySlug($slug);
        
        if (null === $case) {
            
            $this->get('session')->getFlashBag()->add("errormsg", "We could not find that case!");
            
            return $this->redirectToRoute('lexfutures_dashboard');
            
        }
        
        return $this->render('AppBundle:cases:details.html.twig', [
            'case' => $case,
            'jurisdiction' => $case->getJurisdiction(),
        ]);
        
    }
    
    /**
     * 
     * Show the cases for the particular jurisdiction
     * 
     * @Route("/league/{slug}", name="lexfutures_cases")
     */
    public function listAction(Request $request, $slug)
    {
                
        $jurisdiction = $this->getDoctrine()->getRepository('AppBundle:Jurisdiction')->findOneBySlug($slug);
        
        if (null === $jurisdiction) {
            
            return $this->redirectToRoute('lexfutures_dashboard');
            
        }
        
        $cases = $this->getJurisditionCases($jurisdiction);
        
        return $this->render('AppBundle:cases:list.html.twig', [
            'cases' => $cases, 'jurisdiction' => $jurisdiction
        ]);
        
    }
    
    /**
     * Return an array of cases for the given jurisdiction. Inject the users
     * prediction for each case and inject the actual result
     * 
     * TODO: add in seasons so that we can reset the ladder and competition
     * 
     * @param Jurisdiction $jurisdiction
     * @return array
     */
    public function getJurisditionCases(Jurisdiction $jurisdiction)
    {
        
        $finalcases = array();
        
        $util = $this->get(Util::class);
        
        //$helper = $this->get('lex.core.util');
        
        $cases = $this->getDoctrine()->getRepository('AppBundle:Courtcase')->findBy(["jurisdiction" => $jurisdiction->getId(), "season" => $jurisdiction->getCurrentSeason()->getId()]);
        
        foreach ($cases as $case) {
            
            if ($this->getUser()) {
                
                $finalcases[] = array("case" => $case, "prediction" => $util->getUserPrediction($this->getUser(), $case));
                
            } else {
                
                $finalcases[] = array("case" => $case, "prediction" => null);
                
            }
            
        }
        //$helper->getUserPrediction($case)
        
        // Order Cases by hearing date
        
        usort($finalcases, array($this, 'caseSort'));
        
        return $finalcases;
        
    }
    
    private static function caseSort($a, $b) {
        
        if ($a["case"]->getScheduledDate() == $b["case"]->getScheduledDate()) {
            
            return 0;
            
        }
        
        return ($a["case"]->getScheduledDate() < $b["case"]->getScheduledDate()) ? -1 : 1;
        
    }

    private static function caseSortRev($a, $b) {
        
        if ($a["case"]->getScheduledDate() == $b["case"]->getScheduledDate()) {
            
            return 0;
            
        }
        
        return ($a["case"]->getScheduledDate() > $b["case"]->getScheduledDate()) ? -1 : 1;
        
    }
}
