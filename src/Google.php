<?php

declare(strict_types=1);

namespace Autowp\ExternalLoginService;

use Google_Client;
use Laminas\Uri\Http;
use Laminas\Uri\UriFactory;

use function preg_split;

class Google extends AbstractService
{
    /** @var string */
    private string $idToken;

    public function getState(): string
    {
        return '';
    }

    public function getLoginUrl(): string
    {
        return '';
    }

    public function getFriendsUrl(): string
    {
        return '';
    }

    public function callback(array $params): array
    {
        return [];
    }

    public function setIDToken(string $idToken): self
    {
        $this->idToken = $idToken;

        return $this;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getData(array $options): Result
    {
        $clientIDs = preg_split("/[[:space:]]+/isu", $this->options['clientId']);

        foreach ($clientIDs as $clientID) {
            if (! $clientID) {
                continue;
            }

            $client = new Google_Client([
                'client_id' => $clientID,
            ]);

            $payload = $client->verifyIdToken($this->idToken);
            if ($payload) {
                break;
            }
        }

        if (! $payload) {
            throw new ExternalLoginServiceException("idToken verification failed");
        }

        $photoUrl = $payload['picture'];
        if ($photoUrl) {
            $photoUrl = $this->setSizeParam($photoUrl, 640);
        }

        return new Result([
            'externalId' => $payload['sub'],
            'name'       => $payload['name'],
            'profileUrl' => '',
            'photoUrl'   => $photoUrl,
            'location'   => '',
            'language'   => $payload['locale'],
            'email'      => $payload['email'],
        ]);
    }

    public function getFriends(): array
    {
        return [];
    }

    private function setSizeParam(string $url, int $size): string
    {
        $uri = UriFactory::factory($url);

        if ($uri instanceof Http) {
            $params       = $uri->getQueryAsArray();
            $params['sz'] = $size;
            $uri->setQuery($params);

            $url = $uri->toString();
        }

        return $url;
    }
}
