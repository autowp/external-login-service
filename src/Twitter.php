<?php

declare(strict_types=1);

namespace Autowp\ExternalLoginService;

use Exception;
use GuzzleHttp\Exception\BadResponseException;
use Laminas\Session\Container;
use League\OAuth1\Client;
use League\OAuth1\Client\Credentials\TokenCredentials;

use function count;
use function http_build_query;
use function json_decode;
use function str_replace;

use const JSON_THROW_ON_ERROR;

class Twitter extends AbstractService
{
    private Client\Server\Twitter $server;

    /** @var Container */
    private Container $session;

    /** @var Client\Credentials\TokenCredentials */
    private Client\Credentials\TokenCredentials $accessToken;

    /** @var string */
    private string $state;

    public function getSession(): Container
    {
        return $this->session ? $this->session : $this->session = new Container('Twitter');
    }

    /**
     * @return Client\Server\Twitter
     */
    public function getServer()
    {
        if (! $this->server) {
            $serverOptions = [
                'identifier' => $this->options['consumerKey'],
                'secret'     => $this->options['consumerSecret'],
            ];
            if (isset($this->options['redirectUri'])) {
                $serverOptions['callback_uri'] = $this->options['redirectUri'];
            }

            $this->server = new Client\Server\Twitter($serverOptions);
        }

        return $this->server;
    }

    public function setServer(Client\Server\Twitter $server)
    {
        $this->server = $server;
    }

    public function getState(): string
    {
        return $this->state;
    }

    public function getLoginUrl(): string
    {
        $temporaryCredentials = $this->getServer()->getTemporaryCredentials();

        // Store credentials in the session, we'll need them later
        $this->getSession()->temporaryCredentials = $temporaryCredentials;

        $this->state = $temporaryCredentials->getIdentifier();

        // Second part of OAuth 1.0 authentication is to redirect the
        // resource owner to the login screen on the server.
        return $this->getServer()->getAuthorizationUrl($temporaryCredentials);
    }

    public function getFriendsUrl(): string
    {
        $temporaryCredentials = $this->getServer()->getTemporaryCredentials();

        // Store credentials in the session, we'll need them later
        $this->getSession()->temporaryCredentials = $temporaryCredentials;

        $this->state = $temporaryCredentials->getIdentifier();

        // Second part of OAuth 1.0 authentication is to redirect the
        // resource owner to the login screen on the server.
        return $this->getServer()->getAuthorizationUrl($temporaryCredentials);
    }

    public function callback(array $params): ?TokenCredentials
    {
        if (isset($params['denied']) && $params['denied']) {
            return null;
        }

        if (! isset($params['oauth_token'], $params['oauth_verifier'])) {
            throw new ExternalLoginServiceException('oauth_token or oauth_verifier not provided');
        }

        // Retrieve the temporary credentials we saved before
        $session = $this->getSession();
        if (! isset($session->temporaryCredentials)) {
            throw new ExternalLoginServiceException('Request token not set');
        }

        $temporaryCredentials = $session->temporaryCredentials;

        // We will now retrieve token credentials from the server
        $tokenCredentials = $this->getServer()->getTokenCredentials(
            $temporaryCredentials,
            $params['oauth_token'],
            $params['oauth_verifier']
        );

        $this->accessToken = $tokenCredentials;

        return $tokenCredentials;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getData(array $options): Result
    {
        $user = $this->getServer()->getUserDetails($this->accessToken);

        $imageUrl = null;
        if ($user->imageUrl) {
            $imageUrl = str_replace('_normal', '', $user->imageUrl);
        }

        $data = [
            'externalId' => $user->uid,
            'name'       => $user->name,
            'profileUrl' => 'http://twitter.com/' . $user->nickname,
            'photoUrl'   => $imageUrl,
            'location'   => $user->location,
            'language'   => $user->lang,
            'email'      => $user->email ?? null,
        ];

        return new Result($data);
    }

    /**
     * @throws Exception
     */
    private function friendsIds(int $cursor, int $count): array
    {
        $url = 'https://api.twitter.com/1.1/friends/ids.json';

        $url .= '?' . http_build_query([
            'cursor' => $cursor,
            'count'  => $count,
        ]);

        $client = $this->getServer()->createHttpClient();

        $headers = $this->getServer()->getHeaders($this->accessToken, 'GET', $url);

        try {
            $response = $client->get($url, [
                'headers' => $headers,
            ]);
        } catch (BadResponseException $e) {
            $response   = $e->getResponse();
            $body       = $response->getBody();
            $statusCode = $response->getStatusCode();

            throw new Exception(
                "Received error [$body] with status code [$statusCode] when retrieving token credentials."
            );
        }
        return json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * @throws Exception
     */
    public function getFriends(): array
    {
        $cursor    = - 1;
        $friendsId = [];
        $count     = 1000;
        while (true) {
            $response = $this->friendsIds($cursor, $count);
            if (! $response) {
                break;
            }

            $value = null;
            foreach ($response['ids'] as &$value) {
                $friendsId[] = (string) $value;
            }
            if (count($response['ids']) !== $count) {
                break;
            }

            $cursor++;
        }
        return $friendsId;
    }

    public function setAccessToken(Client\Credentials\TokenCredentials $accessToken)
    {
        $this->accessToken = $accessToken;
    }
}
