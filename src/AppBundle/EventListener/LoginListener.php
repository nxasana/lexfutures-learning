<?php

namespace AppBundle\EventListener;

use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Event\FormEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Http\SecurityEvents;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

/**
 * Listener responsible to change the redirection at the end of the password resetting
 */
class LoginListener implements EventSubscriberInterface
{
    //private $container;
    private $em;

    public function __construct(\Doctrine\Common\Persistence\ObjectManager $entityManager)
    {
        //$this->container = $container;
        $this->em = $entityManager;
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            FOSUserEvents::SECURITY_IMPLICIT_LOGIN => 'onLogin',
            SecurityEvents::INTERACTIVE_LOGIN => 'onLogin',
        );
    }

    public function onLogin($event)
    {
        // FYI
        // if ($event instanceof UserEvent) {
        //    $user = $event->getUser();
        // }
        // if ($event instanceof InteractiveLoginEvent) {
        //    $user = $event->getAuthenticationToken()->getUser();
        // }

        if ($event instanceof UserEvent) {
            
            $user = $event->getUser();
            
        }
        
        if ($event instanceof InteractiveLoginEvent) {
            
            $user = $event->getAuthenticationToken()->getUser();
            
        }
        
        if ($user) {
            
            $user->setLoginOffsetTimer($user->getLastLogin());
                        
            $this->em->persist($user);
            
            $this->em->flush();
            
        }
    }
}