<?php

namespace Autowp\ExternalLoginService;

use Autowp\ExternalLoginService\Exception;

use DateTime;
use Zend\Validator\Uri;
use Zend\Validator\EmailAddress;

class Result
{
    /**
     * @var string
     */
    private $externalId = null;

    /**
     * @var string
     */
    private $name = null;

    /**
     * @var string
     */
    private $profileUrl = null;

    /**
     * @var string
     */
    private $photoUrl = null;

    /**
     * @var DateTime
     */
    private $birthday = null;

    /**
     * @var string
     */
    private $email = null;

    /**
     * @var string
     */
    private $gender = null;

    /**
     * @var string
     */
    private $location = null;

    /**
     * @var string
     */
    private $language = null;

    /**
     * @param array $options
     * @throws Exception
     */
    public function __construct(array $options = array())
    {
        $this->setOptions($options);
    }

    /**
     * @param array $options
     * @return Result
     * @throws Exception
     */
    public function setOptions(array $options)
    {
        foreach ($options as $key => $value) {
            $method = 'set' . ucfirst($key);

            if (method_exists($this, $method)) {
                $this->$method($value);
            } else {
                $message = "Unexpected option '$key'";
                throw new Exception($message);
            }
        }

        return $this;
    }

    /**
     * @param string $externalId
     * @return Result
     */
    public function setExternalId($externalId)
    {
        $this->externalId = (string)$externalId;

        return $this;
    }

    /**
     * @return string
     */
    public function getExternalId()
    {
        return $this->externalId;
    }

    /**
     * @param string $name
     * @return Result
     */
    public function setName($name)
    {
        $this->name = (string)$name;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $profileUrl
     * @return Result
     */
    public function setProfileUrl($profileUrl)
    {
        $profileUrl = (string)$profileUrl;

        if ($profileUrl) {
            $this->profileUrl = $profileUrl;
            /*if (Zend_Uri::check($profileUrl)) {
                $this->profileUrl = $profileUrl;
            } else {
                $message = "Invalid profile url `$profileUrl`";
                throw new Exception($message);
            }*/
        } else {
            $this->profileUrl = null;
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getProfileUrl()
    {
        return $this->profileUrl;
    }

    /**
     * @param string $photoUrl
     * @return Result
     */
    public function setPhotoUrl($photoUrl)
    {
        $photoUrl = (string)$photoUrl;

        if ($photoUrl) {

            $validator = new Uri([
                'allowRelative' => false
            ]);

            if ($validator->isValid($photoUrl)) {
                $this->photoUrl = $photoUrl;
            } else {
                throw new InvalidUriException("Invalid profile url `$photoUrl`");
            }
        } else {
            $this->photoUrl = null;
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getPhotoUrl()
    {
        return $this->photoUrl;
    }

    /**
     * @param string $email
     * @return Result
     */
    public function setEmail($email)
    {
        $email = (string)$email;

        if ($email) {

            $validator = new EmailAddress();

            if ($validator->isValid($email)) {
                $this->email = $email;
            } else {
                throw new InvalidEmailAddressException("Invalid e-mail `$email`");
            }
        } else {
            $this->email = null;
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param DateTime $birthday
     * @return Result
     */
    public function setBirthday(DateTime $birthday = null)
    {
        $this->birthday = $birthday;

        return $this;
    }

    /**
     * @return DateTime
     */
    public function getBirthday()
    {
        return $this->birthday;
    }

    /**
     * @param string $gender
     * @return Result
     */
    public function setGender($gender)
    {
        $this->gender = $gender;

        return $this;
    }

    /**
     * @return string
     */
    public function getGender()
    {
        return $this->gender;
    }

    /**
     * @param string $location
     * @return Result
     */
    public function setLocation($location)
    {
        $this->location = $location;

        return $this;
    }
    
    /**
     * @return string $location
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * @param string $language
     * @return Result
     */
    public function setLanguage($language)
    {
        $this->language = $language;

        return $this;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'externalId' => $this->externalId,
            'name'       => $this->name,
            'profileUrl' => $this->profileUrl,
            'photoUrl'   => $this->photoUrl,
            'email'      => $this->email,
            'birthday'   => $this->birthday,
            'gender'     => $this->gender,
            'location'   => $this->location,
            'language'   => $this->language
        ];
    }

    public static function fromArray(array $options)
    {
        return new self($options);
    }
}