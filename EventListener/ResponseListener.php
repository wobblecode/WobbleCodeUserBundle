<?php

namespace WobbleCode\UserBundle\EventListener;

use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Cookie;

class ResponseListener
{
    private $session;

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    public function onKernelResponse(FilterResponseEvent $event)
    {
        $response = $event->getResponse();

        if ($this->session->has('REMEMBER_ME') && $response->getStatusCode() == 200) {
            $cookie = new Cookie(
                'REMEMBERME',
                $this->session->get('REMEMBER_ME'),
                time() + 3600 * 24 * 120
            );

            $response->headers->setCookie($cookie);
            $this->session->remove('REMEMBER_ME');
        }
    }
}
