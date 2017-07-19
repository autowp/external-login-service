<?php

namespace Autowp\ExternalLoginService;

use Autowp\ExternalLoginService\Exception;
use Autowp\ExternalLoginService\LeagueOAuth2;
use Autowp\ExternalLoginService\Result;

use League\OAuth2\Client\Provider\LinkedIn as LinkedInProvider;

class Linkedin extends LeagueOAuth2
{
    protected function createProvider()
    {
        return new LinkedInProvider([
            'clientId'     => $this->options['clientId'],
            'clientSecret' => $this->options['clientSecret'],
            'redirectUri'  => $this->options['redirect_uri']
        ]);
    }

    protected function getAuthorizationUrl()
    {
        return $this->getProvider()->getAuthorizationUrl();
    }

    protected function getFriendsAuthorizationUrl()
    {
        throw new Exception("Not implemented");
    }

    /**
     * @return Result
     * 
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getData(array $options)
    {
        $provider = $this->getProvider();

        $ownerDetails = $provider->getResourceOwner($this->accessToken);

        return new Result([
            'externalId' => $ownerDetails->getId(),
            'name'       => trim($ownerDetails->getFirstname() . ' ' . $ownerDetails->getLastname()),
            'profileUrl' => $ownerDetails->getUrl(),
            'photoUrl'   => $ownerDetails->getImageurl()
        ]);
    }

    public function getFriendsUrl()
    {
        throw new Exception("Not implemented");
    }

    public function getFriends()
    {
        throw new Exception("Not implemented");
    }
}
