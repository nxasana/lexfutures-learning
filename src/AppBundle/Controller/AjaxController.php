<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use AppBundle\Service\Util;

use AppBundle\Entity\Jurisdiction;
use AppBundle\Entity\File;
use AppBundle\Entity\Courtcase;
use AppBundle\Entity\Judge;
use AppBundle\Entity\User;
use AppBundle\Entity\Note;
use AppBundle\Entity\Prediction;
use AppBundle\Entity\EmailComm;


class AjaxController extends Controller
{
        
    /**
     * @Route("/ajax/case/delete/preview", name="lexfutures_ajax_case_delete_preview")
     */
    public function caseDeletePreviewAction(Request $request)
    {
        
        $util = $this->get(Util::class);
        
        $id = $_POST['id'];
        
        $case = $this->getDoctrine()->getRepository('AppBundle:CourtCase')->findOneById($id); //Load the case
        
        $email = $_POST["email"];
        
        $content = $_POST['content'];
        
        $status = "OK";
             
        $subject = "A Courtcase has been removed from LEXFutures";
        
        $message = \Swift_Message::newInstance()
            ->setContentType("text/html")
            ->setSubject($subject)
            ->setFrom($this->getParameter('email_from'), $this->getParameter('email_from_name'))
            ->setTo($email)
            ->setBody($this->container->get('templating')->render('@App/Templates/deletedCase.html.twig', array('case' => $case, 'content' => nl2br($content), 'name' => 'Recipient Name')), 'text/html');

        $mailResponse = $this->container->get('mailer')->send($message);
        
        if (1 === (int) $mailResponse) {
            
            $response = array("code" => 200, "status" => $status, "msg" => "Email Successfully Sent");
            
        }  else {
            
            $response = array("code" => 500, "status" => "ERR", "msg" => "There was an error sending the Email");
            
        }
        
        $emailComm = new EmailComm();
        
        $emailComm->setEmailBody($this->container->get('templating')->render('@App/Templates/deletedCase.html.twig', array('case' => $case, 'content' => nl2br($content), 'name' => 'Recipient Name')));
        
        $emailComm->setRecipientEmail($email);
        
        $emailComm->setEmailSubject($subject);
        
        $emailComm->setResponse($mailResponse);
        
        $emailComm->setCommType(EmailComm::TYPE_CASE_DELETED);
        
        $emailComm->setObjId($case->getId());
        
        $em = $this->getDoctrine()->getManager();
        
        $em->persist($emailComm);
        
        $em->flush();
        
        //$response = array("code" => 200, "status" => $status, "msg" => $msg);
        
        return new Response(json_encode($response));
        
    }
    
    /**
     * @Route("/ajax/case/delete", name="lexfutures_ajax_case_delete")
     */
    public function caseDeleteAction(Request $request)
    {
        
        $util = $this->get(Util::class);
        
        $em = $this->getDoctrine()->getManager();
        
        $id = $_POST['id'];
        
        // Delete the case - SOFT DELETE
        
        $case = $this->getDoctrine()->getRepository('AppBundle:CourtCase')->findOneById($id); //Load the case
        
        $case->setStatus(CourtCase::STATUS_DELETED);
        
        $em->persist($case);
        
        $em->flush();
        
        $notify = $_POST["notify"];
        
        if ($notify === 'no') {
            
            $notify = false;
            
        } else {
            
            $notify = true;
            
        }
        
        $content = $_POST['content'];
        
        $status = "OK";
             
        $subject = "A Courtcase has been removed from LEXFutures";
        
        $successes = $failures = 0;
        
        if ($notify) { 
            
            foreach ($case->getUserPredictions() as $prediction) {

                $message = \Swift_Message::newInstance()
                    ->setContentType("text/html")
                    ->setSubject($subject)
                    ->setFrom($this->getParameter('email_from'), $this->getParameter('email_from_name'))
                    ->setTo($prediction->getUser()->getEmail())
                    ->setBody($this->container->get('templating')->render('@App/Templates/deletedCase.html.twig', array('case' => $case, 'content' => nl2br($content), 'name' => $prediction->getUser()->getFirstName())), 'text/html');

                $mailResponse = $this->container->get('mailer')->send($message);



                if (1 === (int) $mailResponse) {

                    $successes += 1;

                }  else {

                    $failures += 1;

                }

                $emailComm = new EmailComm();

                $emailComm->setEmailBody($this->container->get('templating')->render('@App/Templates/deletedCase.html.twig', array('case' => $case, 'content' => nl2br($content), 'name' => 'Recipient Name')));

                $emailComm->setRecipientEmail($prediction->getUser()->getEmail());

                $emailComm->setResponse($mailResponse);
                
                $emailComm->setEmailSubject($subject);

                $emailComm->setCommType(EmailComm::TYPE_CASE_DELETED);

                $emailComm->setObjId($case->getId());

                $em->persist($emailComm);

                $em->flush();
        
            }
            
            $msg = "The Case has been deleted. ".$successes ." notifications SUCCESSFULLY sent. ".$failures ." notifications could not be sent.";
            
            $response = array("code" => 200, "status" => $status, "msg" => $msg);
            
        } else {
            
            $msg = "The Case has been deleted. No notifications sent. ";
            
            $response = array("code" => 200, "status" => $status, "msg" => $msg);
            
        }
                
        $response = array("code" => 200, "status" => $status, "msg" => $msg);
        
        return new Response(json_encode($response));
        
    }
    
    
}
