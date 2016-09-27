<?php

namespace WobbleCode\UserBundle\Document;

use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use JMS\Serializer\Annotation as Serializer;
use WobbleCode\UserBundle\Document\Avatar;

/**
 * @MongoDB\EmbeddedDocument
 * @Serializer\ExclusionPolicy("all")
 */
class Contact
{
    /**
     * Type of contact: person|organization
     *
     * @MongoDB\Field(type="string")
     * @Serializer\Expose
     * @Serializer\Groups({"ui", "api"})
     * @Assert\Length(max="16")
     */
    protected $type;

    /**
     * @MongoDB\Field(type="string")
     * @Serializer\Expose
     * @Serializer\Groups({"ui", "api"})
     * @Assert\Length(max="5")
     */
    protected $locale;

    /**
     * @MongoDB\Field(type="string")
     * @Assert\Length(max="40")
     * @Serializer\Expose
     * @Serializer\Groups({"ui", "api"})
     */
    protected $timezone;

    /**
     * @MongoDB\Field(type="string")
     * @Assert\Length(max="2")
     * @Serializer\Expose
     * @Serializer\Groups({"ui", "api"})
     */
    protected $selectedLanguage;

    /**
     * @MongoDB\Field(type="string")
     * @Assert\Length(max="2")
     * @Serializer\Expose
     * @Serializer\Groups({"ui", "api"})
     */
    protected $preferredLanguage;

    /**
     * Contact Role Eg: owner|admin|billing|tech|support|commercial
     *
     * @MongoDB\Field(type="string")
     * @Assert\Length(max="24")
     * @Serializer\Expose
     * @Serializer\Groups({"ui", "api"})
     */
    protected $role;

    /**
     * @MongoDB\Field(type="string")
     * @Assert\Length(max="2")
     * @Serializer\Expose
     * @Serializer\Groups({"ui", "api"})
     */
    protected $gender;

    /**
     * @MongoDB\Field(type="string")
     * @Assert\Length(max="48")
     * @Serializer\Expose
     * @Serializer\Groups({"ui", "api"})
     */
    protected $name;

    /**
     * @MongoDB\Field(type="string")
     * @Assert\Length(max="32")
     * @Serializer\Expose
     * @Serializer\Groups({"ui", "api"})
     */
    protected $lastNames;

    /**
     * @MongoDB\Field(type="string")
     * @Assert\Length(max="2")
     * @Serializer\Expose
     * @Serializer\Groups({"ui", "api"})
     */
    protected $documentCountry;

    /**
     * @MongoDB\Field(type="string")
     * @Assert\Length(max="30")
     * @Serializer\Expose
     * @Serializer\Groups({"ui", "api"})
     */
    protected $documentType;

    /**
     * @MongoDB\Field(type="string")
     * @Assert\Length(max="30", groups={"invoice"})
     * @Assert\NotBlank(groups={"invoice"})
     * @Serializer\Expose
     * @Serializer\Groups({"ui", "api"})
     */
    protected $documentId;

    /**
     * @MongoDB\Field(type="string")
     * @Assert\Country(groups={"invoice"})
     * @Assert\NotBlank(groups={"invoice"})
     * @Serializer\Expose
     * @Serializer\Groups({"ui", "api"})
     */
    protected $country;

    /**
     * @MongoDB\Field(type="string")
     * @Assert\Length(max="48", groups={"invoice"})
     * @Assert\NotBlank(groups={"invoice"})
     * @Serializer\Expose
     * @Serializer\Groups({"ui", "api"})
     */
    protected $province;

    /**
     * @MongoDB\Field(type="string")
     * @Assert\Length(max="48", groups={"invoice"})
     * @Assert\NotBlank(groups={"invoice"})
     * @Serializer\Expose
     * @Serializer\Groups({"ui", "api"})
     */
    protected $city;

    /**
     * @MongoDB\Field(type="string")
     * @Assert\Length(max="64", groups={"invoice"})
     * @Serializer\Expose
     * @Serializer\Groups({"ui", "api"})
     */
    protected $region;

    /**
     * @MongoDB\Field(type="string")
     * @Assert\Length(max="128", groups={"invoice"})
     * @Assert\NotBlank(groups={"invoice"})
     * @Serializer\Expose
     * @Serializer\Groups({"ui", "api"})
     */
    protected $address;

    /**
     * @MongoDB\Field(type="string")
     * @Assert\Length(max="12", groups={"invoice"})
     * @Assert\NotBlank(groups={"invoice"})
     * @Serializer\Expose
     * @Serializer\Groups({"ui", "api"})
     */
    protected $zip;

    /**
     * @MongoDB\Field(type="string")
     * @Assert\Length(max="128")
     * @Assert\Email()
     * @Serializer\Expose
     * @Serializer\Groups({"ui", "api"})
     */
    protected $email;

