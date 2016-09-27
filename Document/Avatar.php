<?php

namespace WobbleCode\UserBundle\Document;

use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use JMS\Serializer\Annotation as Serializer;

/**
 * @MongoDB\EmbeddedDocument
 * @Serializer\ExclusionPolicy("all")
 */
class Avatar
{
    /**
     * Define possible types social|url|gravatar|initials|silhouette
     *
     * @MongoDB\Field(type="string")
     * @Serializer\Expose
     */
    protected $type = 'silhouette';

    /**
     * Gravatar email
     *
     * Gravatar schema Example
     *
     *    {
     *        "gravatar": "johndoe@wobblecode.com"
     *    }
     *
     * @MongoDB\Hash
     * @Serializer\Expose
     */
    protected $gravatarData;

    /**
     * Initials schema Example
     *
     *    {
     *        "initials": "LH",
     *        "initialsBgColor": "DDCCEE",
     *        "initialsTextColor": "FFFFFF"
     *    }
     *
     * @MongoDB\Hash
     * @Serializer\Expose
     */
    protected $initialsData;

    /*
     * Url schema Example
     *
     *    {
     *        "default": "//lh3.googleusercontent.com/-yLUf2/photo.jpg",
     *        "urlHighRes": null,
     *        "urlLowRes": null
     *    }
     *
     * @MongoDB\Hash
     * @Serializer\Expose
     */
    protected $urlData;

    /*
     * Url schema Example
     *
     *    {
     *        "default": "//lh3.googleusercontent.com/-yLUf2/photo.jpg",
     *        "urlHighRes": null,
     *        "urlLowRes": null
     *    }
     *
     * @MongoDB\Hash
     * @Serializer\Expose
     */
    protected $socialData;

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
     * Set gravatarData
     *
     * @param string $gravatarData
     * @return self
     */
    public function setGravatarData($gravatarData)
    {
        $this->gravatarData = $gravatarData;

        return $this;
    }

    /**
     * Get gravatarData
     *
     * @return string $gravatarData
     */
    public function getGravatarData()
    {
        return $this->gravatarData;
    }

    /**
     * Set initialsData
     *
     * @param hash $initialsData
     * @return self
     */
    public function setInitialsData($initialsData)
    {
        $this->initialsData = $initialsData;

        return $this;
    }

    /**
     * Get initialsData
     *
     * @return hash $initialsData
     */
    public function getInitialsData()
    {
        return $this->initialsData;
    }

    /**
     * Set $urlData
     *
     * @param hash $urlData
     * @return self
     */
    public function setUrlData($urlData)
    {
        $this->urlData = $urlData;

        return $this;
    }

    /**
     * Get $urlData
     *
     * @return hash $urlData
     */
    public function getUrlData()
    {
        return $this->urlData;
    }

    /**
     * Set initialsData
     *
     * @param hash $socialData
     * @return self
     */
    public function setSocialData($socialData)
    {
        $this->socialData = $socialData;

        return $this;
    }

    /**
     * Get socialData
     *
     * @return hash $socialData
     */
    public function getSocialData()
    {
        return $this->socialData;
    }
}
