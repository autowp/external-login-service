<?php

namespace Autowp\ExternalLoginService;

use Autowp\ExternalLoginService\Exception;
use Autowp\ExternalLoginService\LeagueOAuth2;
use Autowp\ExternalLoginService\Result;
use Autowp\ExternalLoginService\Provider\Vk as VkProvider;

class Vk extends LeagueOAuth2
{
    protected function _createProvider()
    {
        return new VkProvider([
            'clientId'     => $this->_options['clientId'],
            'clientSecret' => $this->_options['clientSecret'],
            'redirectUri'  => $this->_options['redirect_uri'],
        ]);
    }

    protected function _getAuthorizationUrl()
    {
        return $this->_getProvider()->getAuthorizationUrl();
    }

    protected function _getFriendsAuthorizationUrl()
    {
        throw new Exception("Not implemented");
    }

    /**
     * @return Result
     */
    public function getData(array $options)
    {
        $provider = $this->_getProvider();

        if (isset($options['language'])) {
            $provider->setLang($options['language']);
        }

        $ownerDetails = $provider->getResourceOwner($this->_accessToken);

        $data = [
            'externalId' => null,
            'name'       => null,
            'profileUrl' => null,
            'photoUrl'   => null
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

    public function serviceFriends($token)
    {
        throw new Exception("Not implemented");
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