    /**
     * @MongoDB\Field(type="string")
     * @Assert\Length(max="20", groups={"invoice"})
     * @Assert\NotBlank(groups={"invoice"})
     * @Serializer\Expose
     * @Serializer\Groups({"ui", "api"})
     */
    protected $phone;

    /**
     * @MongoDB\Field(type="string")
     * @Assert\Length(max="20")
     * @Serializer\Expose
     * @Serializer\Groups({"ui", "api"})
     */
    protected $cellPhone;

    /**
     * @MongoDB\Field(type="string")
     * @Assert\Length(max="4")
     * @Serializer\Expose
     * @Serializer\Groups({"ui", "api"})
     */
    protected $activationCode;

    /**
     * @MongoDB\Field(type="integer")
     * @Serializer\Expose
     * @Serializer\Groups({"ui", "api"})
     */
    protected $activationTries = 0;

    /**
     * @MongoDB\Field(type="string")
     * @Assert\Length(max="200")
     * @Serializer\Expose
     * @Serializer\Groups({"ui", "api"})
     */
    protected $web;

    /**
     * @todo Add validation
     *
     * @MongoDB\Hash
     * @Serializer\Expose
     * @Serializer\Groups({"ui", "api"})
     *
     * Schema example:
     *
     *     {
     *          "web": {"link": "https://www.wobblecode.com"},
     *          "facebook": {"link": "https://facebook.com/wobblecode"},
     *          "linkedIn": {"link": "https://linkedin.com/wobblecode"},
     *          "skype": {"username": "wobblecode"}
     *     }
     */
    protected $serviceProfiles = [];

    /**
     * @MongoDB\EmbedOne(targetDocument="Avatar")
     * @Serializer\Expose
     * @Serializer\Groups({"ui", "api-dr"})
     */
    protected $avatar;

    /**
     * Constructor
     *
     * @return self
     */
    public function __construct()
    {
        $this->avatar = new Avatar;

        return $this;
    }

