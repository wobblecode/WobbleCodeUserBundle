<?php

namespace WobbleCode\UserBundle\Document;

use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Doctrine\Bundle\MongoDBBundle\Validator\Constraints\Unique;
use JMS\Serializer\Annotation as Serializer;

/**
 * @MongoDB\Document(
 *     repositoryClass="WobbleCode\UserBundle\Document\InvitationRepository"
 * )
 * @Serializer\ExclusionPolicy("all")
 * @Assert\Callback(methods={"isDifferentEmail"})
 * @Unique(
 *     repositoryMethod="findUniqueBy",
 *     fields={"email", "organization", "status"},
 *     errorPath="email",
 *     message="wc_user.invitation.already_pending"
 * )
 */
class Invitation
{
    /**
     * @MongoDB\Id(strategy="auto")
     * @Serializer\Groups({"ui", "api"})
     * @Serializer\Expose
     */
    protected $id;

    /**
     * pending, accepted, rejected, expired
     *
     * @MongoDB\Field(type="string")
     * @Serializer\Groups({"ui", "api"})
     * @Serializer\Expose
     */
    protected $status = 'pending';

    /**
     * @MongoDB\Field(type="string")
     * @Assert\Length(max="40")
     * @Assert\Regex(
     *     pattern="/[0-9a-f]{40}/",
     *     match=true,
     *     message="This hash is not valid"
     * )
     */
    protected $hash;

    /**
     * @MongoDB\Field(type="string")
     * @Assert\NotBlank()
     * @Assert\Email()
     * @Assert\Length(max="255")
     * @Serializer\Groups({"ui", "api"})
     * @Serializer\Expose
     */
    protected $email;

    /**
     * @MongoDB\Hash
     * @Assert\NotNull()
     * @Assert\Count(min="1")
     * @Serializer\Groups({"ui", "api"})
     * @Serializer\Expose
     */
    protected $roles;

    /**
     * @Gedmo\Timestampable(on="create")
     * @MongoDB\Field(type="date")
     */
    protected $createdAt;

    /**
     * @Gedmo\Timestampable(on="update")
     * @MongoDB\Field(type="date")
     */
    protected $updatedAt;

    /**
     * @Gedmo\Blameable(on="create")
     * @MongoDB\ReferenceOne(targetDocument="User")
     */
    protected $createdBy;

    /**
     * @Gedmo\Blameable(on="update")
     * @MongoDB\ReferenceOne(targetDocument="User")
     */
    protected $updatedBy;

    /**
     * @MongoDB\ReferenceOne(targetDocument="WobbleCode\UserBundle\Model\OrganizationInterface", inversedBy="invitations")
     */
    protected $organization;

    /**
     * @MongoDB\ReferenceOne(targetDocument="User", inversedBy="sentInvitations")
     */
    protected $from;

    /**
    * @MongoDB\Field(type="string")
     */
    protected $locale = 'en';

    /**
     * @MongoDB\ReferenceOne(targetDocument="User", inversedBy="acceptedInvitations")
     */
    protected $to;

    /**
     * Callback to check the if is a different email from it self
     */
    public function isDifferentEmail(ExecutionContextInterface $context)
    {
        if ($this->getEmail() == $this->getFrom()->getEmail()) {
            $context->buildViolation('You can\'t invite yourself!')
                    ->atPath('email')
                    ->addViolation();
        }
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
     * Set status
     *
     * @param string $status
     * @return self
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * Get status
     *
     * @return string $status
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set hash
     *
     * @param string $hash
     * @return self
     */
    public function setHash($hash)
    {
        $this->hash = $hash;
        return $this;
    }

    /**
     * Get hash
     *
     * @return string $hash
     */
    public function getHash()
    {
        return $this->hash;
    }

    /**
     * Set email
     *
     * @param string $email
     * @return self
     */
    public function setEmail($email)
    {
        $this->email = $email;
        return $this;
    }

    /**
     * Get email
     *
     * @return string $email
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set roles
     *
     * @param hash $roles
     * @return self
     */
    public function setRoles($roles)
    {
        $this->roles = $roles;
        return $this;
    }

    /**
     * Get roles
     *
     * @return hash $roles
     */
    public function getRoles()
    {
        return $this->roles;
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
     * Set organization
     *
     * @param WobbleCode\UserBundle\Model\OrganizationInterface $organization
     * @return self
     */
    public function setOrganization(\WobbleCode\UserBundle\Model\OrganizationInterface $organization)
    {
        $this->organization = $organization;
        return $this;
    }

    /**
     * Get organization
     *
     * @return WobbleCode\UserBundle\Model\OrganizationInterface $organization
     */
    public function getOrganization()
    {
        return $this->organization;
    }

    /**
     * Set from
     *
     * @param WobbleCode\UserBundle\Document\User $from
     * @return self
     */
    public function setFrom(\WobbleCode\UserBundle\Document\User $from)
    {
        $this->from = $from;
        return $this;
    }

    /**
     * Get from
     *
     * @return WobbleCode\UserBundle\Document\User $from
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * Set to
     *
     * @param WobbleCode\UserBundle\Document\User $to
     * @return self
     */
    public function setTo(\WobbleCode\UserBundle\Document\User $to)
    {
        $this->to = $to;
        return $this;
    }

    /**
     * Get to
     *
     * @return WobbleCode\UserBundle\Document\User $to
     */
    public function getTo()
    {
        return $this->to;
    }

    /**
     * Set locale
     *
     * @param string $locale
     * @return self
     */
    public function setLocale($locale)
    {
        $this->locale = strtolower($locale);
        return $this;
    }

    /**
     * Get locale
     *
     * @return string $locale
     */
    public function getLocale()
    {
        return $this->locale;
    }
}
