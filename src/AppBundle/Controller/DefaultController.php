<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use AppBundle\Service\Util;

class DefaultController extends Controller
{
    
    /**
     * @Route("/driver", name="driver")
     */
    public function driverAction(Request $request)
    {
        echo "Driver<br/>";
        
        $util = $this->get(Util::class);
        
        $em = $this->getDoctrine()->getManager();
        
        $leaderboard = $util->getLeaderBoard('overall', 10);
        
        var_dump($leaderboard);
        
        die();
        
    }
    
    /**
     * @Route("/unsubscribe/key/{key}/u/{id}", name="unsubscribe")
     * @param integer $md5
     * @param integer $id
     * @param Request $request
     */
    public function unsubscribeAction(Request $request, $key, $id)
    {
        
        $msg = "";
        $type = "err";
        
        $em = $this->getDoctrine()->getManager();
        
        $user = $this->getDoctrine()->getRepository('AppBundle:User')->findOneById($id);
        
        if (null === $user || md5($user->getEmail()) !== $key) {
            
            $msg = "Error - we could not update your preferences at this time.";
            
        }
        
        if (null !== $user && $user->getUnsub()) {
            
            $msg = "Error - you have already been unsubscribed.";
            
        }
        
        if (null !== $user && !$user->getUnsub()) {
            
            $user->setUnsub(true);
            
            $em->persist($user);
            
            $em->flush();
            
            $msg = "Success - you have been unsubscribed.";
            
            $type = 'success';
        }
        
        return $this->render('AppBundle:default:unsubscribe.html.twig', [
            //'user' => $user
            'msg' => $msg,
            'msgtype' => $type
        ]);
        
    }
    
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        
        return $this->render('AppBundle:default:home.html.twig', [
            'pagetitle' => 'home'
        ]);
        
    }
    
    /**
     * @Route("/privacy-policy", name="privacy-policy")
     */
    public function privacyAction(Request $request)
    {
        
        return $this->render('AppBundle:default:privacy.html.twig', [
            'pagetitle' => 'Privacy Policy'
        ]);
        
    }
    
    /**
     * @Route("/competition-rules", name="competition-rules")
     */
    public function rulesAction(Request $request)
    {
        
        return $this->render('AppBundle:default:rules.html.twig', [
            'pagetitle' => 'COMPETITION RULES OF THE LEXFUTURES APEX COURT PREDICTION LEAGUE'
        ]);
        
    }
    
    /**
     * @Route("/terms-and-conditions", name="terms")
     */
    public function termsAction(Request $request)
    {
        
        return $this->render('AppBundle:default:terms.html.twig', [
            'pagetitle' => 'Terms of Use'
        ]);
        
    }
    
    /**
     * @Route("/research-study-terms-and-conditions", name="research-study-terms")
     */
    public function researchTermsAction(Request $request)
    {
        
        return $this->render('AppBundle:default:researchTerms.html.twig', [
            'pagetitle' => 'terms and conditions'
        ]);
        
    }
    
    /**
     * @Route("/contact", name="contact")
     */
    public function contactAction(Request $request)
    {
        
        return $this->render('AppBundle:default:contact.html.twig', [
            
        ]);
        
    }
    
    /**
     * @Route("/fantasy-league/register", name="fantasy_league")
     */
    public function fantasyLeagueAction(Request $request)
    {
        
        return $this->render('AppBundle:default:fantasyLeague.html.twig', [
            
        ]);
        
    }
    
    /**
     * @Route("/lex/redirect", name="lexfutures_login_redirect")
     */
    public function redirectAction(Request $request)
    {
        
        if ($this->getUser()->hasRole("ROLE_ADMIN")) {
            
            return $this->redirectToRoute('lexfutures_admin_dashboard');
            
        } else if ($this->getUser()->hasRole("ROLE_FRONT_USER")) {
            
            return $this->redirectToRoute('lexfutures_dashboard');
            
        }
        
    }
    
    /**
     * @Route("/login", name="lexfutures_login_redirect_change")
     */
    public function reloginAction(Request $request)
    {

        return $this->redirectToRoute('fos_user_security_login');
                
    }
    
    /**
     * @Route("fantasy-league-home", name="lexfutures_dashboard")
     */
    public function dashboardAction(Request $request)
    {
        
        if ($this->getUser()) {
            
            $name = $this->getUser()->getFirstName()." ".$this->getUser()->getLastName();
            
        } else {
            
            $name = "";
            
        }
        
        $jurisdictions = $this->getDoctrine()->getRepository('AppBundle:Jurisdiction')->findAll();
        
        return $this->render('AppBundle:default:dashboard.html.twig', [
            'name' => $name, 'jurisdictions' => $jurisdictions
        ]);
        
    }
}
