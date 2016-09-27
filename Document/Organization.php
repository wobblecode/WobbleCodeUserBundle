<?php

namespace WobbleCode\UserBundle\Document;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Doctrine\Bundle\MongoDBBundle\Validator\Constraints\Unique;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation as Serializer;
use WobbleCode\UserBundle\Model\OrganizationInterface;
use WobbleCode\UserBundle\Document\Contact;
use WobbleCode\ManagerBundle\Traits\Document\Attributable;
use WobbleCode\ManagerBundle\Traits\Document\Taggeable;

/**
 * @Serializer\ExclusionPolicy("all")
 * @MongoDB\Document
 * @MongoDB\UniqueIndex(keys={"owner"="asc", "name"="asc"})
 */
class Organization implements OrganizationInterface
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
     * @Serializer\Groups({"ui", "ui-admin", "api"})
     * @Serializer\Expose
     */
    protected $id;

    /**
     * @MongoDB\Field(type="boolean")
     * @Serializer\Groups({"ui", "ui-admin", "api"})
     * @Serializer\Expose
     * @Assert\Type(type="boolean")
     */
    protected $enabled = true;

    /**
     * @MongoDB\Field(type="boolean")
     * @Serializer\Groups({"ui", "ui-admin", "api"})
     * @Serializer\Expose
     * @Assert\Type(type="boolean")
     */
    protected $locked = false;

    /**
     * @MongoDB\Field(type="string")
     * @Serializer\Groups({"ui", "ui-admin", "api"})
     * @Serializer\Expose
     * @Assert\Choice(choices={"freelance", "company"})
     */
    protected $type = 'freelance';

    /**
     * @MongoDB\Field(type="string")
     * @Serializer\Groups({"ui", "ui-admin", "api"})
     * @Serializer\Expose
     * @Assert\Length(
     *      min="3",
     *      max="20",
     *      minMessage="Your first name must be at least {{ limit }} characters length",
     *      maxMessage="Your first name cannot be longer than {{ limit }} characters length"
     * )
     */
    protected $name;

    /**
     * @MongoDB\Field(type="string")
     * @Serializer\Groups({"ui-admin"})
     * @Serializer\Expose
     */
    protected $comment;

    /**
     * Determine if the organization is the system main organization
     *
     * @MongoDB\Field(type="boolean")
     * @Serializer\Groups({"ui", "ui-admin", "api"})
     * @Serializer\Expose
     * @Assert\Type(type="boolean")
     */
    protected $adminOwner;

    /**
     * @MongoDB\Field(type="date")
     * @Gedmo\Timestampable(on="create")
     * @Serializer\Groups({"ui", "api"})
     * @Serializer\Expose
     */
    protected $createdAt;

    /**
     * @Gedmo\Timestampable(on="update")
     * @MongoDB\Field(type="date")
     */
    protected $updatedAt;

    /**
     * @Gedmo\Blameable(on="create")
     * @MongoDB\ReferenceOne(targetDocument="WobbleCode\UserBundle\Document\User", cascade={"persist"})
     */
    protected $createdBy;

    /**
     * @Gedmo\Blameable(on="update")
     * @MongoDB\ReferenceOne(targetDocument="WobbleCode\UserBundle\Document\User")
     */
    protected $updatedBy;

    /**
     * @MongoDB\Field(type="date")
     */
    private $deletedAt;

    /**
     * @MongoDB\ReferenceOne(
     *     targetDocument="WobbleCode\UserBundle\Document\User",
     *     cascade={"all"},
     *     orphanRemoval=true
     * )
     * @Serializer\Expose
     * @Serializer\Groups({"ui", "ui-admin", "api"})
     */
    protected $owner;

    /**
     * @MongoDB\EmbedOne(targetDocument="Contact")
     * @Serializer\Expose
     * @Serializer\MaxDepth(1)
     * @Serializer\Accessor(getter="getContact")
     * @Serializer\Groups({"ui", "api", "ui-admin"})
     */
    protected $contact;

    /**
     * @MongoDB\ReferenceMany(
     *     targetDocument="WobbleCode\UserBundle\Document\Invitation",
     *     mappedBy="organization",
     *     cascade={"all"},
     *     orphanRemoval=true
     * )
     */
    protected $invitations;

    /**
     * @MongoDB\ReferenceMany(
     *     targetDocument="WobbleCode\UserBundle\Document\Role",
     *     mappedBy="organization",
     *     cascade={"all"},
     *     orphanRemoval=true
     * )
     */
    protected $roles;

    /**
     * @MongoDB\ReferenceMany(
     *     targetDocument="WobbleCode\BillingBundle\Document\PaymentProfile",
     *     cascade={"all"},
     *     orphanRemoval=true,
     *     mappedBy="organization"
     * )
     */
    protected $paymentProfiles;

    /**
     * ReferenceOne hacked to work with lazyloading
     *
     * @see https://github.com/doctrine/mongodb-odm/issues/180
     * @todo this should be mobed to a final class outside UserBundle and using
     *       EmbedOne.
     *
     * @MongoDB\ReferenceMany(
     *     targetDocument="WobbleCode\BillingBundle\Document\InvoiceProfile",
     *     mappedBy="organization"
     * )
     */
    protected $invoiceProfile;

    /**
     * @MongoDB\ReferenceMany(targetDocument="User", mappedBy="activeOrganization")
     */
    protected $activeUsers;

    /**
     * @MongoDB\ReferenceMany(targetDocument="User", mappedBy="organizations", cascade={"all"})
     */
    protected $users;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->users = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Return name string from owner contact if is same as owner
     *
     * @return string Full name
     */
    public function __toString()
    {
        return (string) $this->getContactName();
    }

    /**
     * Get contact Name
     *
     * @Serializer\VirtualProperty
     * @Serializer\Type("string")
     * @Serializer\SerializedName("contact_name")
     * @Serializer\Groups({"ui", "ui-admin", "api"})
     *
     * @return string
     */
    public function getContactName()
    {
        $contactName = $this->getContact()->getName();

        if (!$contactName) {
            $contactName = $this->getOwner()->getContactName();
        }

        return $contactName;
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
     * Set type
     *
     * @param string $type
     * @return self
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * Get type
     *
     * @return string $type
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set comment
     *
     * @param string $comment
     * @return self
     */
    public function setComment($comment)
    {
        $this->comment = $comment;
        return $this;
    }

    /**
     * Get comment
     *
     * @return string $comment
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return self
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Get name
     *
     * @return string $name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set enabled
     *
     * @param boolean $enabled
     * @return self
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;
        return $this;
    }

    /**
     * Get enabled
     *
     * @return boolean $enabled
     */
    public function getEnabled()
    {
        return $this->enabled;
    }

    /**
     * Set locked
     *
     * @param boolean $enabled
     * @return self
     */
    public function setLocked($locked)
    {
        $this->locked = $locked;
        return $this;
    }

    /**
     * Get locked
     *
     * @return boolean $locked
     */
    public function getLocked()
    {
        return $this->locked;
    }

    /**
     * Set adminOwner
     *
     * @param boolean $adminOwner
     * @return self
     */
    public function setAdminOwner($adminOwner)
    {
        $this->adminOwner = $adminOwner;
        return $this;
    }

    /**
     * Get adminOwner
     *
     * @return boolean $adminOwner
     */
    public function getAdminOwner()
    {
        return $this->adminOwner;
    }

    /**
     * Set createdAt
     *
     * @param date $createdAt
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
     * @return date $createdAt
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set updatedAt
     *
     * @param date $updatedAt
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
     * @return date $updatedAt
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Set createdBy
     *
     * @param WobbleCode\UserBundle\Document\User $createdBy
     * @return self
     */
    public function setCreatedBy(\WobbleCode\UserBundle\Document\User $createdBy)
    {
        $this->createdBy = $createdBy;
        return $this;
    }

    /**
     * Get createdBy
     *
     * @return WobbleCode\UserBundle\Document\User $createdBy
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
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
     * Set owner
     *
     * @param WobbleCode\UserBundle\Document\User $owner
     * @return self
     */
    public function setOwner(\WobbleCode\UserBundle\Document\User $owner)
    {
        $this->owner = $owner;
        return $this;
    }

    /**
     * Get owner
     *
     * @return WobbleCode\UserBundle\Document\User $owner
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * Set invitations
     *
     * @param WobbleCode\UserBundle\Document\Invitation $invitations
     * @return self
     */
    public function setInvitations(\WobbleCode\UserBundle\Document\Invitation $invitations)
    {
        $this->invitations = $invitations;
        return $this;
    }

    /**
     * Get invitations
     *
     * @return WobbleCode\UserBundle\Document\Invitation $invitations
     */
    public function getInvitations()
    {
        return $this->invitations;
    }

    /**
     * Set roles
     *
     * @param WobbleCode\UserBundle\Document\Role $roles
     * @return self
     */
    public function setRoles(\WobbleCode\UserBundle\Document\Role $roles)
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * Get roles
     *
     * @return WobbleCode\UserBundle\Document\Role $roles
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * Set contact
     *
     * @param WobbleCode\UserBundle\Document\Contact $contact
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
     * @return WobbleCode\UserBundle\Document\Contact $contact
     */
    public function getContact()
    {
        return $this->contact;
    }

    /**
     * Set paymentProfiles
     *
     * @param WobbleCode\BillingBundle\Document\PaymentProfile $paymentProfiles
     * @return self
     */
    public function setPaymentProfiles(\WobbleCode\BillingBundle\Document\PaymentProfile $paymentProfiles)
    {
        $this->paymentProfiles = $paymentProfiles;
        return $this;
    }

    /**
     * Get paymentProfiles
     *
     * @return WobbleCode\BillingBundle\Document\PaymentProfile $paymentProfiles
     */
    public function getPaymentProfiles()
    {
        return $this->paymentProfiles;
    }

    /**
     * Set invoiceProfile
     *
     * @param WobbleCode\BillingBundle\Document\InvoiceProfile $invoiceProfile
     * @return self
     */
    public function setInvoiceProfile(\WobbleCode\BillingBundle\Document\InvoiceProfile $invoiceProfile)
    {
        $this->invoiceProfile = $invoiceProfile;
        return $this;
    }

    /**
     * Get invoiceProfile
     *
     * @return WobbleCode\BillingBundle\Document\InvoiceProfile $invoiceProfile
     */
    public function getInvoiceProfile()
    {
        return $this->invoiceProfile[0];
    }

    /**
     * Set activeUsers
     *
     * @param WobbleCode\UserBundle\Document\User $activeUsers
     * @return self
     */
    public function setActiveUsers(\WobbleCode\UserBundle\Document\User $activeUsers)
    {
        $this->activeUsers = $activeUsers;
        return $this;
    }

    /**
     * Get activeUsers
     *
     * @return WobbleCode\UserBundle\Document\User $activeUsers
     */
    public function getActiveUsers()
    {
        return $this->activeUsers;
    }

    /**
     * Add user
     *
     * @param WobbleCode\UserBundle\Document\User $user
     */
    public function addUser(\WobbleCode\UserBundle\Document\User $user)
    {
        $this->users[] = $user;
    }

    /**
     * Remove user
     *
     * @param WobbleCode\UserBundle\Document\User $user
     */
    public function removeUser(\WobbleCode\UserBundle\Document\User $user)
    {
        $this->users->removeElement($user);
    }

    /**
     * Get users
     *
     * @return \Doctrine\Common\Collections\Collection $users
     */
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * Add invitation
     *
     * @param WobbleCode\UserBundle\Document\Invitation $invitation
     */
    public function addInvitation(\WobbleCode\UserBundle\Document\Invitation $invitation)
    {
        $this->invitations[] = $invitation;
    }

    /**
     * Remove invitation
     *
     * @param WobbleCode\UserBundle\Document\Invitation $invitation
     */
    public function removeInvitation(\WobbleCode\UserBundle\Document\Invitation $invitation)
    {
        $this->invitations->removeElement($invitation);
    }

    /**
     * Add role
     *
     * @param WobbleCode\UserBundle\Document\Role $role
     */
    public function addRole(\WobbleCode\UserBundle\Document\Role $role)
    {
        $this->roles[] = $role;
    }

    /**
     * Remove role
     *
     * @param WobbleCode\UserBundle\Document\Role $role
     */
    public function removeRole(\WobbleCode\UserBundle\Document\Role $role)
    {
        $this->roles->removeElement($role);
    }

    /**
     * Add paymentProfile
     *
     * @param WobbleCode\BillingBundle\Document\PaymentProfile $paymentProfile
     */
    public function addPaymentProfile(\WobbleCode\BillingBundle\Document\PaymentProfile $paymentProfile)
    {
        $this->paymentProfiles[] = $paymentProfile;
    }

    /**
     * Remove paymentProfile
     *
     * @param WobbleCode\BillingBundle\Document\PaymentProfile $paymentProfile
     */
    public function removePaymentProfile(\WobbleCode\BillingBundle\Document\PaymentProfile $paymentProfile)
    {
        $this->paymentProfiles->removeElement($paymentProfile);
    }

    /**
     * Add invoiceProfile
     *
     * @param WobbleCode\BillingBundle\Document\InvoiceProfile $invoiceProfile
     */
    public function addInvoiceProfile(\WobbleCode\BillingBundle\Document\InvoiceProfile $invoiceProfile)
    {
        $this->invoiceProfile[] = $invoiceProfile;
    }

    /**
     * Remove invoiceProfile
     *
     * @param WobbleCode\BillingBundle\Document\InvoiceProfile $invoiceProfile
     */
    public function removeInvoiceProfile(\WobbleCode\BillingBundle\Document\InvoiceProfile $invoiceProfile)
    {
        $this->invoiceProfile->removeElement($invoiceProfile);
    }

    /**
     * Add activeUser
     *
     * @param WobbleCode\UserBundle\Document\User $activeUser
     */
    public function addActiveUser(\WobbleCode\UserBundle\Document\User $activeUser)
    {
        $this->activeUsers[] = $activeUser;
    }

    /**
     * Remove activeUser
     *
     * @param WobbleCode\UserBundle\Document\User $activeUser
     */
    public function removeActiveUser(\WobbleCode\UserBundle\Document\User $activeUser)
    {
        $this->activeUsers->removeElement($activeUser);
    }
}
