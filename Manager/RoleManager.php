<?php

namespace WobbleCode\UserBundle\Manager;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Doctrine\ODM\MongoDB\DocumentManager;
use WobbleCode\UserBundle\Provider\UserProvider;
use WobbleCode\UserBundle\Model\OrganizationInterface;
use WobbleCode\UserBundle\Document\User;
use WobbleCode\UserBundle\Document\Role;

class RoleManager
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
    private $dm;

    /**
     * TokenStorage.
     *
     * @var TokenStorage
     */
    private $tokenStorage;

    /**
     * AuthorizationChecker.
     *
     * @var AuthorizationChecker
     */
    private $authorizationChecker;

    /**
     * $UserProvider
     *
     * @var [type]
     */
    private $userProvider;

    /**
     * User manager constructor
     *
     * @param DocumentManager       $documentManager
     * @param TokenStorage          $tokenStorage
     * @param AuthorizationChecker  $authorizationChecker
     * @param UserProvider          $userManager
     *
     * @return void
     */
    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        DocumentManager $documentManager,
        TokenStorage $tokenStorage,
        AuthorizationChecker $authorizationChecker,
        UserProvider $userProvider
    ) {
        $this->dm = $documentManager;
        $this->tokenStorage = $tokenStorage;
        $this->authorizationChecker = $authorizationChecker;
        $this->eventDispatcher = $eventDispatcher;
        $this->userProvider = $userProvider;
    }

    /**
     * Switch the working organization of logged user
     *
     * @todo update security context calls. Deprecated since version 2.6, to be
     * removed in 3.0. Use AuthorizationCheckerInterface::isGranted() instead.
     * http://symfony.com/blog/new-in-symfony-2-6-security-component-improvements
     *
     * @param User         $user
     * @param OrganizationInterface $organization
     *
     * @return void
     */
    public function switchOrganization(User $user, OrganizationInterface $organization)
    {
        $role = $this->getOrganizationRole($user, $organization);

        if ($role == false && !$this->authorizationChecker->isGranted('ROLE_SUPER_ADMIN')) {
            throw new NotFoundHttpException('Roles in organization not found');
        }

        $userRoles = $this->removeOrganizationRoles($user->getRoles());

        $user->setRoles(array_merge($userRoles, $role->getroles()));
        $user->setActiveOrganization($organization);
        $user->setActiveRole($role);

        $this->dm->persist($user);
        $this->dm->flush();

        $token = new \Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken(
            $user,
            null,
            'main',
            $user->getRoles()
        );

        $this->tokenStorage->setToken($token);
        $this->userProvider->refreshUser($user);
    }

    /**
     * Get Roles in all organization user is member of
     *
     * @param User         $user
     *
     * @return (Role|false)
     */
    public function getOrganizationRoles($user)
    {
        return $this->dm->getRepository('WobbleCodeUserBundle:Role')->findBy(
            [
                'user.$id' => new \MongoId($user->getId())
            ]
        );
    }

    /**
     * Get user role definition within an organization
     *
     * @param User         $user
     * @param OrganizationInterface $organizatgion
     *
     * @return (Role|false)
     */
    public function getOrganizationRole($user, $organization)
    {
        return $this->dm->getRepository('WobbleCodeUserBundle:Role')->findOneBy(
            [
                'organization.$id' => new \MongoId($organization->getId()),
                'user.$id' => new \MongoId($user->getId())
            ]
        );
    }

    public function removeOrganizationRoles($roles)
    {
        return array_filter(
            $roles,
            function ($var) {
                return !preg_match('/ROLE_ORGANIZATION/', $var);
            }
        );
    }
}
