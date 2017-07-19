<?php

namespace Autowp\ExternalLoginService;

use Autowp\ExternalLoginService\Exception;
use Autowp\ExternalLoginService\LeagueOAuth2;
use Autowp\ExternalLoginService\Result;

use League\OAuth2\Client\Provider\Google as GoogleProvider;

use DateTime;

class GooglePlus extends LeagueOAuth2
{
    protected function createProvider()
    {
        return new GoogleProvider([
            'clientId'     => $this->options['clientId'],
            'clientSecret' => $this->options['clientSecret'],
            'redirectUri'  => isset($this->options['redirect_uri']) ? $this->options['redirect_uri'] : null,
            'userFields'   => ['id', 'displayName', 'url', 'image(url)',
                               'gender', 'language', 'placesLived', 'birthday']
            //'hostedDomain' => 'example.com',
        ]);
    }

    protected function getAuthorizationUrl()
    {
        return $this->getProvider()->getAuthorizationUrl([
            'scope' => implode(' ', [
                'https://www.googleapis.com/auth/plus.me',
                'https://www.googleapis.com/auth/userinfo.email',
                'https://www.googleapis.com/auth/userinfo.profile'
            ]),
        ]);
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

        $ownerDetailsArray = $ownerDetails->toArray();

        $email = null;
        if (isset($ownerDetailsArray['emails']) && is_array($ownerDetailsArray['emails'])) {
            foreach ($ownerDetailsArray['emails'] as $value) {
                if ($value['value']) {
                    $email = $value['value'];
                    break;
                }
            }
        }

        $location = null;
        if (isset($ownerDetailsArray['placesLived']) && is_array($ownerDetailsArray['placesLived'])) {
            foreach ($ownerDetailsArray['placesLived'] as $value) {
                if ($value['value']) {
                    $location = $value['value'];
                    break;
                }
            }
        }

        $birthday = null;
        if (isset($ownerDetailsArray['birthday']) && $ownerDetailsArray['birthday']) {
            $birthday = DateTime::createFromFormat('Y-m-d', $ownerDetailsArray['birthday']);
        }

        return new Result([
            'externalId' => $ownerDetailsArray['id'],
            'name'       => $ownerDetailsArray['displayName'],
            'profileUrl' => $ownerDetailsArray['url'],
            'photoUrl'   => $ownerDetailsArray['image']['url'],
            'email'      => $email,
            'gender'     => $ownerDetailsArray['gender'],
            'language'   => $ownerDetailsArray['language'],
            'location'   => $location,
            'birthday'   => $birthday
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
