<?php

namespace Autowp\ExternalLoginService;

use DateTime;
use Locale;

use Zend\Json\Json;

class Facebook extends LeagueOAuth2
{
    private $graphApiVersion = 'v2.5';

    private $defaultScope = ['public_profile', 'user_friends', 'user_hometown'];

    protected function createProvider()
    {
        return new Provider\Facebook([
            'clientId'        => $this->options['clientId'],
            'clientSecret'    => $this->options['clientSecret'],
            'redirectUri'     => isset($this->options['redirect_uri']) ? $this->options['redirect_uri'] : null,
            'graphApiVersion' => $this->graphApiVersion,
        ]);
    }

    private function getScope()
    {
        $scope = $this->defaultScope;
        if (isset($this->options['scope']) && is_array($this->options['scope'])) {
            $scope = $this->options['scope'];
        }

        return $scope;
    }

    protected function getAuthorizationUrl()
    {
        return $this->getProvider()->getAuthorizationUrl([
            'scope' => $this->getScope()
        ]);
    }

    protected function getFriendsAuthorizationUrl()
    {
        return $this->getProvider()->getAuthorizationUrl([
            'scope' => $this->getScope()
        ]);
    }

    /**
     * @var string
     */
    protected $imageUrlTemplate =
        'https://graph.facebook.com/%s/picture?type=large';

    /**
     * @param array $options
     * @return string
     */
    /*public function getFriendsUrl(array $options)
    {
        $this->_getFacebook()->setPermission(Autowp_Service_Facebook::PERMISSION_FRIENDS);
        return $this->_getFacebook()->getLoginUrl(array(
            'redirect_uri' => $options['redirect_uri']
        ));
    }*/

    /**
     * @return Result
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getData(array $options)
    {
        $provider = $this->getProvider();

        $ownerDetails = $provider->getResourceOwner($this->accessToken);

        $json = $ownerDetails->toArray();

        $data = [
            'externalId' => null,
            'name'       => null,
            'profileUrl' => null,
            'photoUrl'   => null,
            'birthday'   => null,
            'email'      => null,
            'gender'     => null,
            'location'   => null,
            'language'   => null
        ];
        if (isset($json['id']) && $json['id']) {
            $data['externalId'] = $json['id'];
            $data['photoUrl'] = sprintf($this->imageUrlTemplate, $json['id']);
        }
        if (isset($json['name']) && $json['name']) {
            $data['name'] = $json['name'];
        }
        if (isset($json['link']) && $json['link']) {
            $data['profileUrl'] = $json['link'];
        }
        if (isset($json['birthday']) && $json['birthday']) {
            $data['birthday'] = DateTime::createFromFormat('m/d/Y', $json['birthday']);
        }
        if (isset($json['email']) && $json['email']) {
            $data['email'] = $json['email'];
        }
        if (isset($json['gender']) && $json['gender']) {
            $data['gender'] = $json['gender'];
        }
        if (isset($json['location']['name']) && $json['location']['name']) {
            $data['location'] = $json['location']['name'];
        }
        if (isset($json['hometown']['name']) && $json['hometown']['name']) {
            $data['location'] = $json['hometown']['name'];
        }
        if (isset($json['locale']) && $json['locale']) {
            $data['language'] = Locale::getPrimaryLanguage($json['locale']);
        }
        return new Result($data);
    }

    public function getFriends()
    {
        if (! $this->accessToken) {
            throw new Exception("Access token not provided");
        }

        $limit = 1000;
        $url = 'https://graph.facebook.com/' . $this->graphApiVersion . '/me/friends?' . http_build_query([
            'limit'        => $limit,
            'offset'       => 0,
            'access_token' => $this->accessToken->getToken()
        ]);

        $friendsId = [];
        while (true) {
            $response = file_get_contents($url);
            try {
                $response = Json::decode($response);
            } catch (Json\Exception\RuntimeException $e) {
                $response = null;
            }

            if (! $response) {
                throw new Exception('Error requesting data');
            }

            if (isset($response->data) && is_array($response->data)) {
                foreach ($response->data as $value) {
                    $friendsId[] = (string)$value->id;
                }
            }
            if (count($friendsId) == 0) {
                break;
            }
            if (count($friendsId) != $limit || ! isset($response->paging->next)) {
                break;
            }
            $url = $response->paging->next;
        }
        return $friendsId;
    }
}