    /**
     * __toString magic method
     *
     * @return string With full name, name + Last names
     */
    public function __toString()
    {
        return $this->name.' '.$this->lastNames;
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
     * Set role
     *
     * @param string $role
     * @return self
     */
    public function setRole($role)
    {
        $this->role = $role;
        return $this;
    }

    /**
     * Get role
     *
     * @return string $role
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * Set gender
     *
     * @param string $gender
     * @return self
     */
    public function setGender($gender)
    {
        $this->gender = $gender;
        return $this;
    }

    /**
     * Get gender
     *
     * @return string $gender
     */
    public function getGender()
    {
        return $this->gender;
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
     * Set lastNames
     *
     * @param string $lastNames
     * @return self
     */
    public function setLastNames($lastNames)
    {
        $this->lastNames = $lastNames;
        return $this;
    }

    /**
     * Get lastNames
     *
     * @return string $lastNames
     */
    public function getLastNames()
    {
        return $this->lastNames;
    }

    /**
     * Set documentCountry
     *
     * @param string $documentCountry
     * @return self
     */
    public function setDocumentCountry($documentCountry)
    {
        $this->documentCountry = $documentCountry;
        return $this;
    }

    /**
     * Get documentCountry
     *
     * @return string $documentCountry
     */
    public function getDocumentCountry()
    {
        return $this->documentCountry;
    }

    /**
     * Set documentType
     *
     * @param string $documentType
     * @return self
     */
    public function setDocumentType($documentType)
    {
        $this->documentType = $documentType;
        return $this;
    }

    /**
     * Get documentType
     *
     * @return string $documentType
     */
    public function getDocumentType()
    {
        return $this->documentType;
    }

    /**
     * Set documentId
     *
     * @param string $documentId
     * @return self
     */
    public function setDocumentId($documentId)
    {
        $this->documentId = $documentId;
        return $this;
    }

    /**
     * Get documentId
     *
     * @return string $documentId
     */
    public function getDocumentId()
    {
        return $this->documentId;
    }

    /**
     * Set country
     *
     * @param string $country
     * @return self
     */
    public function setCountry($country)
    {
        $this->country = $country;
        return $this;
    }

    /**
     * Get country
     *
     * @return string $country
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * Set province
     *
     * @param string $province
     * @return self
     */
    public function setProvince($province)
    {
        $this->province = $province;
        return $this;
    }

    /**
     * Get province
     *
     * @return string $province
     */
    public function getProvince()
    {
        return $this->province;
    }

    /**
     * Set city
     *
     * @param string $city
     * @return self
     */
    public function setCity($city)
    {
        $this->city = $city;
        return $this;
    }

    /**
     * Get city
     *
     * @return string $city
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Set address
     *
     * @param string $address
     * @return self
     */
    public function setAddress($address)
    {
        $this->address = $address;
        return $this;
    }

    /**
     * Get address
     *
     * @return string $address
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Set zip
     *
     * @param string $zip
     * @return self
     */
    public function setZip($zip)
    {
        $this->zip = $zip;
        return $this;
    }

    /**
     * Get zip
     *
     * @return string $zip
     */
    public function getZip()
    {
        return $this->zip;
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
     * Set web
     *
     * @param string $email
     * @return self
     */
    public function setWeb($web)
    {
        $this->web = $web;
        return $this;
    }

    /**
     * Get web
     *
     * @return string $email
     */
    public function getWeb()
    {
        return $this->web;
    }

    /**
     * Set phone
     *
     * @param string $phone
     * @return self
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;
        return $this;
    }

    /**
     * Get phone
     *
     * @return string $phone
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * Set cellPhone
     *
     * @param string $cellPhone
     * @return self
     */
    public function setCellPhone($cellPhone)
    {
        $this->cellPhone = $cellPhone;
        return $this;
    }

    /**
     * Get cellPhone
     *
     * @return string $cellPhone
     */
    public function getCellPhone()
    {
        return $this->cellPhone;
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

    /**
     * Set timezone
     *
     * @param string $timezone
     * @return self
     */
    public function setTimezone($timezone)
    {
        $this->timezone = $timezone;
        return $this;
    }

    /**
     * Get timezone
     *
     * @return string $timezone
     */
    public function getTimezone()
    {
        return $this->timezone;
    }

    /**
     * Set selectedLanguage
     *
     * @param string $selectedLanguage
     * @return self
     */
    public function setSelectedLanguage($selectedLanguage)
    {
        $this->selectedLanguage = $selectedLanguage;
        return $this;
    }

    /**
     * Get selectedLanguage
     *
     * @return string $selectedLanguage
     */
    public function getSelectedLanguage()
    {
        return $this->selectedLanguage;
    }

    /**
     * Set preferredLanguage
     *
     * @param string $preferredLanguage
     * @return self
     */
    public function setPreferredLanguage($preferredLanguage)
    {
        $this->preferredLanguage = $preferredLanguage;
        return $this;
    }

    /**
     * Get preferredLanguage
     *
     * @return string $preferredLanguage
     */
    public function getPreferredLanguage()
    {
        return $this->preferredLanguage;
    }

    /**
     * Set serviceProfiles
     *
     * @param string $serviceProfiles
     *
     * @return self
     */
    public function setServiceProfiles($serviceProfiles)
    {
        $this->serviceProfiles = $serviceProfiles;

        return $this;
    }

    /**
     * Get serviceProfiles
     *
     * @return string $skype
     */
    public function getServiceProfiles()
    {
        return $this->serviceProfiles;
    }

    /**
     * Set attribute
     *
     * @param string $key
     * @param mixed $attributes
     *
     * @return self
     */
    public function setServiceProfile($service, $data)
    {
        $this->serviceProfiles[$service] = $data;

        return $this;
    }

    /**
     * Get service profile data
     *
     * @param string $service
     *
     * @return array|null
     */
    public function getServiceProfile($service)
    {
        if (isset($this->serviceProfiles[$service])) {
            return $this->serviceProfiles[$service];
        }

        return null;
    }

    /**
     * Set avatar
     *
     * @param WobbleCode\UserBundle\Document\Avatar $avatar
     *
     * @return self
     */
    public function setAvatar(\WobbleCode\UserBundle\Document\Avatar $avatar)
    {
        $this->avatar = $avatar;

        return $this;
    }

    /**
     * Get avatar
     *
     * @return WobbleCode\UserBundle\Document\Avatar $avatar
     */
    public function getAvatar()
    {
        return $this->avatar;
    }

    /**
     * Set region
     *
     * @param string $region
     * @return self
     */
    public function setRegion($region)
    {
        $this->region = $region;
        return $this;
    }

    /**
     * Get region
     *
     * @return string $region
     */
    public function getRegion()
    {
        return $this->region;
    }

    /**
     * Set activationCode
     *
     * @param string $activationCode
     * @return self
     */
    public function setActivationCode($activationCode)
    {
        $this->activationCode = $activationCode;
        return $this;
    }

    /**
     * Get activationCode
     *
     * @return string $activationCode
     */
    public function getActivationCode()
    {
        return $this->activationCode;
    }

    /**
     * Set activationTries
     *
     * @param integer $activationTries
     * @return self
     */
    public function setActivationTries($activationTries)
    {
        $this->activationTries = $activationTries;
        return $this;
    }

    /**
     * Get activationTries
     *
     * @return integer $activationTries
     */
    public function getActivationTries()
    {
        return $this->activationTries;
    }
}
