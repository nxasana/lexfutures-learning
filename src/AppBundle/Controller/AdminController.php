<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\UploadedFile;

use AppBundle\Service\Util;
use AppBundle\Form\rulJurisdictionType;

use AppBundle\Entity\Jurisdiction;
use AppBundle\Entity\File;
use AppBundle\Entity\Courtcase;
use AppBundle\Entity\Judge;
use AppBundle\Entity\Season;
use AppBundle\Entity\User;
use AppBundle\Entity\Note;
use AppBundle\Entity\Prediction;
use AppBundle\Entity\Notification;


class AdminController extends Controller
{
        
    /**
     * @Route("/admin/dashboard", name="lexfutures_admin_dashboard")
     */
    public function dashboardAction(Request $request)
    {
        
        $name = $this->getUser()->getFirstName()." ".$this->getUser()->getLastName();
        
        $util = $this->get(Util::class);
        
        $stats = $util->getDashboardStats($this->getUser());
        
        return $this->render('AppBundle:admin:dashboard.html.twig', [
            'name' => $name,
            'stats' => $stats,
        ]);
        
    }
    
    /**
     * @Route("/admin/user/add", name="lexfutures_admin_user_add")
     */
    /*
    public function userAddAction(Request $request)
    {
        
        echo "Add User";
        die();
        
    }
    */
    
    /**
     * @Route("/admin/user/add", name="lexfutures_admin_user_add")
     * @param Request $request
     */
    public function userAddAction(Request $request)
    {
        
        $util = $this->get(Util::class);
        
        $stats = $util->getDashboardStats($this->getUser());
        
        $em = $this->getDoctrine()->getManager();
        
        $user = new User();
        
        $form = $this->createForm('AppBundle\Form\UserAddType', $user,[]);
        
        if ($request->getMethod() == 'POST') {
            
            $form->handleRequest($request);
            
            if ($form->isValid()) {
                
                $user->setPlainPassword($user->getPassword());
                
                $user->setEnabled(true);
                
                $em->persist($user);
            
                $em->flush();
                
                $this->get('session')->getFlashBag()->add("successmsg", "User has been added!");
            
                return $this->redirectToRoute('lexfutures_admin_user_list', []);
                
            } else {
                
                $this->get('session')->getFlashBag()->add("errormsg", "Could not add User!");
            
                return $this->redirectToRoute('lexfutures_admin_user_list');
                
            }
                        
        }
        
        return $this->render('AppBundle:admin:useradd.html.twig', [
            'form' => $form->createView(),
            'stats' => $stats
        ]);
        
    }
    
    /**
     * @Route("/admin/user/list", name="lexfutures_admin_user_list")
     */
    public function userListAction(Request $request)
    {
        
        $users = $this->getDoctrine()->getRepository('AppBundle:User')->findAll();
        
        $util = $this->get(Util::class);
        
        $stats = $util->getDashboardStats($this->getUser());
        
        return $this->render('AppBundle:admin:userlist.html.twig', [
            'users' => $users,
            "stats" => $stats
        ]);
        
    }
    
    /**
     * @Route("/admin/jurisdiction/list", name="lexfutures_admin_jurisdiction_list")
     */
    public function jurisdictionListAction(Request $request)
    {
        
        $jurisdictions = $this->getDoctrine()->getRepository('AppBundle:Jurisdiction')->findAll();
                
        $util = $this->get(Util::class);
        
        $stats = $util->getDashboardStats($this->getUser());
        
        return $this->render('AppBundle:admin:jurisdictionlist.html.twig', [
            'jurisdictions' => $jurisdictions,
            'stats' => $stats
        ]);
        
    }
    
    /**
     * @Route("/admin/jurisdiction/add", name="lexfutures_admin_jurisdiction_add")
     * @param integer $id
     * @param Request $request
     */
    public function jurisdictionAddAction(Request $request)
    {
        
        $util = $this->get(Util::class);
        
        $em = $this->getDoctrine()->getManager();
        
        $jurisdiction = new Jurisdiction();
                
        $form = $this->createForm('AppBundle\Form\JurisdictionType', $jurisdiction,[]);
                
        $stats = $util->getDashboardStats($this->getUser());
        
        if ($request->getMethod() == 'POST') {
            
            $form->handleRequest($request);
            
            if ($form->isValid()) {
                
                $file = $form['file']->getData();
                
                if ($file) {
                    
                    $path = $this->container->getParameter('flag_upload_path');
                    
                    $directory = __DIR__.'/../..'.$path;
                    
                    $extension = $file->guessExtension();
                    
                    if (!$extension) {
                        // extension cannot be guessed
                        $extension = 'bin';
                    }
                    
                    $newFilename = $util->generateRandomString(20).'.'.$extension;
                    
                    $file->move($directory, $newFilename);
                    
                    $biopic = new File();
                    
                    $biopic->setType(File::TYPE_JURISDICTION_FLAG);
                    
                    $biopic->setFilename($newFilename);
                    
                    $biopic->setOriginalFilename($file->getClientOriginalName());
                    
                    $biopic->setPath($directory);
                    
                    $em->persist($biopic);
                    
                    $jurisdiction->setBiopic($biopic);
                    
                } 
                
                $em->persist($jurisdiction);
            
                $em->flush();
                
                $this->get('session')->getFlashBag()->add("successmsg", $jurisdiction->getName(). " has been updated!");
            
                return $this->redirectToRoute('lexfutures_admin_jurisdiction_list');
                
            } else {
                
                $this->get('session')->getFlashBag()->add("errormsg", "Could not load Jurisdiction!");
            
                return $this->redirectToRoute('lexfutures_admin_jurisdiction_edit', ['id' => $jurisdiction->getId()]);
                
            }
                        
        }
        
        return $this->render('AppBundle:admin:jurisdictionadd.html.twig', [
            'form' => $form->createView(),
            'stats' => $stats
        ]);
        
    }
    
