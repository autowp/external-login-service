<?php

namespace Autowp\ExternalLoginService;

use Autowp\ExternalLoginService\Exception;
use Autowp\ExternalLoginService\LeagueOAuth2;
use Autowp\ExternalLoginService\Result;

use League\OAuth2\Client\Provider\Github as GithubProvider;

class Github extends LeagueOAuth2
{
    protected function createProvider()
    {
        return new GithubProvider([
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
     */
    public function getData(array $options)
    {
        $provider = $this->getProvider();

        $ownerDetails = $provider->getResourceOwner($this->accessToken);
        $data = $ownerDetails->toArray();

        return new Result([
            'externalId' => $data['id'],
            'name'       => $data['name'],
            'profileUrl' => $data['html_url'],
            'photoUrl'   => $data['avatar_url']
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
