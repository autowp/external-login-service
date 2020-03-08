<?php

declare(strict_types=1);

namespace Autowp\ExternalLoginService;

use League\OAuth2\Client\Provider\Github as GithubProvider;

class Github extends AbstractLeagueOAuth2
{
    protected function createProvider(): GithubProvider
    {
        return new GithubProvider([
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
     */
    public function getData(array $options): Result
    {
        $provider = $this->getProvider();

        $ownerDetails = $provider->getResourceOwner($this->accessToken);
        $data         = $ownerDetails->toArray();

        return new Result([
            'externalId' => $data['id'],
            'name'       => $data['name'],
            'profileUrl' => $data['html_url'],
            'photoUrl'   => $data['avatar_url'],
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