    /**
     * @Route("/admin/jurisdiction/edit/{id}", name="lexfutures_admin_jurisdiction_edit")
     * @param integer $id
     * @param Request $request
     */
    public function jurisdictionAction(Request $request, $id)
    {
        
        $util = $this->get(Util::class);
        
        $stats = $util->getDashboardStats($this->getUser());
        
        $em = $this->getDoctrine()->getManager();
        
        $jurisdiction = $this->getDoctrine()->getRepository('AppBundle:Jurisdiction')->findOneById($id);
        
        if (null === $jurisdiction) {
            
            $this->get('session')->getFlashBag()->add("errormsg", "Could not load Jurisdiction!");
            
            return $this->redirectToRoute('lexfutures_admin_jurisdiction_list');
            
        }
        
        $form = $this->createForm('AppBundle\Form\JurisdictionType', $jurisdiction,['seasons' => $jurisdiction->getSeasons()]);
        
        if ($request->getMethod() == 'POST') {
            
            $form->handleRequest($request);
            
            if ($form->isValid()) {
                
                $file = $form['file']->getData();
                
                if ($file) {
                    
                    $path = $this->container->getParameter('flag_upload_path');
                    
                    $directory = __DIR__.'/../..'.$path;
                    
                    $extension = $file->guessExtension();
                    
                    if (!$extension) {
                        // extension cannot be guessed
                        $extension = 'bin';
                    }
                    
                    $newFilename = $util->generateRandomString(20).'.'.$extension;
                    
                    $file->move($directory, $newFilename);
                    
                    $biopic = new File();
                    
                    $biopic->setType(File::TYPE_JURISDICTION_FLAG);
                    
                    $biopic->setFilename($newFilename);
                    
                    $biopic->setOriginalFilename($file->getClientOriginalName());
                    
                    $biopic->setPath($directory);
                    
                    $em->persist($biopic);
                    
                    $jurisdiction->setBiopic($biopic);
                    
                } 
                
                $em->persist($jurisdiction);
            
                $em->flush();
                
                $this->get('session')->getFlashBag()->add("successmsg", $jurisdiction->getName(). " has been updated!");
            
                return $this->redirectToRoute('lexfutures_admin_jurisdiction_list');
                
            } else {
                
                $this->get('session')->getFlashBag()->add("errormsg", "Could not load Jurisdiction!");
            
                return $this->redirectToRoute('lexfutures_admin_jurisdiction_edit', ['id' => $jurisdiction->getId()]);
                
            }
                        
        }
        
        return $this->render('AppBundle:admin:jurisdictionedit.html.twig', [
            'form' => $form->createView(), 'jurisdiction' => $jurisdiction,
            'stats' => $stats
        ]);
        
    }
    
    /**
     * @Route("/admin/jurisdiction/cases/{id}", name="lexfutures_admin_jurisdiction_cases")
     * @param integer $id
     * @param Request $request
     */
    public function jurisdictionCasesAction(Request $request, $id)
    {
        
        $util = $this->get(Util::class);
        
        $stats = $util->getDashboardStats($this->getUser());
        
        $em = $this->getDoctrine()->getManager();
        
        $jurisdiction = $this->getDoctrine()->getRepository('AppBundle:Jurisdiction')->findOneById($id);
        
        if (null === $jurisdiction) {
            
            $this->get('session')->getFlashBag()->add("errormsg", "Could not load Jurisdiction!");
            
            return $this->redirectToRoute('lexfutures_admin_jurisdiction_list');
            
        }
        
        return $this->render('AppBundle:admin:jurisdictioncases.html.twig', [
            'cases' => $jurisdiction->getCourtcases(),
            'jurisdiction' => $jurisdiction,
            'stats' => $stats
        ]);
        
    }
    
    /**
     * @Route("/admin/jurisdiction/seasons/{slug}", name="lexfutures_admin_jurisdiction_seasons")
     * @param String $slug
     * @param Request $request
     */
    public function jurisdictionSeasonsAction(Request $request, $slug)
    {
        
        $util = $this->get(Util::class);
        
        $stats = $util->getDashboardStats($this->getUser());
        
        $em = $this->getDoctrine()->getManager();
        
        $jurisdiction = $this->getDoctrine()->getRepository('AppBundle:Jurisdiction')->findOneBySlug($slug);
        
        if (null === $jurisdiction) {
            
            $this->get('session')->getFlashBag()->add("errormsg", "Could not load Jurisdiction!");
            
            return $this->redirectToRoute('lexfutures_admin_jurisdiction_list');
            
        }
        
        return $this->render('AppBundle:admin:jurisdictionSeasons.html.twig', [
            'jurisdiction' => $jurisdiction,
            'stats' => $stats
        ]);
        
    }
    
    /**
     * @Route("/admin/jurisdiction/season/{slug}/add", name="lexfutures_admin_jurisdiction_season_add")
     * @param string $slug
     * @param Request $request
     */
    public function jurisdictionSeasonAddAction(Request $request, $slug)
    {
        
        $util = $this->get(Util::class);
        
        $stats = $util->getDashboardStats($this->getUser());
        
        $em = $this->getDoctrine()->getManager();
        
        $jurisdiction = $this->getDoctrine()->getRepository('AppBundle:Jurisdiction')->findOneBySlug($slug);
        
        if (null === $jurisdiction) {
            
            $this->get('session')->getFlashBag()->add("errormsg", "Could not load Jurisdiction!");
            
            return $this->redirectToRoute('lexfutures_admin_jurisdiction_list');
            
        }
        
        $season = new Season();
        
        $season->setJurisdiction($jurisdiction);
        
        $form = $this->createForm('AppBundle\Form\SeasonType', $season,[]);
        
        if ($request->getMethod() == 'POST') {
            
            $form->handleRequest($request);
                        
            if ($form->isValid()) {
                
                $em->persist($season);
            
                $em->flush();
                
                $this->get('session')->getFlashBag()->add("successmsg", $season->getName(). " has been added!");
            
                return $this->redirectToRoute('lexfutures_admin_jurisdiction_seasons', ['slug' => $season->getJurisdiction()->getSlug()]);
                
            } else {
                
                $this->get('session')->getFlashBag()->add("errormsg", "Could not load Season!");
            
                return $this->redirectToRoute('lexfutures_admin_jurisdiction_seasons', ['slug' => $judge->getJurisdiction()->getSlug()]);
                
            }
                        
        }
        
        return $this->render('AppBundle:admin:jurisdictionSeasonAdd.html.twig', [
            'jurisdiction' => $jurisdiction,
            'form' => $form->createView(),
            'stats' => $stats
        ]);
        
    }
    
    /**
     * @Route("/admin/jurisdiction/season/{slug}/edit/{seasonid}", name="lexfutures_admin_jurisdiction_season_edit")
     * @param string $slug
     * @param integer $seasonid
     * @param Request $request
     */
    public function jurisdictionSeasonEditAction(Request $request, $slug, $seasonid)
    {
        
        $util = $this->get(Util::class);
        
        $stats = $util->getDashboardStats($this->getUser());
        
        $em = $this->getDoctrine()->getManager();
        
        $jurisdiction = $this->getDoctrine()->getRepository('AppBundle:Jurisdiction')->findOneBySlug($slug);
        
        if (null === $jurisdiction) {
            
            $this->get('session')->getFlashBag()->add("errormsg", "Could not load Jurisdiction!");
            
            return $this->redirectToRoute('lexfutures_admin_jurisdiction_list');
            
        }
        
        $season = $this->getDoctrine()->getRepository('AppBundle:Season')->findOneById($seasonid);
                
        $form = $this->createForm('AppBundle\Form\SeasonType', $season,[]);
        
        if ($request->getMethod() == 'POST') {
            
            $form->handleRequest($request);
                        
            if ($form->isValid()) {
                
                $em->persist($season);
            
                $em->flush();
                
                $this->get('session')->getFlashBag()->add("successmsg", $season->getName(). " has been updated!");
            
                return $this->redirectToRoute('lexfutures_admin_jurisdiction_seasons', ['slug' => $season->getJurisdiction()->getSlug()]);
                
            } else {
                
                $this->get('session')->getFlashBag()->add("errormsg", "Could not load Season!");
            
                return $this->redirectToRoute('lexfutures_admin_jurisdiction_seasons', ['slug' => $season->getJurisdiction()->getSlug()]);
                
            }
                        
        }
        
        return $this->render('AppBundle:admin:jurisdictionSeasonEdit.html.twig', [
            'jurisdiction' => $jurisdiction, 'season' => $season,
            'form' => $form->createView(),
            'stats' => $stats
        ]);
        
    }
    
