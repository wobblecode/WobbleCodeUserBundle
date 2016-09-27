<?php

namespace WobbleCode\UserBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use FOS\UserBundle\Model\User as BaseUser;
use JMS\Serializer\Annotation as Serializer;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\Bundle\MongoDBBundle\Validator\Constraints\Unique;
use WobbleCode\UserBundle\Document\Contact;
use WobbleCode\ManagerBundle\Traits\Document\Attributable;
use WobbleCode\ManagerBundle\Traits\Document\Taggeable;

/**
 * @MongoDB\Document(repositoryClass="WobbleCode\UserBundle\Document\UserRepository")
 * @Serializer\ExclusionPolicy("all")
 * @Unique(
 *     fields={"contact"},
 *     repositoryMethod="findUniqueContactCellPhoneBy",
 *     message="wc_user.mobile.already_registered"
 * )
 */
class User extends BaseUser
{
    /**
     * Trait to add attribute support
     */
    use Attributable;

    /**
     * Trait to add tags support
     */
    use Taggeable;

    /**
     * @MongoDB\Id(strategy="auto")
     * @Serializer\Groups({"ui", "api"})
     * @Serializer\Expose
     */
    protected $id;

    /**
     * Hash of options for the user
     *
     * @MongoDB\Hash
     */
    protected $options = [];

    /**
     * The current resource used for authentication. Eg: github
     *
     * @MongoDB\Field(type="string")
     * @Serializer\Expose
     * @Serializer\Groups({"ui", "api"})
     */
    protected $authProvider = 'local';

    /**
     * Hash of data related to auth providers
     *
     * Example of schema
     *
     *     {
     *       "github": {
     *         "id": "luishdez",
     *         "token": "aoKoj3g3…",
     *         "type" "Bearer",
     *         "scope": "repo,gist",
     *         "expiresIn": "3600"
     *         "expiresAt": "2015-04-23T18:25:43Z"
     *       }
     *     }
     *
     * @MongoDB\Hash
     */
    protected $authData = [];

    /**
     * Hash of data related to tracking or external applications
     *
     * Example of schema
     *
     *     {
     *       "activeCampign": {
     *         "id": "23"
     *       }
     *     }
     *
     * @deprecated use attributes instead.
     *
     * @MongoDB\Hash
     */
    protected $servicesData = [];

    /**
     * The first resource used for authentication signup Eg: github
     *
     * @deprecated use attributes instead.
     *
     * @MongoDB\Field(type="string")
     * @Serializer\Expose
     * @Serializer\Groups({"ui", "api"})
     */
    protected $firstAuthProvider = 'local';

    /**
     * @Gedmo\Timestampable(on="create")
     * @MongoDB\Field(type="date")
     * @Serializer\Expose
     * @Serializer\Groups({"ui"})
     */
    protected $createdAt;

    /**
     * @Gedmo\Timestampable(on="update")
     * @MongoDB\Field(type="date")
     * @Serializer\Expose
     * @Serializer\Groups({"ui"})
     */
    protected $updatedAt;

    /**
     * @MongoDB\Field(type="date")
     * @Serializer\Groups({"ui"})
     */
    protected $deletedAt;

    /**
     * @Gedmo\Blameable(on="update")
     * @MongoDB\ReferenceOne(targetDocument="User")
     * @Serializer\Expose
     * @Serializer\Groups({"ui"})
     */
    protected $updatedBy;

    /**
     * @MongoDB\EmbedOne(targetDocument="Contact")
     * @Serializer\Expose
     * @Serializer\MaxDepth(1)
     * @Serializer\Groups({"ui", "api"})
     */
    protected $contact;

    /**
     * The current active organization. The system currently only allows to
     * navigate under one Organization per session.
     *
     * @MongoDB\ReferenceOne(targetDocument="WobbleCode\UserBundle\Model\OrganizationInterface")
     *
     * @Serializer\Expose
     * @Serializer\MaxDepth(1)
     * @Serializer\Accessor(getter="getActiveOrganizationId")
     * @Serializer\Groups({"ui", "api"})
     */
    protected $activeOrganization;

    /**
     * This load the possible roles for the activeOrganization.
     *
     * This should be always keep in sync with activeOrganization, if and user
     * has the wrong role object loaded could gain permissions to other
     * organizations.
     *
     * @MongoDB\ReferenceOne(targetDocument="Role", cascade={"persist", "remove"})
     */
    protected $activeRole;

    /**
     * @MongoDB\ReferenceMany(targetDocument="WobbleCode\UserBundle\Model\OrganizationInterface", mappedBy="users")
     */
    protected $organizations;

    /**
     * @MongoDB\ReferenceMany(targetDocument="Invitation", mappedBy="from")
     */
    protected $sentInvitations;

    /**
     * @MongoDB\ReferenceMany(targetDocument="Invitation", mappedBy="to")
     */
    protected $acceptedInvitations;

