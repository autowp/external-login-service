<?php

declare(strict_types=1);

namespace Autowp\ExternalLoginService;

use League\OAuth2\Client\Provider\LinkedIn as LinkedInProvider;

use function trim;

class Linkedin extends AbstractLeagueOAuth2
{
    protected function createProvider(): LinkedInProvider
    {
        return new LinkedInProvider([
            'clientId'     => $this->options['clientId'],
            'clientSecret' => $this->options['clientSecret'],
            'redirectUri'  => $this->options['redirectUri'],
        ]);
    }

    protected function getAuthorizationUrl(): string
    {
        return $this->getProvider()->getAuthorizationUrl();
    }

    protected function getFriendsAuthorizationUrl(): string
    {
        return '';
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @throws ExternalLoginServiceException
     */
    public function getData(array $options): Result
    {
        $provider = $this->getProvider();

        $ownerDetails = $provider->getResourceOwner($this->accessToken);

        return new Result([
            'externalId' => $ownerDetails->getId(),
            'name'       => trim($ownerDetails->getFirstname() . ' ' . $ownerDetails->getLastname()),
            'profileUrl' => $ownerDetails->getUrl(),
            'photoUrl'   => $ownerDetails->getImageurl(),
        ]);
    }

    public function getFriendsUrl(): string
    {
        return '';
    }

    public function getFriends(): array
    {
        return [];
    }
}