    /**
     * @Route("/admin/jurisdiction/judges/{id}", name="lexfutures_admin_jurisdiction_judges")
     * @param integer $id
     * @param Request $request
     */
    public function jurisdictionJudgesAction(Request $request, $id)
    {
        
        $util = $this->get(Util::class);
        
        $stats = $util->getDashboardStats($this->getUser());
        
        $em = $this->getDoctrine()->getManager();
        
        $jurisdiction = $this->getDoctrine()->getRepository('AppBundle:Jurisdiction')->findOneById($id);
        
        if (null === $jurisdiction) {
            
            $this->get('session')->getFlashBag()->add("errormsg", "Could not load Jurisdiction!");
            
            return $this->redirectToRoute('lexfutures_admin_jurisdiction_list');
            
        }
        
        return $this->render('AppBundle:admin:jurisdictionJudges.html.twig', [
            'judges' => $jurisdiction->getJudges(),
            'jurisdiction' => $jurisdiction,
            'stats' => $stats
        ]);
        
    }
    
    /**
     * @Route("/admin/jurisdiction/judge/{id}/add", name="lexfutures_admin_jurisdiction_judge_add")
     * @param integer $id
     * @param Request $request
     */
    public function jurisdictionJudgeAddAction(Request $request, $id)
    {
        
        $util = $this->get(Util::class);
        
        $stats = $util->getDashboardStats($this->getUser());
        
        $em = $this->getDoctrine()->getManager();
        
        $jurisdiction = $this->getDoctrine()->getRepository('AppBundle:Jurisdiction')->findOneById($id);
        
        if (null === $jurisdiction) {
            
            $this->get('session')->getFlashBag()->add("errormsg", "Could not load Jurisdiction!");
            
            return $this->redirectToRoute('lexfutures_admin_jurisdiction_list');
            
        }
        
        $judge = new Judge();
        
        $judge->setJurisdiction($jurisdiction);
        
        $form = $this->createForm('AppBundle\Form\JudgeType', $judge,[]);
        
        if ($request->getMethod() == 'POST') {
            
            $form->handleRequest($request);
                        
            if ($form->isValid()) {
                
                $em->persist($judge);
            
                $em->flush();
                
                $this->get('session')->getFlashBag()->add("successmsg", $judge->getName(). " has been added!");
            
                return $this->redirectToRoute('lexfutures_admin_jurisdiction_judges', ['id' => $judge->getJurisdiction()->getId()]);
                
            } else {
                
                $this->get('session')->getFlashBag()->add("errormsg", "Could not load Judge!");
            
                return $this->redirectToRoute('lexfutures_admin_jurisdiction_judges', ['id' => $judge->getJurisdiction()->getId()]);
                
            }
                        
        }
        
        return $this->render('AppBundle:admin:jurisdictionJudgeAdd.html.twig', [
            'jurisdiction' => $jurisdiction,
            'form' => $form->createView(),
            'stats' => $stats
        ]);
        
    }
    
    /**
     * @Route("/admin/jurisdiction/judge/{id}/edit/{judgeid}", name="lexfutures_admin_jurisdiction_judge_edit")
     * @param integer $id
     * @param integer $judgeid
     * @param Request $request
     */
    public function jurisdictionJudgeEditAction(Request $request, $id, $judgeid)
    {
        
        $util = $this->get(Util::class);
        
        $stats = $util->getDashboardStats($this->getUser());
        
        $em = $this->getDoctrine()->getManager();
        
        $jurisdiction = $this->getDoctrine()->getRepository('AppBundle:Jurisdiction')->findOneById($id);
        
        if (null === $jurisdiction) {
            
            $this->get('session')->getFlashBag()->add("errormsg", "Could not load Jurisdiction!");
            
            return $this->redirectToRoute('lexfutures_admin_jurisdiction_list');
            
        }
        
        $judge = $this->getDoctrine()->getRepository('AppBundle:Judge')->findOneById($judgeid);
                
        $form = $this->createForm('AppBundle\Form\JudgeType', $judge,[]);
        
        if ($request->getMethod() == 'POST') {
            
            $form->handleRequest($request);
                        
            if ($form->isValid()) {
                
                $file = $form['file']->getData();
                
                $file2 = $form['file2']->getData();
                
                if ($file) {
                    
                    $path = $this->container->getParameter('profile_upload_path');
                    
                    $directory = __DIR__.'/../..'.$path;
                    
                    $extension = $file->guessExtension();
                    
                    if (!$extension) {
                        // extension cannot be guessed
                        $extension = 'bin';
                    }
                    
                    $newFilename = $util->generateRandomString(20).'.'.$extension;
                    
                    $file->move($directory, $newFilename);
                    
                    $biopic = new File();
                    
                    $biopic->setType(File::TYPE_BIO_PIC);
                    
                    $biopic->setFilename($newFilename);
                    
                    $biopic->setOriginalFilename($file->getClientOriginalName());
                    
                    $biopic->setPath($directory);
                    
                    $em->persist($biopic);
                    
                    $judge->setBiopic($biopic);
                    
                } 
                
                if ($file2) {
                    
                    $path = $this->container->getParameter('profile_upload_path');
                    
                    $directory = __DIR__.'/../..'.$path;
                    
                    $extension = $file2->guessExtension();
                    
                    if (!$extension) {
                        // extension cannot be guessed
                        $extension = 'bin';
                    }
                    
                    $newFilename = $util->generateRandomString(20).'.'.$extension;
                    
                    $file2->move($directory, $newFilename);
                    
                    $biopic = new File();
                    
                    $biopic->setType(File::TYPE_BIO_PDF);
                    
                    $biopic->setFilename($newFilename);
                    
                    $biopic->setOriginalFilename($file2->getClientOriginalName());
                    
                    $biopic->setPath($directory);
                    
                    $em->persist($biopic);
                    
                    $judge->setBiopic($biopic);
                    
                } 
                
                $em->persist($judge);
            
                $em->flush();
                
                $this->get('session')->getFlashBag()->add("successmsg", $judge->getName(). " has been updated!");
            
                return $this->redirectToRoute('lexfutures_admin_jurisdiction_judges', ['id' => $judge->getJurisdiction()->getId()]);
                
            } else {
                
                $this->get('session')->getFlashBag()->add("errormsg", "Could not load Judge!");
            
                return $this->redirectToRoute('lexfutures_admin_jurisdiction_judges', ['id' => $judge->getJurisdiction()->getId()]);
                
            }
                        
        }
        
        return $this->render('AppBundle:admin:jurisdictionJudgeEdit.html.twig', [
            'jurisdiction' => $jurisdiction, 'judge' => $judge,
            'form' => $form->createView(),
            'stats' => $stats
        ]);
        
    }
    