    /**
     * Constructor that calls parent FOSUser Constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->setContact(new Contact);
    }

    /**
     * Return contact name when converts to string the object
     */
    public function __toString()
    {
        return $this->getContactName();
    }

    /**
     * Get id
     *
     * @return id $id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set createdAt
     *
     * @param timestamp $createdAt
     * @return self
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * Get createdAt
     *
     * @return timestamp $createdAt
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set updatedAt
     *
     * @param timestamp $updatedAt
     * @return self
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return timestamp $updatedAt
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Get contact Name
     *
     * @Serializer\VirtualProperty
     * @Serializer\Type("string")
     * @Serializer\SerializedName("contact_name")
     * @Serializer\Groups({"ui", "api"})
     *
     * @return string
     */
    public function getContactName()
    {
        $name = false;
        $lastNames = false;

        if ($this->getContact()) {
            $name = $this->getContact()->getName();
            $lastNames = $this->getContact()->getLastNames();
        }

        if ($name == false && $lastNames == false) {
            return $this->getUsername();
        }

        return trim($name.' '.$lastNames);
    }

    /**
     * Merge new roles with current ones
     *
     * @param array $roles
     *
     * @return User
     */
    public function addRoles($roles)
    {
        return $this->roles = array_merge($this->roles, $roles);
    }

    /**
     * Returns the user roles
     *
     * @return array The roles
     */
    public function getRoles()
    {
        $roles = $this->roles;

        foreach ($this->getGroups() as $group) {
            $roles = array_merge($roles, $group->getRoles());
        }

        $newRoles = [];

        if ($this->getActiveRole()) {
            $newRoles = $this->getActiveRole()->getRoles();
        }

        $finalRoles = array_merge($roles, $newRoles);

        // we need to make sure to have at least one role
        $finalRoles[] = static::ROLE_DEFAULT;

        return array_unique($finalRoles);
    }

    /**
     * Set options
     *
     * @param hash $options
     * @return self
     */
    public function setOptions($options)
    {
        $this->options = $options;
        return $this;
    }

    /**
     * Get options
     *
     * @return hash $options
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Set authProvider
     *
     * @param string $authProvider
     * @return self
     */
    public function setAuthProvider($authProvider)
    {
        $this->authProvider = $authProvider;
        return $this;
    }

    /**
     * Get authProvider
     *
     * @return string $authProvider
     */
    public function getAuthProvider()
    {
        return $this->authProvider;
    }

    /**
     * Set authData
     *
     * @param array $authData
     * @return self
     */
    public function setAuthData($authData)
    {
        $this->authData = $authData;
        return $this;
    }

    /**
     * Get authData
     *
     * @return array $authData
     */
    public function getAuthData()
    {
        return $this->authData;
    }

