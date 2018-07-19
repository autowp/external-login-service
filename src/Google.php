<?php

namespace Autowp\ExternalLoginService;

use Autowp\ExternalLoginService\Exception;
use Autowp\ExternalLoginService\AbstractService;
use Autowp\ExternalLoginService\Result;

use Google_Client;
use GuzzleHttp\Exception\BadResponseException;

class Google extends AbstractService
{
    /**
     * @var Google_Client
     */
    private $client = null;

    /**
     * @var string
     */
    private $idToken;

    public function getState()
    {
        return '';
    }

    /**
     * @return Google_Client
     */
    public function getClient()
    {
        if (! $this->client) {
            $this->client = new Google_Client([
                'client_id' => $this->options['clientId']
            ]);
            $this->client->addScope("email");
        }

        return $this->client;
    }

    /**
     * @return string
     */
    public function getLoginUrl()
    {
        return '';
    }

    /**
     * @return string
     */
    public function getFriendsUrl()
    {
        return '';
    }

    /**
     * @param array $params
     */
    public function callback(array $params)
    {
        return [];
    }

    public function setIDToken(string $idToken)
    {
        $this->idToken = $idToken;

        return $this;
    }

    /**
     * @return Result
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getData(array $options)
    {
        $payload = $this->getClient()->verifyIdToken($this->idToken);
        if (! $payload) {
            throw new Exception("idToken verification failed");
        }

        return new Result([
            'externalId' => $payload['sub'],
            'name'       => $payload['name'],
            'profileUrl' => '',
            'photoUrl'   => $payload['picture'],
            'location'   => '',
            'language'   => $payload['locale'],
            'email'      => $payload['email']
        ]);
    }

    public function getFriends()
    {
        return [];
    }
}