    /**
     * @Route("/admin/jurisdiction/judge/{id}/retire/{slug}", name="lexfutures_admin_jurisdiction_judge_retire")
     * @param integer $id
     * @param string $slug
     * @param Request $request
     */
    public function jurisdictionJudgeRetireAction(Request $request, $id, $slug)
    {
        
        $util = $this->get(Util::class);
        
        $em = $this->getDoctrine()->getManager();
        
        $jurisdiction = $this->getDoctrine()->getRepository('AppBundle:Jurisdiction')->findOneById($id);
        
        if (null === $jurisdiction) {
            
            $this->get('session')->getFlashBag()->add("errormsg", "Could not load Jurisdiction!");
            
            return $this->redirectToRoute('lexfutures_admin_jurisdiction_list');
            
        }
        
        $judge = $this->getDoctrine()->getRepository('AppBundle:Judge')->findOneBySlug($slug);
        
        if ($judge->getIsRetired()) {
            
            $judge->setIsRetired(false);
            
        } else {
            
            $judge->setIsRetired(true);
            
        }
        
        $em->persist($judge);
        
        $em->flush();
        
        $this->get('session')->getFlashBag()->add("successmsg", "Judge has been retired!");
            
        return $this->redirectToRoute('lexfutures_admin_jurisdiction_judges', ['id' => $jurisdiction->getId()]);
        
        
    }
    
    
    
    /**
     * @Route("/admin/jurisdiction/case/{slug}/predictions", name="lexfutures_admin_jurisdiction_case_predictions")
     * @param string $slug
     * @param Request $request
     */
    public function jurisdictionCasePredictionsAction(Request $request, $slug)
    {
        
        $util = $this->get(Util::class);
        
        $stats = $util->getDashboardStats($this->getUser());
        
        $em = $this->getDoctrine()->getManager();
        
        $courtcase = $this->getDoctrine()->getRepository('AppBundle:Courtcase')->findOneBySlug($slug);
        
        
        return $this->render('AppBundle:admin:jurisdictionCasePredictions.html.twig', [
            'jurisdiction' => $courtcase->getJurisdiction(),
            'courtcase' => $courtcase,
            'predictions' => $courtcase->getUserPredictions(),
            'stats' => $stats
        ]);
        
    }
    
    /**
     * @Route("/admin/user/{id}/predictions", name="lexfutures_admin_user_predictions")
     * @param integer $id
     * @param Request $request
     */
    public function userPredictionsAction(Request $request, $id)
    {
        
        $util = $this->get(Util::class);
        
        $stats = $util->getDashboardStats($this->getUser());
        
        $em = $this->getDoctrine()->getManager();
        
        $user = $this->getDoctrine()->getRepository('AppBundle:User')->findOneById($id);
        
        return $this->render('AppBundle:admin:userPredictions.html.twig', [
            'user' => $user,
            'predictions' => $user->getPredictions(),
            'stats' => $stats
        ]);
        
    }
    
    
    /**
     * @Route("/admin/jurisdiction/prediction/{id}/view", name="lexfutures_admin_jurisdiction_prediction_view")
     * @param integer $id
     * @param Request $request
     */
    public function jurisdictionCasePredictionViewAction(Request $request, $id) {
        
        $util = $this->get(Util::class);
        
        $stats = $util->getDashboardStats($this->getUser());
        
        $em = $this->getDoctrine()->getManager();
        
        $prediction = $this->getDoctrine()->getRepository('AppBundle:Prediction')->findOneById($id);
        
        return $this->render('AppBundle:admin:jurisdictionCasePredictionView.html.twig', [
            'jurisdiction' => $prediction->getCourtcase()->getJurisdiction(),
            'courtcase' => $prediction->getCourtcase(),
            'prediction' => $prediction,
            'stats' => $stats
        ]);
        
    }
    
