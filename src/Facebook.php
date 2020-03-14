<?php

declare(strict_types=1);

namespace Autowp\ExternalLoginService;

use DateTime;
use Laminas\Json;
use Locale;

use function count;
use function file_get_contents;
use function http_build_query;
use function is_array;
use function sprintf;

class Facebook extends AbstractLeagueOAuth2
{
    private array $defaultScope = ['public_profile', 'user_friends', 'user_hometown'];

    protected function createProvider(): Provider\FacebookProvider
    {
        return new Provider\FacebookProvider([
            'clientId'        => $this->options['clientId'],
            'clientSecret'    => $this->options['clientSecret'],
            'redirectUri'     => $this->options['redirectUri'] ?? null,
            'graphApiVersion' => $this->options['graphApiVersion'],
        ]);
    }

    private function getScope(): array
    {
        $scope = $this->defaultScope;
        if (isset($this->options['scope']) && is_array($this->options['scope'])) {
            $scope = $this->options['scope'];
        }

        return $scope;
    }

    protected function getAuthorizationUrl(): string
    {
        return $this->getProvider()->getAuthorizationUrl([
            'scope' => $this->getScope(),
        ]);
    }

    protected function getFriendsAuthorizationUrl(): string
    {
        return $this->getAuthorizationUrl();
    }

    /** @var string */
    protected string $imageUrlTemplate = 'https://graph.facebook.com/%s/picture?type=large';

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
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @throws ExternalLoginServiceException
     */
    public function getData(array $options): Result
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
            'language'   => null,
        ];
        if (isset($json['id']) && $json['id']) {
            $data['externalId'] = $json['id'];
            $data['photoUrl']   = sprintf($this->imageUrlTemplate, $json['id']);
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

    /**
     * @throws ExternalLoginServiceException
     */
    public function getFriends(): array
    {
        if (! $this->accessToken) {
            throw new ExternalLoginServiceException("Access token not provided");
        }

        $limit = 1000;
        $url   = 'https://graph.facebook.com/' . $this->options['graphApiVersion'] . '/me/friends?' . http_build_query([
            'limit'        => $limit,
            'offset'       => 0,
            'access_token' => $this->accessToken->getToken(),
        ]);

        $friendsId = [];
        while (true) {
            $response = file_get_contents($url);
            try {
                $response = Json\Json::decode($response);
            } catch (Json\Exception\RuntimeException $e) {
                $response = null;
            }

            if (! $response) {
                throw new ExternalLoginServiceException('Error requesting data');
            }

            if (isset($response->data) && is_array($response->data)) {
                foreach ($response->data as $value) {
                    $friendsId[] = (string) $value->id;
                }
            }
            if (count($friendsId) === 0) {
                break;
            }
            if (count($friendsId) !== $limit || ! isset($response->paging->next)) {
                break;
            }
            $url = $response->paging->next;
        }
        return $friendsId;
    }
}
