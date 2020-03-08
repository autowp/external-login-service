<?php

declare(strict_types=1);

namespace Autowp\ExternalLoginService;

use DateTime;
use Laminas\Uri\Http;
use Laminas\Uri\UriFactory;
use League\OAuth2\Client\Provider\Google as GoogleProvider;

use function implode;
use function is_array;

class GooglePlus extends AbstractLeagueOAuth2
{
    protected function createProvider(): GoogleProvider
    {
        return new GoogleProvider([
            'clientId'     => $this->options['clientId'],
            'clientSecret' => $this->options['clientSecret'],
            'redirectUri'  => $this->options['redirectUri'] ?? null,
            'userFields'   => [
                'id',
                'displayName',
                'url',
                'image(url)',
                'gender',
                'language',
                'placesLived',
                'birthday',
            ],
            //'hostedDomain' => 'example.com',
        ]);
    }

    protected function getAuthorizationUrl(): string
    {
        return $this->getProvider()->getAuthorizationUrl([
            'scope' => implode(' ', [
                'https://www.googleapis.com/auth/plus.me',
                'https://www.googleapis.com/auth/userinfo.email',
                'https://www.googleapis.com/auth/userinfo.profile',
            ]),
        ]);
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

        $photoUrl = $ownerDetailsArray['image']['url'];
        if ($photoUrl) {
            $photoUrl = $this->removeSizeParam($photoUrl);
        }

        return new Result([
            'externalId' => $ownerDetailsArray['id'],
            'name'       => $ownerDetailsArray['displayName'],
            'profileUrl' => $ownerDetailsArray['url'] ?? null,
            'photoUrl'   => $photoUrl,
            'email'      => $email,
            'gender'     => $ownerDetailsArray['gender'] ?? null,
            'language'   => $ownerDetailsArray['language'],
            'location'   => $location,
            'birthday'   => $birthday,
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

    private function removeSizeParam(string $url): string
    {
        $uri = UriFactory::factory($url);

        if ($uri instanceof Http) {
            $params = $uri->getQueryAsArray();
            unset($params['sz']);
            $uri->setQuery($params);

            $url = $uri->toString();
        }

        return $url;
    }
}
