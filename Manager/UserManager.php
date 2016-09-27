<?php

namespace WobbleCode\UserBundle\Manager;

use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use FOS\UserBundle\Doctrine\UserManager as FOSUserManager;
use WobbleCode\UserBundle\Document\User;
use WobbleCode\UserBundle\Document\Contact;

class UserManager extends FOSUserManager
{
    /**
     * EventDispatcher
     *
     * @var EventDispatcher
     */
    protected $eventDispatcher;

    /**
     * MongoDB DocumentManager
     *
     * @var DocumentManager
     */
    protected $dm;

    /**
     * Session Manager
     *
     * @var Session
     */
    protected $session;

    /**
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function setEventDispatcher(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param OrganizationManager $organizationManager
     */
    public function setDocumentManager(DocumentManager $documentManager)
    {
        $this->dm = $documentManager;
    }

    /**
     * @param SessionInterface $session
     */
    public function setSession(SessionInterface $session)
    {
        $this->session = $session;
    }

    /**
     * Creates a new User from oAuth data
     *
     * @param array $data Normalized oAuth data
     *
     * @return User
     */
    public function createUserFromOAuth($data)
    {
        $user = $this->createUser();
        $user->setAuthDataByProvider($data['provider'], ['id' => $data['id'], 'token' => $data['token']]);
        $user->setAuthProvider($data['provider']);
        $user->setFirstAuthProvider($data['provider']);
        $user->setUsername($data['email']);
        $user->setEmail($data['email']);
        $user->setPassword('none');
        $user->setEnabled(true);
        $user->setContact(new Contact);

        return $user;
    }

    /**
     * Update User contact data from geoIp
     *
     * @param User  $user
     * @param array $ipInfo
     *
     * @return self
     */
    public function updateFromGeoIp(User $user, array $ipInfo)
    {
        $contact = $user->getContact();
        $country = $contact->getCountry();
        $city = $contact->getCity();
        $region = $contact->getRegion();

        if (!$country) {
            $contact->setCountry($ipInfo['country']);
        }

        if (!$city) {
            $contact->setCountry($ipInfo['country']);
        }

        if (!$region) {
            $contact->setCountry($ipInfo['country']);
        }

        $this->dm->persist($user);

        return $this;
    }

    /**
     * Set signup attributes for geoIp on signup
     *
     * @param  User  $user
     * @param  array $geoIp
     *
     * @return self
     */
    public function signupLog(User $user, array $ipInfo)
    {
        $user->setAttribute('signupIp', $ipInfo);
        $this->dm->persist($user);
        $this->dm->flush();

        return $this;
    }

    /**
     * Save login geoIp last 20 events
     *
     * @param  User  $user
     * @param  array $geoIp
     *
     * @return self
     */
    public function loginLog(User $user, array $geoIp)
    {
        $geoIp['date'] = date('Y-m-d H:i:s');
        $loginIps = $user->getAttribute('lastLoginIps');

        if (!$loginIps) {
            $loginIps = [];
        }

        array_unshift($loginIps, $geoIp);
        $loginIps = array_slice($loginIps, 0, 3);

        $user->setAttribute('lastLoginIps', $loginIps);
        $this->dm->persist($user);
        $this->dm->flush();

        return $this;
    }

    /**
     * Set locale for an user
     *
     * @param User $user
     *
     * @param $locale
     * @return $this
     */
    public function setLocale(User $user, $locale)
    {
        $user->getContact()->setLocale($locale);

        $this->dm->persist($user);
        $this->dm->flush();

        return $this;
    }

    /**
     * Get user from db registered by an oAuth service from its unique id
     *
     * @param string $provider Service provider Eg: facebook
     * @param string $id       Unique Id used by the provider
     *
     * @return (User|false)
     */
    public function getUserByAuthId($provider, $id)
    {
        return $this->dm->getRepository('WobbleCodeUserBundle:User')->findOneBy(
            [
                "authData.$provider.id" => $id
            ]
        );
    }

    /**
     * Enable Remember Me feature
     *
     * @param User
     */
    public function enableRememberMe($user)
    {
        $rememberMeValue = $this->generateRememberMeCookie($user->getUsername());
        $this->session->set('REMEMBER_ME', $rememberMeValue);
    }

    /**
     * Generate remember me cookie final value
     *
     * @todo Add bundle option to define the class name
     * @todo Add bundle option to get the expire time
     * @todo Add functionality to get the secret key from service
     *
     * @param string $username Username to remember
     *
     * @return string Cookie final value
     */
    protected function generateRememberMeCookie($username)
    {
        $key = 'ThisTokenIsNotSoSecretChangeIt';
        $class = 'WobbleCode\UserBundle\Document\User';
        $password = 'none';
        $expires = time()+2592000;

        $hash = hash('sha256', $class.$username.$expires.$password.$key);

        return $this->encodeCookie([$class, base64_encode($username), $expires, $hash]);
    }

    /**
     * Encodes the cookie parts
     *
     * @param array $cookieParts
     *
     * @return string
     */
    protected function encodeCookie(array $cookieParts)
    {
        return base64_encode(implode(':', $cookieParts));
    }
}
