<?php

namespace Autowp\ExternalLoginService;

use InvalidArgumentException;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Token\AccessToken;

abstract class LeagueOAuth2 extends AbstractService
{
    /**
     * @var AbstractProvider
     */
    protected $provider;

    /**
     * @var string
     */
    protected $accessToken;

    /**
     * @return AbstractProvider
     */
    abstract protected function createProvider();

    /**
     * @return AbstractProvider
     */
    protected function getProvider()
    {
        if (! $this->provider) {
            $this->provider = $this->createProvider();
        }

        return $this->provider;
    }

    public function setProvider(AbstractProvider $provider)
    {
        $this->provider = $provider;
    }

    /**
     * @return string
     */
    abstract protected function getAuthorizationUrl();

    /**
     * @return string
     */
    abstract protected function getFriendsAuthorizationUrl();

    public function getState()
    {
        return $this->getProvider()->getState();
    }

    public function getLoginUrl()
    {
        return $this->getAuthorizationUrl();
    }

    public function getFriendsUrl()
    {
        return $this->getFriendsAuthorizationUrl();
    }

    public function callback(array $params)
    {
        if (! isset($params['code'])) {
            throw new InvalidArgumentException("`code` not provided");
        }

        $this->accessToken = $this->getProvider()->getAccessToken('authorization_code', [
            'code' => $params['code']
        ]);

        return $this->accessToken;
    }

    public function setAccessToken($accessToken)
    {
        $this->accessToken = new AccessToken([
            'access_token' => $accessToken
        ]);

        return $this;
    }
}
