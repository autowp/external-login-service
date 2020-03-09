<?php

declare(strict_types=1);

namespace Autowp\ExternalLoginService;

use DateTime;
use Laminas\Validator\EmailAddress;
use Laminas\Validator\Uri;

use function method_exists;
use function ucfirst;

class Result
{
    /** @var string */
    private string $externalId;

    /** @var string */
    private string $name;

    /** @var string */
    private ?string $profileUrl;

    /** @var string */
    private ?string $photoUrl;

    /** @var DateTime */
    private ?DateTime $birthday;

    /** @var string */
    private ?string $email;

    /** @var string */
    private ?string $gender;

    /** @var string */
    private ?string $location;

    /** @var string */
    private ?string $language;

    /**
     * @throws ExternalLoginServiceException
     */
    public function __construct(array $options = [])
    {
        $this->setOptions($options);
    }

    /**
     * @throws ExternalLoginServiceException
     */
    public function setOptions(array $options): self
    {
        foreach ($options as $key => $value) {
            $method = 'set' . ucfirst($key);

            if (! method_exists($this, $method)) {
                throw new ExternalLoginServiceException("Unexpected option '$key'");
            }
            $this->$method($value);
        }

        return $this;
    }

    public function setExternalId(string $externalId): self
    {
        $this->externalId = $externalId;

        return $this;
    }

    public function getExternalId(): string
    {
        return $this->externalId;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setProfileUrl(?string $profileUrl): self
    {
        $this->profileUrl = null;

        if ($profileUrl) {
            $this->profileUrl = $profileUrl;
            /*if (Zend_Uri::check($profileUrl)) {
                $this->profileUrl = $profileUrl;
            } else {
                $message = "Invalid profile url `$profileUrl`";
                throw new Exception($message);
            }*/
        }

        return $this;
    }

    public function getProfileUrl(): ?string
    {
        return $this->profileUrl;
    }

    /**
     * @throws InvalidUriException
     */
    public function setPhotoUrl(?string $photoUrl): self
    {
        $this->photoUrl = null;

        if ($photoUrl) {
            $validator = new Uri([
                'allowRelative' => false,
            ]);

            if (! $validator->isValid($photoUrl)) {
                throw new InvalidUriException("Invalid profile url `$photoUrl`");
            }

            $this->photoUrl = $photoUrl;
        }

        return $this;
    }

    public function getPhotoUrl(): ?string
    {
        return $this->photoUrl;
    }

    /**
     * @throws InvalidEmailAddressException
     */
    public function setEmail(?string $email): self
    {
        $this->email = null;

        if ($email) {
            $validator = new EmailAddress();

            if (! $validator->isValid($email)) {
                throw new InvalidEmailAddressException("Invalid e-mail `$email`");
            }

            $this->email = $email;
        }

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setBirthday(?DateTime $birthday = null): self
    {
        $this->birthday = $birthday;

        return $this;
    }

    public function getBirthday(): ?DateTime
    {
        return $this->birthday;
    }

    public function setGender(?string $gender): self
    {
        $this->gender = $gender;

        return $this;
    }

    public function getGender(): ?string
    {
        return $this->gender;
    }

    public function setLocation(?string $location): self
    {
        $this->location = $location;

        return $this;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLanguage(?string $language): self
    {
        $this->language = $language;

        return $this;
    }

    public function toArray(): array
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
            'language'   => $this->language,
        ];
    }

    /**
     * @throws ExternalLoginServiceException
     */
    public static function fromArray(array $options): self
    {
        return new self($options);
    }
}
