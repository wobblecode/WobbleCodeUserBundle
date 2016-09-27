<?php

namespace WobbleCode\UserBundle\EventListener;

use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Event\FormEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Listener responsible to change the redirection at the end of the password resetting
 */
class RedirectSubscriber implements EventSubscriberInterface
{
    /**
     * UrlGeneratorInterface
     *
     * @var UrlGeneratorInterface
     */
    private $router;

    /**
     * Route to redirect after Reset password
     *
     * @var string
     */
    private $redirectPathReset;

    /**
     * Route to redirect after confirm user
     *
     * @var string
     */
    private $redirectPathConfirm;

    /**
     * Constructor
     *
     * @param UrlGeneratorInterface $router
     */
    public function __construct(
        UrlGeneratorInterface $router,
        $redirectPathReset,
        $redirectPathConfirm
    ) {
        $this->router = $router;
        $this->redirectPathReset = $redirectPathReset;
        $this->redirectPathConfirm = $redirectPathConfirm;
    }

    public function redirectResetSuccess(FormEvent $event)
    {
        if ($this->redirectPathReset) {
            $url = $this->router->generate($this->redirectPathReset);
            $event->setResponse(new RedirectResponse($url));
        }
    }

    public function redirectRegistrationConfirmed($event)
    {
        if ($this->redirectPathConfirm) {
            $url = $this->router->generate($this->redirectPathConfirm);
            $event->setResponse(new RedirectResponse($url));
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            FOSUserEvents::RESETTING_RESET_SUCCESS => 'redirectResetSuccess',
            FOSUserEvents::REGISTRATION_CONFIRM => 'redirectRegistrationConfirmed'
        ];
    }
}
