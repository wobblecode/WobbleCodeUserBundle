<?php

namespace WobbleCode\UserBundle\Manager;

use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Doctrine\ODM\MongoDB\DocumentManager;
use WobbleCode\UserBundle\Model\OrganizationInterface;
use WobbleCode\UserBundle\Document\User;
use WobbleCode\UserBundle\Document\Contact;
use WobbleCode\UserBundle\Document\Role;
use WobbleCode\UserBundle\Document\Invitation;

class OrganizationManager
{
    /**
     * @var string FQNC for organization
     */
    protected $organizationClass;

    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @var Session
     */
    protected $session;

    /**
     * MongoDB DocumentManager
     *
     * @var DocumentManager
     */
    protected $dm;

    /**
     * Constructor
     *
     * @param DocumentManager $dm
     */
    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        Session $session,
        DocumentManager $documentManager,
        $organizationClass
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->session = $session;
        $this->dm = $documentManager;
        $this->organizationClass = $organizationClass;
    }

    /**
     * Setup organization from signup. It chekcs if there is an invitationHash
     * in session
     *
     * @todo Add Bundle option for $guestCreateOrganization
     *
     * @param User $user
     *
     * @return Organization
     */
    public function setupOrganization(User $user)
    {
        $guestCreateOrganization = false;
        $invitationHash = $this->session->get('newInvitation', false);

        if ($guestCreateOrganization || $invitationHash == false) {
            $organization = $this->createOrganization($user);
        }

        if ($invitationHash) {
            $organization = $this->processInvitationByHash($user, $invitationHash);
        }

        return $organization;
    }

    /**
     * It creates and completes a new Organization
     *
     * @param OrganizationInterface $organization New empty organization
     * @param User         $user         User that owns the organizartion
     *
     * @return Organization The new organization
     */
    public function createOrganization(User $user)
    {
        $organization = new $this->organizationClass;
        $organization->setOwner($user);
        $organization->addUser($user);

        $role = new Role;
        $role->setRoles(array('ROLE_ORGANIZATION_OWNER'));
        $role->setUser($user);
        $role->setOrganization($organization);

        $contact = $user->getContact();

        if (!$contact) {
            $contact = new Contact;
            $user->setContact($contact);
        }

        $organizationContact = clone $user->getContact();
        $organization->setContact($organizationContact);
        $user->setActiveOrganization($organization);
        $user->setActiveRole($role);

        $this->dm->persist($user);
        $this->dm->persist($role);
        $this->dm->persist($contact);
        $this->dm->persist($organizationContact);
        $this->dm->persist($organization);
        $this->dm->flush();

        $this->eventDispatcher->dispatch(
            'wc_user.organization.create',
            new GenericEvent('wc_user.organization.create', array(
                'notifyUser'                => $user,
                'notifyOrganizationTrigger' => $organization,
                'notifyOrganizations'       => [$organization],
                'data'                      => [
                    'organization' => $organization
                ]
            ))
        );

        return $organization;
    }

    /**
     * Add member to an organization with specifics roles
     *
     * @param OrganizationInterface $organization
     * @param User         $user
     * @param array        $roles
     */
    public function addMember(OrganizationInterface $organization, User $user, array $roles)
    {
        if ($organization->getOwner() === null) {
            $organization->setOwner($user);
        }

        $user->addOrganization($organization);
        $organization->addUser($user);

        $role = new Role();
        $role->setOrganization($organization);
        $role->setUser($user);
        $role->setRoles($roles);

        $this->dm->persist($user);
        $this->dm->persist($organization);
        $this->dm->persist($role);
        $this->dm->flush();
    }

    /**
     * Finds an Invitation by Hash and add member if exists
     *
     * @param User   $user
     * @param string $hash Secret Invitation Hash
     */
    public function processInvitationByHash(User $user, $hash)
    {
        $invitation = $this->dm->getRepository('WobbleCodeUserBundle:Invitation')->findOneBy(
            [
                'hash'   => $hash,
                'status' => 'pending'
            ]
        );

        if (!$invitation) {
            return false;
        }

        $organization = $invitation->getOrganization();
        $this->addMemberByInvitation($organization, $user, $invitation);

        return $organization;
    }

    /**
     * Add member to an Organization using settings from invitation
     *
     * @param OrganizationInterface $organization
     * @param User         $user
     * @param Invitation   $invitation
     */
    public function addMemberByInvitation(OrganizationInterface $organization, User $user, Invitation $invitation)
    {
        $this->addMember($organization, $user, $invitation->getRoles());

        $invitation->setStatus('accepted');
        $invitation->setTo($user);
        $this->dm->persist($invitation);
        $this->dm->flush();
    }

    /**
     * Gets the system admin Organization
     *
     * @return OrganizationInterface
     */
    public function getAdminOwner()
    {
        $repo = $this->dm->getRepository($this->organizationClass);

        return $repo->findOneBy([
            'adminOwner' => true
        ]);
    }

    /**
     * @todo move to InvoiceProfileManager service
     */
    public function getNewInvoiceRef(OrganizationInterface $organization)
    {
        $invoiceProfile = $organization->getInvoiceProfile();
        $id = $invoiceProfile->getRefId();
        $newId = $id + 1;
        $invoiceProfile->setRefId($newId);
        $pattern = $invoiceProfile->getRefId();

        $this->dm->persist($invoiceProfile);
        $this->dm->flush();

        return str_replace('{ref}', $newId, $pattern);
    }
}
