<?php

namespace WobbleCode\UserBundle\Provider;

use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\User\OAuthAwareUserProviderInterface;
use HWI\Bundle\OAuthBundle\Security\Core\User\FOSUBUserProvider;
use WobbleCode\UserBundle\OAuth\oAuthMapper;
use WobbleCode\UserBundle\Model\OrganizationInterface;
use WobbleCode\UserBundle\Manager\UserManager;
use WobbleCode\UserBundle\Manager\OrganizationManager;

class UserProvider extends FOSUBUserProvider implements OAuthAwareUserProviderInterface
{
    /**
     * EventDispatcherInterface
     *
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * OrganizationManager
     *
     * @var OrganizationManager
     */
    protected $organizationManager;

    /**
     * oAuthMapper
     *
     * @var oAuthMapper
     */
    protected $oAuthMapper;

    /**
     * Root application dir
     *
     * @var string
     */
    protected $rootDir;

    /**
     * @param rootDir
     */
    public function setRootDir($rootDir)
    {
        $this->rootDir = $rootDir;
    }

    /**
     * @param SessionInterface $session
     */
    public function setUserManager(UserManager $userManager)
    {
        $this->userManager = $userManager;
    }

    /**
     * @param OrganizationManager $organizationManager
     */
    public function setOrganizationManager(OrganizationManager $organizationManager)
    {
        $this->organizationManager = $organizationManager;
    }

    /**
     * @param oAuthMapper $oAuthMapper
     */
    public function setoAuthMapper(oAuthMapper $oAuthMapper)
    {
        $this->oAuthMapper = $oAuthMapper;
    }

    /**
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function setEventDispatcher(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * {@inheritDoc}
     */
    public function connect(UserInterface $user, UserResponseInterface $response)
    {
        $username = $response->getUsername();
        $provider = $response->getResourceOwner()->getName();
        $user->setAuthDataByProvider($provider, ['id' => $username, 'token' => $response->getAccessToken()]);

        $this->userManager->updateUser($user);
    }

    public function oAuthLogin($user, $data)
    {
        $user->setAuthProvider($data['provider']);
        $user->setEmail($data['email']);

        if ($user->getFirstAuthProvider() != 'local') {
            $user->setUsername($data['email']);
        }

        $user->setAuthDataByProvider($data['provider'], ['id' => $data['id'], 'token' => $data['token']]);
        $this->userManager->updateUser($user);
        $this->userManager->enableRememberMe($user);

        $this->eventDispatcher->dispatch(
            'wc_user.user.oauth_login',
            new GenericEvent('wc_user.user.oauth_login', [
                'notifyUserTrigger' => $user,
                'data' => [
                    'user'         => $user,
                    'provider'     => $data['provider']
                ]
            ])
        );

        return $user;
    }

    /**
     * {@inheritdoc}
     *
     * @todo redirect to unverfied email if not verified, this can be a security
     * issue and users can gain access to other account with unverified emails
     */
    public function loadUserByOAuthUserResponse(UserResponseInterface $response)
    {
        $provider = $response->getResourceOwner()->getName();
        $data = $this->oAuthMapper->normalizeData($response);

        if (!empty($data['email']) && $data['emailVerified']) {
            $user = $this->userManager->findUserBy(['emailCanonical' => $data['email']]);
        }

        if (!$user) {
            $user = $this->userManager->getUserByAuthId($data['provider'], $data['id']);
        }

        if (!$user) {
            $user = $this->userManager->createUserFromOAuth($data);
            $contact = $user->getContact();
            $this->oAuthMapper->updateContact($contact, $data);
            $this->userManager->updateUser($user);

            $this->eventDispatcher->dispatch(
                'wc_user.user.oauth_signup',
                new GenericEvent('wc_user.user.oauth_signup', [
                    'notifyUserTrigger' => $user,
                    'data' => [
                        'user'         => $user,
                        'provider'     => $provider
                    ]
                ])
            );
        }

        return $this->oAuthLogin($user, $data);
    }
}