    /**
     * @Route("/admin/user/{id}/edit", name="lexfutures_admin_user_edit")
     * @param integer $id
     * @param Request $request
     */
    public function userEditAction(Request $request, $id)
    {
        
        $util = $this->get(Util::class);
        
        $stats = $util->getDashboardStats($this->getUser());
        
        $em = $this->getDoctrine()->getManager();
        
        $user = $this->getDoctrine()->getRepository('AppBundle:User')->findOneById($id);
        
        $jurisdiction = $this->getDoctrine()->getRepository('AppBundle:Jurisdiction')->findOneById(3); // hardcoding to South African Jurisdiction
        
        if (null === $user) {
            
            $this->get('session')->getFlashBag()->add("errormsg", "Could not load User!");
            
            return $this->redirectToRoute('lexfutures_admin_user_list');
            
        }
        
        if (null === $jurisdiction) {
            
            return $this->redirectToRoute('lexfutures_dashboard');
            
        }
        
        $leaderboard = array();
        
        $leaderboard['overall'] = $util->getLeaderBoard('overall', 10, $jurisdiction);
        $leaderboard['lawProfessionals'] = $util->getLeaderBoard('lawProfessionals', 10, $jurisdiction);
        $leaderboard['students'] = $util->getLeaderBoard('students', 10, $jurisdiction);
        $leaderboard['academics'] = $util->getLeaderBoard('academics', 10, $jurisdiction);
        $leaderboard['other'] = $util->getLeaderBoard('other', 10, $jurisdiction);
        $leaderboard['open'] = $util->getLeaderBoard('open', 10, $jurisdiction);
        
        $form = $this->createForm('AppBundle\Form\UserType', $user,[]);
        
        if ($request->getMethod() == 'POST') {
            
            $form->handleRequest($request);
            
            if ($form->isValid()) {
                                
                $em->persist($user);
            
                $em->flush();
                
                $this->get('session')->getFlashBag()->add("successmsg", "User has been updated!");
            
                return $this->redirectToRoute('lexfutures_admin_user_list', []);
                
            } /*else {
                
                $this->get('session')->getFlashBag()->add("errormsg", "ERROR!");
            
                return $this->redirectToRoute('lexfutures_admin_user_list');
                
            }*/
                        
        }
        
        if ($user) {
            
            $leaderstats = $util->leaderboardStats($user, $jurisdiction);
            
            $pointsTable = $util->pointsTable($user, $jurisdiction);
            
            $newPointsTable = $util->newPointsTable($user, $jurisdiction, $leaderboard);
            
        }
        
        return $this->render('AppBundle:admin:useredit.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
            'stats' => $stats,
            'leaderboard' => $leaderboard,
            'leaderstats' => $leaderstats,
            'pointstable' => $pointsTable,
            'newPointsTable' => $newPointsTable,
        ]);
        
    }
    
    
    
    /**
     * @Route("/admin/user/togglestatus/{id}", name="lexfutures_admin_user_toggle_status")
     * @param integer $id
     * @param Request $request
     */
    public function userToggleStatusAction(Request $request, $id)
    {
       
        $util = $this->get(Util::class);
        
        $em = $this->getDoctrine()->getManager();
        
        $user = $this->getDoctrine()->getRepository('AppBundle:User')->findOneById($id);
        
        if (null === $user) {
            
            $this->get('session')->getFlashBag()->add("errormsg", "Could not load User!");
            
            return $this->redirectToRoute('lexfutures_admin_user_list');
            
        }
        
        if ($user->isEnabled()) {
            
            $user->setEnabled(false);
            
            $em->persist($user);
            
            $em->flush();

            $this->get('session')->getFlashBag()->add("successmsg", "User account has been Deactivated!");

            return $this->redirectToRoute('lexfutures_admin_user_edit', ['id' => $user->getId()]);
            
        } else {
            
            $user->setEnabled(true);
            
            $em->persist($user);
            
            $em->flush();

            $this->get('session')->getFlashBag()->add("successmsg", "User account has been Activated!");

            return $this->redirectToRoute('lexfutures_admin_user_edit', ['id' => $user->getId()]);
            
        }
        
        
    }
    /**
     * @Route("/admin/jurisdiction/case/{id}/note/edit", name="lexfutures_admin_jurisdiction_case_note_edit")
     * @param integer $id
     * @param Request $request
     */
    public function jurisdictionCaseNoteEditAction(Request $request, $id)
    {
        
        $util = $this->get(Util::class);
        
        $stats = $util->getDashboardStats($this->getUser());
        
        $em = $this->getDoctrine()->getManager();
        
        $note = $this->getDoctrine()->getRepository('AppBundle:Note')->findOneById($id);
        
        if (null === $note) {
            
            $this->get('session')->getFlashBag()->add("errormsg", "Could not load Courtcase Note!");
            
            return $this->redirectToRoute('lexfutures_admin_jurisdiction_list');
            
        }
        
        $form = $this->createForm('AppBundle\Form\CourtcaseNoteType', $note,[]);
        
        if ($request->getMethod() == 'POST') {
            
            $form->handleRequest($request);
            
            if ($form->isValid()) {
                                
                $em->persist($note);
            
                $em->flush();
                
                $this->get('session')->getFlashBag()->add("successmsg", "Note has been updated!");
            
                return $this->redirectToRoute('lexfutures_admin_jurisdiction_case_notes', ['slug' => $note->getCourtcase()->getSlug()]);
                
            } else {
                
                $this->get('session')->getFlashBag()->add("errormsg", "Could not load Court Case!");
            
                return $this->redirectToRoute('lexfutures_admin_dashboard');
                
            }
                        
        }
        
        return $this->render('AppBundle:admin:jurisdictionCaseNoteEdit.html.twig', [
            'jurisdiction' => $note->getCourtcase()->getJurisdiction(),
            'courtcase' => $note->getCourtcase(),
            'form' => $form->createView(),
            'stats' => $stats
        ]);
        
    }
    
    /**
     * @Route("/admin/jurisdiction/case/{id}/note/delete", name="lexfutures_admin_jurisdiction_case_note_delete")
     * @param integer $id
     * @param Request $request
     */
    public function jurisdictionCaseNoteDeleteAction(Request $request, $id)
    {
        
        $util = $this->get(Util::class);
        
        $em = $this->getDoctrine()->getManager();
        
        $note = $this->getDoctrine()->getRepository('AppBundle:Note')->findOneById($id);
        
        if (null === $note) {
            
            $this->get('session')->getFlashBag()->add("errormsg", "Could not load Courtcase Note!");
            
            return $this->redirectToRoute('lexfutures_admin_jurisdiction_list');
            
        }
        
        $case = $note->getCourtcase();
        
        $em->remove($note);
        
        $em->flush();
        
        $this->get('session')->getFlashBag()->add("successmsg", "Note has been added!");
            
        return $this->redirectToRoute('lexfutures_admin_jurisdiction_case_notes', ['slug' => $case->getSlug()]);
        
    }
    
    /**
     * @Route("/admin/jurisdiction/case/{slug}/notes", name="lexfutures_admin_jurisdiction_case_notes")
     * @param string $slug
     * @param Request $request
     */
    public function jurisdictionCaseNotesAction(Request $request, $slug)
    {
        
        $util = $this->get(Util::class);
        
        $stats = $util->getDashboardStats($this->getUser());
        
        $em = $this->getDoctrine()->getManager();
        
        $case = $this->getDoctrine()->getRepository('AppBundle:Courtcase')->findOneBySlug($slug);
        
        if (null === $case) {
            
            $this->get('session')->getFlashBag()->add("errormsg", "Could not load Courtcase!");
            
            return $this->redirectToRoute('lexfutures_admin_jurisdiction_list');
            
        }
        
        $note = new Note();
        
        $note->setCourtcase($case);
        
        $note->setCreator($this->getUser());
        
        $form = $this->createForm('AppBundle\Form\CourtcaseNoteType', $note,[]);
        
        if ($request->getMethod() == 'POST') {
            
            $form->handleRequest($request);
            
            if ($form->isValid()) {
                                
                $em->persist($note);
            
                $em->flush();
                
                $this->get('session')->getFlashBag()->add("successmsg", "Note has been added!");
            
                return $this->redirectToRoute('lexfutures_admin_jurisdiction_case_notes', ['slug' => $case->getSlug()]);
                
            } else {
                
                $this->get('session')->getFlashBag()->add("errormsg", "Could not load Court Case!");
            
                return $this->redirectToRoute('lexfutures_admin_dashboard');
                
            }
                        
        }
        
        return $this->render('AppBundle:admin:jurisdictionCaseNotes.html.twig', [
            'jurisdiction' => $case->getJurisdiction(),
            'courtcase' => $case,
            'form' => $form->createView(),
            'stats' => $stats
        ]);
        
    }
    
    /**
     * @Route("/admin/jurisdiction/case/{slug}/delete", name="lexfutures_admin_jurisdiction_case_delete")
     * @param string $slug
     * @param Request $request
     */
    public function jurisdictionCaseDeleteAction(Request $request, $slug)
    {
        
        $util = $this->get(Util::class);
        
        $stats = $util->getDashboardStats($this->getUser());
        
        $em = $this->getDoctrine()->getManager();
        
        $case = $this->getDoctrine()->getRepository('AppBundle:Courtcase')->findOneBySlug($slug);
        
        if (null === $case) {
            
            $this->get('session')->getFlashBag()->add("errormsg", "Could not load Courtcase!");
            
            return $this->redirectToRoute('lexfutures_admin_jurisdiction_list');
            
        }
          
        /*
        $form = $this->createForm('AppBundle\Form\CourtcaseNoteType', $note,[]);
        
        if ($request->getMethod() == 'POST') {
            
            $form->handleRequest($request);
            
            if ($form->isValid()) {
                                
                $em->persist($note);
            
                $em->flush();
                
                $this->get('session')->getFlashBag()->add("successmsg", "Note has been added!");
            
                return $this->redirectToRoute('lexfutures_admin_jurisdiction_case_notes', ['slug' => $case->getSlug()]);
                
            } else {
                
                $this->get('session')->getFlashBag()->add("errormsg", "Could not load Court Case!");
            
                return $this->redirectToRoute('lexfutures_admin_dashboard');
                
            }
                        
        }
        */
        return $this->render('AppBundle:admin:jurisdictionCaseDelete.html.twig', [
            'jurisdiction' => $case->getJurisdiction(),
            'courtcase' => $case,
            'stats' => $stats
        ]);
        
    }
    
    /**
     * @Route("/admin/jurisdiction/case/{id}/add", name="lexfutures_admin_jurisdiction_case_add")
     * @param integer $id
     * @param Request $request
     */
    public function jurisdictionCaseAddAction(Request $request, $id)
    {
        
        $util = $this->get(Util::class);
        
        $stats = $util->getDashboardStats($this->getUser());
        
        $em = $this->getDoctrine()->getManager();
        
        $jurisdiction = $this->getDoctrine()->getRepository('AppBundle:Jurisdiction')->findOneById($id);
        
        if (null === $jurisdiction) {
            
            $this->get('session')->getFlashBag()->add("errormsg", "Could not load Jurisdiction!");
            
            return $this->redirectToRoute('lexfutures_admin_jurisdiction_list');
            
        }
        
        $courtcase = new Courtcase();
        
        $courtcase->setJurisdiction($jurisdiction);
        
        $courtcase->setStatus(Courtcase::STATUS_PUBLISHED);
        
        $courtcase->setSeason($jurisdiction->getCurrentSeason());
        
        $form = $this->createForm('AppBundle\Form\CourtcaseType', $courtcase,[]);
        
        if ($request->getMethod() == 'POST') {
            
            $form->handleRequest($request);
            
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
            
                return $this->redirectToRoute('lexfutures_admin_jurisdiction_cases', ['id' => $courtcase->getJurisdiction()->getId()]);
                
            } else {
                
                $this->get('session')->getFlashBag()->add("errormsg", "Could not load Court Case!");
            
                return $this->redirectToRoute('lexfutures_admin_jurisdiction_cases', ['id' => $courtcase->getJurisdiction()->getId()]);
                
            }
                        
        }
        
        return $this->render('AppBundle:admin:jurisdictionCaseAdd.html.twig', [
            'jurisdiction' => $jurisdiction,
            'form' => $form->createView(),
            'stats' => $stats
        ]);
        
    }
    
    /**
     * @Route("/admin/jurisdiction/case/{id}/edit/{caseid}/action/{action}", name="lexfutures_admin_jurisdiction_case_pause_toggle")
     * @param integer $id
     * @param Request $request
     */
    public function jurisdictionCasePauseToggleAction(Request $request, $id, $caseid, $action)
    {
        
        $util = $this->get(Util::class);
        
        $stats = $util->getDashboardStats($this->getUser());
        
        $em = $this->getDoctrine()->getManager();
        
        $jurisdiction = $this->getDoctrine()->getRepository('AppBundle:Jurisdiction')->findOneById($id);
        
        if (null === $jurisdiction) {
            
            $this->get('session')->getFlashBag()->add("errormsg", "Could not load Jurisdiction!");
            
            return $this->redirectToRoute('lexfutures_admin_jurisdiction_list');
            
        }
        
        $courtcase = $this->getDoctrine()->getRepository('AppBundle:Courtcase')->findOneById($caseid);
        
        if (null === $courtcase) {
            
            $this->get('session')->getFlashBag()->add("errormsg", "Could not load Court Case!");
            
            return $this->redirectToRoute('lexfutures_admin_jurisdiction_cases', ['id' => $jurisdiction->getId()]);
            
        }
        
        if ($action === "pause") {
            
            $courtcase->setIsPaused(true);
            
            $this->get('session')->getFlashBag()->add("successmsg", "Predictions for this case have been paused!");
            
        } else {
            
            $courtcase->setIsPaused(false);
            
            $this->get('session')->getFlashBag()->add("successmsg", "Predictions for this case have been unpaused!");
            
        }
        
        $em->persist($courtcase);
        
        $em->flush();
        
        return $this->redirectToRoute('lexfutures_admin_jurisdiction_cases', ['id' => $jurisdiction->getId()]);
        
    }
    
    /**
     * @Route("/admin/jurisdiction/case/{id}/edit/{caseid}", name="lexfutures_admin_jurisdiction_case_edit")
     * @param integer $id
     * @param Request $request
     */
    public function jurisdictionCaseEditAction(Request $request, $id, $caseid)
    {
        
        $util = $this->get(Util::class);
        
        $stats = $util->getDashboardStats($this->getUser());
        
        $em = $this->getDoctrine()->getManager();
        
        $jurisdiction = $this->getDoctrine()->getRepository('AppBundle:Jurisdiction')->findOneById($id);
        
        if (null === $jurisdiction) {
            
            $this->get('session')->getFlashBag()->add("errormsg", "Could not load Jurisdiction!");
            
            return $this->redirectToRoute('lexfutures_admin_jurisdiction_list');
            
        }
        
        $courtcase = $this->getDoctrine()->getRepository('AppBundle:Courtcase')->findOneById($caseid);
        
        $courtcase->setStatus(Courtcase::STATUS_PUBLISHED);
        
        $form = $this->createForm('AppBundle\Form\CourtcaseType', $courtcase,[]);
        
        if ($request->getMethod() == 'POST') {
            
            $form->handleRequest($request);
            
            //$courtcase->setScheduledDate(new \DateTime($courtcase->getScheduledDate()));
            
            if ($form->isValid()) {
                
                $em->persist($courtcase);
            
                $em->flush();
                
                $this->get('session')->getFlashBag()->add("successmsg", $courtcase->getName(). " has been updated!");
            
                return $this->redirectToRoute('lexfutures_admin_jurisdiction_cases', ['id' => $courtcase->getJurisdiction()->getId()]);
                
            } else {
                
                $this->get('session')->getFlashBag()->add("errormsg", "Could not load Court Case!");
            
                return $this->redirectToRoute('lexfutures_admin_jurisdiction_cases', ['id' => $courtcase->getJurisdiction()->getId()]);
                
            }
                        
        }
        
        return $this->render('AppBundle:admin:jurisdictionCaseEdit.html.twig', [
            'jurisdiction' => $jurisdiction,
            'courtcase' => $courtcase,
            'form' => $form->createView(),
            'stats' => $stats
        ]);
        
    }
    
    /**
     * @Route("/admin/jurisdiction/case/{slug}/judges", name="lexfutures_admin_jurisdiction_case_judges")
     * @param string $slug
     * @param Request $request
     */
    public function jurisdictionCaseJudgesAction(Request $request, $slug)
    {
        
        $util = $this->get(Util::class);
        
        $stats = $util->getDashboardStats($this->getUser());
        
        $em = $this->getDoctrine()->getManager();
        
        $courtcase = $this->getDoctrine()->getRepository('AppBundle:Courtcase')->findOneBySlug($slug);
        
        if (null === $courtcase) {
            
            $this->get('session')->getFlashBag()->add("errormsg", "Could not load Courtcase!");
            
            return $this->redirectToRoute('lexfutures_admin_jurisdiction_list');
            
        }
        
        return $this->render('AppBundle:admin:jurisdictionCaseJudges.html.twig', [
            'jurisdiction' => $courtcase->getJurisdiction(),
            'courtcase' => $courtcase,
            'stats' => $stats
        ]);
        
    }
    
    /**
     * @Route("/admin/jurisdiction/case/{slug}/judge/add", name="lexfutures_admin_jurisdiction_case_judge_add")
     * @param string $slug
     * @param Request $request
     */
    public function jurisdictionCaseJudgeAddAction(Request $request, $slug)
    {
        
        $util = $this->get(Util::class);
        
        $stats = $util->getDashboardStats($this->getUser());
        
        $em = $this->getDoctrine()->getManager();
        
        $courtcase = $this->getDoctrine()->getRepository('AppBundle:Courtcase')->findOneBySlug($slug);
        
        $action = "recuse";
        
        if (null === $courtcase) {
            
            $this->get('session')->getFlashBag()->add("errormsg", "Could not load Courtcase!");
            
            return $this->redirectToRoute('lexfutures_admin_jurisdiction_list');
            
        }
        
        if ($request->getMethod() == 'POST') {
            
            $counter = 0;
            
            foreach ($_POST["selectedJudges"] as $judgeid) {
                
                $addJudge = $this->getDoctrine()->getRepository('AppBundle:Judge')->findOneById($judgeid); 
                
                $courtcase->addJudge($addJudge);
                
                $counter += 1;
                
            }
            
            $em->persist($courtcase);
            
            $em->flush();
            
            $this->get('session')->getFlashBag()->add("successmsg", $counter." Judges added to Courtcase!");
            
            return $this->redirectToRoute('lexfutures_admin_jurisdiction_case_judges', ['slug' => $courtcase->getSlug()]);
            
        }
        
        
        return $this->render('AppBundle:admin:jurisdictionCaseJudgesAdd.html.twig', [
            'jurisdiction' => $courtcase->getJurisdiction(),
            'judges' => $courtcase->getJurisdiction()->getFreeJudges($courtcase),
            'courtcase' => $courtcase,
            'stats' => $stats
        ]);
        
    }
    
    /**
     * @Route("/admin/jurisdiction/case/{slug}/judge/recuse/{judgeid}", name="lexfutures_admin_jurisdiction_case_judge_recuse")
     * @param string $slug
     * @param integer $judgeid
     * @param Request $request
     */
    public function jurisdictionCaseJudgeRecuseAction(Request $request, $slug, $judgeid)
    {
        
        $util = $this->get(Util::class);
        
        $em = $this->getDoctrine()->getManager();
        
        $courtcase = $this->getDoctrine()->getRepository('AppBundle:Courtcase')->findOneBySlug($slug);
        
        $judge = $this->getDoctrine()->getRepository('AppBundle:Judge')->findOneById($judgeid);
        
        $action = "recuse";
        
        if (null === $courtcase || null === $judge) {
            
            $this->get('session')->getFlashBag()->add("errormsg", "Could not load Courtcase!");
            
            return $this->redirectToRoute('lexfutures_admin_jurisdiction_list');
            
        }
        
        $recusals = unserialize($judge->getRecusals());
        
        if (!$recusals || count($recusals) == 0) {
            
            $recusals = array();
            
            $recusals[] = $courtcase->getId();
            
        } else {
            
            if (in_array($courtcase->getId(), $recusals)) {
                
                if (($key = array_search($courtcase->getId(), $recusals)) !== false) {
                    
                    unset($recusals[$key]);
                    
                    $action = "unrecuse";
                    
                }
                
            } else {
                
                $recusals[] = $courtcase->getId();
                
            }
            
        }
        
        $judge->setRecusals(serialize($recusals));
        
        $em->persist($judge);
        
        $em->flush();
        
        if ($action == "recuse") {
            
            $this->get('session')->getFlashBag()->add("successmsg", $courtcase->getName(). " ruling has been recused from ".$courtcase->getName()."!");
            
        } else {
            
            $this->get('session')->getFlashBag()->add("successmsg", $courtcase->getName(). " ruling has been activated for ".$courtcase->getName()."!");
            
        }
        
        return $this->redirectToRoute('lexfutures_admin_jurisdiction_case_judges', ['slug' => $courtcase->getSlug()]);
    }
    
    /**
     * @Route("/admin/prediction/{id}/ruling/notify", name="lexfutures_admin_jurisdiction_prediction_notify")
     * @param integer $id
     * @param Request $request
     */
    public function jurisdictionCPredictionRulingNotifyAction(Request $request, $id)
    {
        
        $util = $this->get(Util::class);
        
        $em = $this->getDoctrine()->getManager();
        
        //$stats = $util->getDashboardStats($this->getUser());
        
        $userprediction = $this->getDoctrine()->getRepository('AppBundle:Prediction')->findOneById($id);
        
        if (null === $userprediction) {
            
            $this->get('session')->getFlashBag()->add("errormsg", "Could not load Prediction!");
            
            return $this->redirectToRoute('lexfutures_admin_dashboard');
            
        }
        
        $prediction = $userprediction->getCourtcase()->getRuling();
        
        $capturedPrediction = $em->getRepository('AppBundle:Prediction')->findOneById($prediction->getId());
        
        $courtcase = $em->getRepository('AppBundle:Courtcase')->findOneById($userprediction->getCourtcase()->getId());
        
        $jurisdiction = $em->getRepository('AppBundle:Jurisdiction')->findOneById($courtcase->getJurisdiction()->getId());
        
        $season = $jurisdiction->getCurrentSeason(); // Load the current season
        
        $stats = $util->getPointsOverView($userprediction->getUser(), $jurisdiction, $season);
                
        $leaderboard = array();

        $leaderboard['overall'] = $util->getLeaderBoard('overall', 10, $jurisdiction, $season);
        $leaderboard['lawProfessionals'] = $util->getLeaderBoard('lawProfessionals', 10, $jurisdiction, $season);
        $leaderboard['students'] = $util->getLeaderBoard('students', 10, $jurisdiction, $season);
        $leaderboard['academics'] = $util->getLeaderBoard('academics', 10, $jurisdiction, $season);
        $leaderboard['other'] = $util->getLeaderBoard('other', 10, $jurisdiction, $season);
        $leaderboard['open'] = $util->getLeaderBoard('open', 10, $jurisdiction, $season);
        
        
        if ($userprediction->getUser()) {
            //var_dump($userprediction->getUser()->getOccupation()->getCategoryType()); die();

            $user = $this->getDoctrine()->getRepository('AppBundle:User')->findOneById($userprediction->getUser()->getId()); //Load the case

            //$leaderstats = $util->leaderboardStats($userprediction->getUser(), $jurisdiction);

            $pointsTable = $util->pointsTable($userprediction->getUser(), $jurisdiction);

            $newPointsTable = $util->newPointsTable($user, $jurisdiction, $leaderboard);

        } else {

            $stats = null;

            $pointsTable = $newPointsTable = null;

        }   

        // compose mailer
        $message = \Swift_Message::newInstance()
            ->setContentType("text/html")
            ->setSubject('LEXFutures: Judgment')
            ->setFrom('info@lexfutures.com', 'LEXFutures')
            ->setTo($userprediction->getUser()->getEmail())
            //->setTo('divan@sideclick.co.za')
            ->setBcc('rscholiadis@infology.net')
            ->setBody($this->container->get('templating')->render('AppBundle:admin:judgementEmail.html.twig', [
                'key' => md5($userprediction->getUser()->getEmail()), 
                'jurisdiction' => $userprediction->getCourtcase()->getJurisdiction(), 
                'stats' => $stats, 
                'prediction' => $userprediction, 
                'ruling' => $prediction, 
                "awardedPoints" => $userprediction->getFullScore($capturedPrediction),
                'pointstable' => $pointsTable,
                'newPointsTable' => $newPointsTable,
                //'leaderstats' => $leaderstats
            ]));
        $result = $this->container->get('mailer')->send($message);
        
        $notification = new Notification();
        
        $notification->setEmail($userprediction->getUser()->getEmail());
        
        $notification->setRecipient($userprediction->getUser());
        
        $notification->setPrediction($userprediction);
        
        $em->persist($notification);
        
        $em->flush();
        
        $this->get('session')->getFlashBag()->add("successmsg", $userprediction->getCourtcase()->getName(). " ruling ruling notification set to ".$userprediction->getUser()->getEmail()."!");
            
        return $this->redirectToRoute('lexfutures_admin_jurisdiction_case_ruling_summary', ['id' => $prediction->getCourtcase()->getId()]);
        
    }
    
    /**
     * @Route("/admin/jurisdiction/case/{id}/ruling/notify", name="lexfutures_admin_jurisdiction_case_ruling_notify")
     * @param integer $id
     * @param Request $request
     */
    public function caseRulingNotifyAction(Request $request, $id)
    {
        
        echo $id;
        die();
        
    }
    
    /**
     * @Route("/admin/jurisdiction/case/{id}/ruling/summary", name="lexfutures_admin_jurisdiction_case_ruling_summary")
     * @param integer $id
     * @param Request $request
     */
    public function jurisdictionCaseRulingSummaryAction(Request $request, $id)
    {
        
        $util = $this->get(Util::class);
        
        $stats = $util->getDashboardStats($this->getUser());
        
        $courtcase = $this->getDoctrine()->getRepository('AppBundle:Courtcase')->findOneById($id);
        
        if (null === $courtcase) {
            
            $this->get('session')->getFlashBag()->add("errormsg", "Could not load Courtcase!");
            
            return $this->redirectToRoute('lexfutures_admin_jurisdiction_list');
            
        }
        
        $jurisdiction = $courtcase->getJurisdiction();        
        
        
        
        return $this->render('AppBundle:admin:jurisdictionCaseRulingSummary.html.twig', [
            'jurisdiction' => $jurisdiction,
            'courtcase' => $courtcase,
            'stats' => $stats
        ]);
        
    }
    
    /**
     * @Route("/admin/jurisdiction/case/{id}/ruling/", name="lexfutures_admin_jurisdiction_case_ruling")
     * @param integer $id
     * @param Request $request
     */
    public function jurisdictionCaseRulingAction(Request $request, $id)
    {
                
        $util = $this->get(Util::class);
        
        $stats = $util->getDashboardStats($this->getUser());
        
        $em = $this->getDoctrine()->getManager();
        
        $courtcase = $this->getDoctrine()->getRepository('AppBundle:Courtcase')->findOneById($id);
        
        $jurisdiction = $courtcase->getJurisdiction();
        
        if (null === $jurisdiction) {
            
            $this->get('session')->getFlashBag()->add("errormsg", "Could not load Jurisdiction!");
            
            return $this->redirectToRoute('lexfutures_admin_jurisdiction_list');
            
        }
        
        $prediction = $courtcase->getRuling();
        
        if (null === $prediction) {

            $prediction = new Prediction();
            
            $prediction = $util->initPrediction($courtcase, $this->getUser());
            
        } 
        
        if ($request->getMethod() == 'POST') {
            
            $util->updatePrediction($prediction, $_POST);
            
            $prediction->setIsRuling(true);
                        
            $em->persist($prediction);
            
            $em->flush();
            
            $em->clear();
            /*
            $capturedPrediction = $em->getRepository('AppBundle:Prediction')->findOneById($prediction->getId());
            
            if (true) {
                
                foreach ($courtcase->getPredictions() as $userprediction) {
                    if (!$userprediction->getIsRuling() && !$userprediction->getUser()->getUnsub()) {
                        
                        $stats = $util->getPointsOverView($userprediction->getUser(), $userprediction->getCourtcase()->getJurisdiction());
                        
                        $jurisdiction = $userprediction->getCourtcase()->getJurisdiction();
                        
                        $leaderboard = array();

                        $leaderboard['overall'] = $util->getLeaderBoard('overall', 10, $jurisdiction);
                        $leaderboard['lawProfessionals'] = $util->getLeaderBoard('lawProfessionals', 10, $jurisdiction);
                        $leaderboard['students'] = $util->getLeaderBoard('students', 10, $jurisdiction);
                        $leaderboard['academics'] = $util->getLeaderBoard('academics', 10, $jurisdiction);
                        $leaderboard['other'] = $util->getLeaderBoard('other', 10, $jurisdiction);
                        $leaderboard['open'] = $util->getLeaderBoard('open', 10, $jurisdiction);

                        if ($userprediction->getUser()) {
                            //var_dump($userprediction->getUser()->getOccupation()->getCategoryType()); die();
                            
                            $user = $this->getDoctrine()->getRepository('AppBundle:User')->findOneById($userprediction->getUser()->getId()); //Load the case
                            
                            //$leaderstats = $util->leaderboardStats($userprediction->getUser(), $jurisdiction);

                            $pointsTable = $util->pointsTable($userprediction->getUser(), $jurisdiction);

                            $newPointsTable = $util->newPointsTable($user, $jurisdiction, $leaderboard);

                        } else {
                            
                            $stats = null;

                            $pointsTable = $newPointsTable = null;

                        }   
                        
                        // compose mailer
                        $message = \Swift_Message::newInstance()
                            ->setContentType("text/html")
                            ->setSubject('LEXFutures: Judgment')
                            ->setFrom('info@lexfutures.com', 'LEXFutures')
                            ->setTo($userprediction->getUser()->getEmail())
                            //->setTo('divan@sideclick.co.za')
                            ->setBcc('rscholiadis@infology.net')
                            ->setBody($this->container->get('templating')->render('AppBundle:admin:judgementEmail.html.twig', [
                                'key' => md5($userprediction->getUser()->getEmail()), 
                                'jurisdiction' => $userprediction->getCourtcase()->getJurisdiction(), 
                                'stats' => $stats, 
                                'prediction' => $userprediction, 
                                'ruling' => $prediction, 
                                "awardedPoints" => $userprediction->getFullScore($capturedPrediction),
                                'pointstable' => $pointsTable,
                                'newPointsTable' => $newPointsTable,
                                //'leaderstats' => $leaderstats
                            ]));
                        $result = $this->container->get('mailer')->send($message);
                        
                    }
                }
            }
            */
            $this->get('session')->getFlashBag()->add("successmsg", $courtcase->getName(). " ruling has been updated!");
            
            return $this->redirectToRoute('lexfutures_admin_jurisdiction_case_ruling_summary', ['id' => $prediction->getCourtcase()->getId()]);
            
        }
        
        return $this->render('AppBundle:admin:jurisdictionCaseRuling.html.twig', [
            'jurisdiction' => $jurisdiction,
            'courtcase' => $courtcase,
            'prediction' => $prediction,
            'stats' => $stats
        ]);
        
    }
    
}
