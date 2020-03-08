<?php

declare(strict_types=1);

namespace Autowp\ExternalLoginService;

use Autowp\ExternalLoginService\Provider\VkProvider;

class Vk extends AbstractLeagueOAuth2
{
    protected function createProvider(): VkProvider
    {
        return new VkProvider([
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

    public function getData(array $options): Result
    {
        $provider = $this->getProvider();

        if (isset($options['language'])) {
            $provider->setLang($options['language']);
        }

        $ownerDetails = $provider->getResourceOwner($this->accessToken);

        $data = [
            'externalId' => null,
            'name'       => null,
            'profileUrl' => null,
            'photoUrl'   => null,
        ];

        $vkUser = $ownerDetails->toArray();

        if (isset($vkUser['id']) && $vkUser['id']) {
            $data['externalId'] = $vkUser['id'];
        }

        $firstName = false;
        if (isset($vkUser['first_name']) && $vkUser['first_name']) {
            $firstName = $vkUser['first_name'];
        }
        $lastName = false;
        if (isset($vkUser['last_name']) && $vkUser['last_name']) {
            $lastName = $vkUser['last_name'];
        }
        $data['name'] = $firstName . ($firstName && $lastName ? ' ' : '') . $lastName;
        if (isset($vkUser['screen_name']) && $vkUser['screen_name']) {
            $data['profileUrl'] = 'http://vk.com/' . $vkUser['screen_name'];
        }
        if (isset($vkUser['photo_max_orig']) && $vkUser['photo_max_orig']) {
            $data['photoUrl'] = $vkUser['photo_max_orig'];
        }

        return new Result($data);
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