    /**
     * {@inheritdoc}
     */
    public function setAuthDataByProvider($provider, array $authData)
    {
        $this->authData[$provider] = $authData;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthDataByProvider($provider, array $authData)
    {
        return (isset($this->authData[$provider])) ? $this->authData[$provider] : false;
    }

    /**
     * Set firstAuthProvider
     *
     * @param string $firstAuthProvider
     * @return self
     */
    public function setFirstAuthProvider($firstAuthProvider)
    {
        $this->firstAuthProvider = $firstAuthProvider;
        return $this;
    }

    /**
     * Set servicesData
     *
     * @param array $servicesData
     * @return self
     */
    public function setServicesData($servicesData)
    {
        $this->servicesData = $servicesData;
        return $this;
    }

    /**
     * Get servicesData
     *
     * @return array $servicesData
     */
    public function getServicesData()
    {
        return $this->servicesData;
    }

    /**
     * Set servicesData by key provider
     *
     * @param array $provider
     * @param array $servicesData
     */
    public function setServiceData($service, array $data)
    {
        $this->servicesData[$service] = $data;

        return $this;
    }

    /**
     * Set servicesData by key provider
     *
     * @param array $provider
     * @param array $servicesData
     */
    public function getServiceData($service)
    {
        return $this->servicesData[$service];
    }

    /**
     * Set servicesData by key provider
     *
     * @param array $provider
     * @param array $servicesData
     */
    public function setServiceDatum($service, $key, $value)
    {
        $this->servicesData[$service][$key] = $value;

        return $this;
    }

    /**
     * Set servicesData by key provider
     *
     * @param array $provider
     * @param array $servicesData
     */
    public function getServiceDatum($service, $key)
    {
        if (isset($this->servicesData[$service])) {
            return $this->servicesData[$service][$key];
        }

        return null;
    }

    /**
     * Get firstAuthProvider
     *
     * @return string $firstAuthProvider
     */
    public function getFirstAuthProvider()
    {
        return $this->firstAuthProvider;
    }

    /**
     * Set deletedAt
     *
     * @param date $deletedAt
     * @return self
     */
    public function setDeletedAt($deletedAt)
    {
        $this->deletedAt = $deletedAt;
        return $this;
    }

    /**
     * Get deletedAt
     *
     * @return date $deletedAt
     */
    public function getDeletedAt()
    {
        return $this->deletedAt;
    }

    /**
     * Set updatedBy
     *
     * @param WobbleCode\UserBundle\Document\User $updatedBy
     * @return self
     */
    public function setUpdatedBy(\WobbleCode\UserBundle\Document\User $updatedBy)
    {
        $this->updatedBy = $updatedBy;
        return $this;
    }

    /**
     * Get updatedBy
     *
     * @return WobbleCode\UserBundle\Document\User $updatedBy
     */
    public function getUpdatedBy()
    {
        return $this->updatedBy;
    }

    /**
     * Set contact
     *
     * @param Contact $contact
     * @return self
     */
    public function setContact(\WobbleCode\UserBundle\Document\Contact $contact)
    {
        $this->contact = $contact;
        return $this;
    }

    /**
     * Get contact
     *
     * @return Contact $contact
     */
    public function getContact()
    {
        return $this->contact;
    }

    /**
     * Set activeOrganization
     *
     * @param OrganizationInterface $activeOrganization
     * @return self
     */
    public function setActiveOrganization(\WobbleCode\UserBundle\Model\OrganizationInterface $activeOrganization)
    {
        $this->activeOrganization = $activeOrganization;
        return $this;
    }

    /**
     * Get activeOrganization
     *
     * @return Organization $activeOrganization
     */
    public function getActiveOrganization()
    {
        return $this->activeOrganization;
    }

    /**
     * Get activeOrganizationId
     *
     * @return integer
     */
    public function getActiveOrganizationId()
    {
        if ($this->activeOrganization) {
            return $this->activeOrganization->getId();
        }

        return null;
    }

    /**
     * Set activeRole
     *
     * @param WobbleCode\UserBundle\Document\Role $activeRole
     * @return self
     */
    public function setActiveRole(\WobbleCode\UserBundle\Document\Role $activeRole)
    {
        $this->activeRole = $activeRole;
        return $this;
    }

    /**
     * Get activeRole
     *
     * @return WobbleCode\UserBundle\Document\Role $activeRole
     */
    public function getActiveRole()
    {
        return $this->activeRole;
    }

    /**
     * Add organization
     *
     * @param WobbleCode\UserBundle\Model\OrganizationInterface $organization
     */
    public function addOrganization(\WobbleCode\UserBundle\Model\OrganizationInterface $organization)
    {
        $this->organizations[] = $organization;
    }

    /**
     * Remove organization
     *
     * @param WobbleCode\UserBundle\Model\OrganizationInterface $organization
     */
    public function removeOrganization(\WobbleCode\UserBundle\Model\OrganizationInterface $organization)
    {
        $this->organizations->removeElement($organization);
    }

    /**
     * Get organizations
     *
     * @return \Doctrine\Common\Collections\Collection $organizations
     */
    public function getOrganizations()
    {
        return $this->organizations;
    }

    /**
     * Add sentInvitation
     *
     * @param WobbleCode\UserBundle\Document\Invitation $sentInvitation
     */
    public function addSentInvitation(\WobbleCode\UserBundle\Document\Invitation $sentInvitation)
    {
        $this->sentInvitations[] = $sentInvitation;
    }

    /**
     * Remove sentInvitation
     *
     * @param WobbleCode\UserBundle\Document\Invitation $sentInvitation
     */
    public function removeSentInvitation(\WobbleCode\UserBundle\Document\Invitation $sentInvitation)
    {
        $this->sentInvitations->removeElement($sentInvitation);
    }

    /**
     * Get sentInvitations
     *
     * @return \Doctrine\Common\Collections\Collection $sentInvitations
     */
    public function getSentInvitations()
    {
        return $this->sentInvitations;
    }

    /**
     * Add acceptedInvitation
     *
     * @param WobbleCode\UserBundle\Document\Invitation $acceptedInvitation
     */
    public function addAcceptedInvitation(\WobbleCode\UserBundle\Document\Invitation $acceptedInvitation)
    {
        $this->acceptedInvitations[] = $acceptedInvitation;
    }

    /**
     * Remove acceptedInvitation
     *
     * @param WobbleCode\UserBundle\Document\Invitation $acceptedInvitation
     */
    public function removeAcceptedInvitation(\WobbleCode\UserBundle\Document\Invitation $acceptedInvitation)
    {
        $this->acceptedInvitations->removeElement($acceptedInvitation);
    }

    /**
     * Get acceptedInvitations
     *
     * @return \Doctrine\Common\Collections\Collection $acceptedInvitations
     */
    public function getAcceptedInvitations()
    {
        return $this->acceptedInvitations;
    }
}
