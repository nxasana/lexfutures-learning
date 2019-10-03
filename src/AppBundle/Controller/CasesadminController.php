<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\UploadedFile;

use AppBundle\Service\Util;
use AppBundle\Form\JurisdictionType;
use AppBundle\Form\CourtcaseAdminType;

use AppBundle\Entity\Jurisdiction;
use AppBundle\Entity\File;
use AppBundle\Entity\Courtcase;
use AppBundle\Entity\Judge;
use AppBundle\Entity\User;
use AppBundle\Entity\Note;
use AppBundle\Entity\Prediction;


class CasesadminController extends Controller
{
        
    
    /**
     * @Route("/admin/cases/list", name="lexfutures_admin_cases_list")
     */
    public function casesListAction(Request $request)
    {
        
        $cases = $this->getDoctrine()->getRepository('AppBundle:Courtcase')->findAll();
                
        $util = $this->get(Util::class);
        
        $stats = $util->getDashboardStats($this->getUser());
        
        return $this->render('AppBundle:casesadmin:list.html.twig', [
            'cases' => $cases,
            'stats' => $stats
        ]);
        
    }
    
    /**
     * @Route("/admin/cases/add", name="lexfutures_admin_case_add")
     */
    public function casesAddAction(Request $request)
    {
        
        //$jurisdictions = $this->getDoctrine()->getRepository('AppBundle:Courtcase')->findAll();
        $em = $this->getDoctrine()->getManager();
        
        $util = $this->get(Util::class);
        
        $stats = $util->getDashboardStats($this->getUser());
        
        $courtcase = new Courtcase();
        
        $courtcase->setStatus(Courtcase::STATUS_PUBLISHED);
        
        $form = $this->createForm('AppBundle\Form\CourtcaseAdminType', $courtcase,[]);
        
        if ($request->getMethod() == 'POST') {
            
            $form->handleRequest($request);
            
            $jurisdiction = $courtcase->getJurisdiction();
            
            //$courtcase->setScheduledDate(new \DateTime($courtcase->getScheduledDate()));
            
            if ($form->isValid()) {
                
                foreach ($jurisdiction->getActiveJudges() as $judge) {
                    
                    if ($judge->getJusticeTitle()->getId() < 4) {
                        
                        $courtcase->addJudge($judge);
                        
                    }
                }
                
                $em->persist($courtcase);
            
                $em->flush();
                
                $this->get('session')->getFlashBag()->add("successmsg", $courtcase->getName(). " has been added!");
            
                return $this->redirectToRoute('lexfutures_admin_case_add', []);
                
            } else {
                
                $this->get('session')->getFlashBag()->add("errormsg", "Could not load Court Case!");
            
                return $this->redirectToRoute('lexfutures_admin_case_add', []);
                
            }
                        
        }
        
        return $this->render('AppBundle:casesadmin:add.html.twig', [
            'stats' => $stats,
            'form' => $form->createView()
        ]);
        
    }
    
    
}
