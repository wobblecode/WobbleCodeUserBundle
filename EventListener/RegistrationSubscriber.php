<?php

namespace WobbleCode\UserBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\HttpFoundation\RequestStack;
use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Event\UserEvent;
use FOS\UserBundle\Event\FilterUserResponseEvent;
use WobbleCode\NotificationBundle\Document\Event;
use WobbleCode\UserBundle\Manager\OrganizationManager;
use WobbleCode\UserBundle\Manager\UserManager;
use WobbleCode\UserBundle\Manager\RoleManager;
use WobbleCode\NotificationBundle\Manager\SubscriptionManager;

/**
 * @todo subscription actions should be move to parent AppBundle in order
 * decouple concerns
 */
class RegistrationSubscriber implements EventSubscriberInterface
{
    /**
     * RequestStack
     *
     * @var RequestStack $requestStack
     */
    protected $requestStack;

    /**
     * $organizationManager
     *
     * @var OrganizationManager
     */
    protected $organizationManager;

    /**
     * $userManager
     *
     * @var UserManager
     */
    protected $userManager;

    /**
     * Request
     *
     * @var $request
     */
    protected $roleManager;

    /**
     * UrlGeneratorInterface
     *
     * @var UrlGeneratorInterface
     */
    protected $router;

    /**
     * Set the posibble languages
     *
     * @var array
     */
    protected $availableLanguages;

    /**
     * @param RequestStack $requestStack
     * @param Notificator  $organizationManager
     */
    public function __construct(
        RequestStack $requestStack,
        OrganizationManager $organizationManager,
        UserManager $userManager,
        RoleManager $roleManager,
        SubscriptionManager $subscriptionManager,
        array $availableLanguages
    ) {
        $this->requestStack = $requestStack;
        $this->organizationManager = $organizationManager;
        $this->userManager = $userManager;
        $this->roleManager = $roleManager;
        $this->subscriptionManager = $subscriptionManager;
        $this->availableLanguages = $availableLanguages;
    }

    public function setupOrganizationFos(FilterUserResponseEvent $event)
    {
        $user = $event->getUser();
        $this->setupOrganization($user);
    }

    public function setupOrganizationGeneric(GenericEvent $event)
    {
        $data = $event->getArgument('data');
        $user = $data['user'];

        $this->setupOrganization($user);
    }

    public function setupOrganization($user)
    {
        $organization = $this->organizationManager->setupOrganization($user);
        $this->subscriptionManager->updateSubscriptions($user, $organization);
        $this->roleManager->switchOrganization($user, $organization);
    }

    /**
     * @param  GenericEvent $event
     */
    public function updateFromIp(GenericEvent $event)
    {
        $ipInfo = $this->getIpInfo();
        $data = $event->getArgument('data');
        $user = $data['user'];

        $this->userManager->updateFromGeoIp($user, $ipInfo);
        $this->userManager->signupLog($user, $ipInfo);
    }

    /**
     * @param  GenericEvent $event
     */
    public function updateFromIpFos(UserEvent $event)
    {
        $ipInfo = $this->getIpInfo();
        $user = $event->getUser();

        $this->userManager->updateFromGeoIp($user, $ipInfo);
        $this->userManager->signupLog($user, $ipInfo);
    }

    /**
     * @param GenericEvent $event
     */
    public function loginLog(GenericEvent $event)
    {
        $ipInfo = $this->getIpInfo();
        $data = $event->getArgument('data');
        $user = $data['user'];

        $this->userManager->loginLog($user, $ipInfo);
    }

    /**
     * @param GenericEvent $event
     */
    public function loginLogFos(UserEvent $event)
    {
        $ipInfo = $this->getIpInfo();
        $user = $event->getUser();

        $this->userManager->loginLog($user, $ipInfo);
    }

    /**
     * Gets array with IP info and Geo Ip
     *
     * @return array
     */
    public function getIpInfo()
    {
        $request = $this->requestStack->getCurrentRequest();

        $ipInfo = [];
        $ipInfo['country'] = $request->server->get('GEOIP_COUNTRY_CODE', null);
        $ipInfo['city'] = $request->server->get('GEOIP_CITY', null);
        $ipInfo['region'] = $request->server->get('GEOIP_REGION_NAME', null);
        $ipInfo['ip'] = $request->getClientIp();

        return $ipInfo;
    }

    public function saveLocale(FilterUserResponseEvent $event)
    {
        $request = $event->getRequest();
        $user = $event->getUser();
        $default = $request->getPreferredLanguage($this->availableLanguages);
        
        $this->userManager->setLocale($user, $default);
    }

    /**
     * Execute first updateFromIp because at signup we clone Contact from user
     * to Organization. So we keep the country and other data updateFromGeoIp
     *
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'wc_user.user.oauth_login' => ['loginLog'],
            FOSUserEvents::SECURITY_IMPLICIT_LOGIN => ['loginLogFos'],
            'wc_user.user.oauth_signup' => [
                ['updateFromIp', 0],
                ['setupOrganizationGeneric', 0],
            ],
            FOSUserEvents::REGISTRATION_CONFIRMED => [
                ['updateFromIpFos', 0],
                ['setupOrganizationFos', 0],
                ['saveLocale', 0],
            ]
        ];
    }
}
