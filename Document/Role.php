<?php

namespace WobbleCode\UserBundle\Document;

use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as Serializer;

/**
 * @MongoDB\Document
 * @MongoDB\UniqueIndex(keys={"user.$id"="asc", "organization.$id"="asc"})
 * @Serializer\ExclusionPolicy("all")
 */
class Role
{
    /**
     * @MongoDB\Id(strategy="auto")
     * @Serializer\Groups({"ui", "api"})
     * @Serializer\Expose
     */
    protected $id;

    /**
     * @MongoDB\Hash
     * @Assert\Type(type="array")
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
     * @MongoDB\Field(type="date")
     */
    private $deletedAt;

    /**
     * @MongoDB\ReferenceOne(targetDocument="User", inversedBy="roles")
     */
    protected $user;

    /**
     * @MongoDB\ReferenceOne(targetDocument="WobbleCode\UserBundle\Model\OrganizationInterface", inversedBy="roles")
     */
    protected $organization;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set user
     *
     * @param \WobbleCode\UserBundle\Document\User $user
     * @return Roles
     */
    public function setUser(\WobbleCode\UserBundle\Document\User $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \WobbleCode\UserBundle\Document\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set organization
     *
     * @param \WobbleCode\UserBundle\Model\OrganizationInterface $organization
     * @return Roles
     */
    public function setOrganization(\WobbleCode\UserBundle\Model\OrganizationInterface $organization)
    {
        $this->organization = $organization;

        return $this;
    }

    /**
     * Get organization
     *
     * @return \WobbleCode\UserBundle\Model\OrganizationInterface
     */
    public function getOrganization()
    {
        return $this->organization;
    }

    /**
     * Set roles
     *
     * @param array $roles
     * @return Roles
     */
    public function setRoles($roles)
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * Get roles
     *
     * @return array
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return Roles
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     * @return Roles
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Set deletedAt
     *
     * @param \DateTime $deletedAt
     * @return Roles
     */
    public function setDeletedAt($deletedAt)
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }

    /**
     * Get deletedAt
     *
     * @return \DateTime
     */
    public function getDeletedAt()
    {
        return $this->deletedAt;
    }

    /**
     * Set createdBy
     *
     * @param \WobbleCode\UserBundle\Document\User $createdBy
     * @return Roles
     */
    public function setCreatedBy(\WobbleCode\UserBundle\Document\User $createdBy = null)
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    /**
     * Get createdBy
     *
     * @return \WobbleCode\UserBundle\Document\User
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    /**
     * Set updatedBy
     *
     * @param \WobbleCode\UserBundle\Document\User $updatedBy
     * @return Roles
     */
    public function setUpdatedBy(\WobbleCode\UserBundle\Document\User $updatedBy = null)
    {
        $this->updatedBy = $updatedBy;

        return $this;
    }

    /**
     * Get updatedBy
     *
     * @return \WobbleCode\UserBundle\Document\User
     */
    public function getUpdatedBy()
    {
        return $this->updatedBy;
    }
}
