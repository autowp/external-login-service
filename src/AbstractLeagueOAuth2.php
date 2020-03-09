<?php

declare(strict_types=1);

namespace Autowp\ExternalLoginService;

use InvalidArgumentException;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Token\AccessTokenInterface;

abstract class AbstractLeagueOAuth2 extends AbstractService
{
    protected ?AbstractProvider $provider;

    protected AccessTokenInterface $accessToken;

    abstract protected function createProvider(): AbstractProvider;

    protected function getProvider(): AbstractProvider
    {
        if (! isset($this->provider)) {
            $this->provider = $this->createProvider();
        }

        return $this->provider;
    }

    public function setProvider(AbstractProvider $provider): void
    {
        $this->provider = $provider;
    }

    abstract protected function getAuthorizationUrl(): string;

    abstract protected function getFriendsAuthorizationUrl(): string;

    public function getState(): string
    {
        return $this->getProvider()->getState();
    }

    public function getLoginUrl(): string
    {
        return $this->getAuthorizationUrl();
    }

    public function getFriendsUrl(): string
    {
        return $this->getFriendsAuthorizationUrl();
    }

    /**
     * @throws IdentityProviderException
     */
    public function callback(array $params): AccessTokenInterface
    {
        if (! isset($params['code'])) {
            throw new InvalidArgumentException("`code` not provided");
        }

        $this->accessToken = $this->getProvider()->getAccessToken('authorization_code', [
            'code' => $params['code'],
        ]);

        return $this->accessToken;
    }

    public function setAccessToken(string $accessToken): self
    {
        $this->accessToken = new AccessToken([
            'access_token' => $accessToken,
        ]);

        return $this;
    }
}
