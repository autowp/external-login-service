<?php

namespace Autowp\ExternalLoginService;

use Autowp\ExternalLoginService\Exception;
use Autowp\ExternalLoginService\AbstractService;
use Autowp\ExternalLoginService\Result;

use League\OAuth1\Client\Server\Twitter as TwitterServer;
use GuzzleHttp\Exception\BadResponseException;

class Twitter extends AbstractService
{
    /**
     * @var Twitter
     */
    protected $server = null;

    /**
     *
     * @var \Zend\Session\Container
     */
    protected $session;

    /**
     *
     * @var Access
     */
    protected $accessToken = null;

    /**
     * @var string
     */
    private $state = null;

    protected function getSession()
    {
        return $this->session ? $this->session : $this->session = new \Zend\Session\Container('Twitter');
    }

    /**
     * @return TwitterServer
     */
    public function getServer()
    {
        if (!$this->server) {
            
            $serverOptions = [
                'identifier' => $this->_options['consumerKey'],
                'secret'     => $this->_options['consumerSecret']
            ];
            if (isset($this->_options['redirect_uri'])) {
                $serverOptions['callback_uri'] = $this->_options['redirect_uri'];
            }
            
            $this->server = new TwitterServer($serverOptions);
        }

        return $this->server;
    }

    public function getState()
    {
        return $this->state;
    }

    /**
     * @param array $options
     * @return string
     */
    public function getLoginUrl()
    {
        $temporaryCredentials = $this->getServer()->getTemporaryCredentials();
        
        // Store credentials in the session, we'll need them later
        $this->getSession()->temporaryCredentials = $temporaryCredentials;
        
        $this->state = $temporaryCredentials->getIdentifier();
        
        // Second part of OAuth 1.0 authentication is to redirect the
        // resource owner to the login screen on the server.
        return $this->getServer()->getAuthorizationUrl($temporaryCredentials);
    }

    /**
     * @param array $options
     * @return string
     */
    public function getFriendsUrl()
    {
        $temporaryCredentials = $this->getServer()->getTemporaryCredentials();
        
        // Store credentials in the session, we'll need them later
        $this->getSession()->temporaryCredentials = $temporaryCredentials;
        
        $this->state = $temporaryCredentials->getIdentifier();
        
        // Second part of OAuth 1.0 authentication is to redirect the
        // resource owner to the login screen on the server.
        return $this->getServer()->getAuthorizationUrl($temporaryCredentials);
    }

    /**
     * @param array $params
     */
    public function callback(array $params)
    {
        if (isset($params['denied']) && $params['denied']) {
            return false;
        }
        
        // Retrieve the temporary credentials we saved before
        $session = $this->getSession();
        if (!isset($session->temporaryCredentials)) {
            throw new Exception('Request token not set');
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
     *
     * @return Result
     */
    public function getData(array $options)
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
            'email'      => isset($user->email) ? $user->email : null
        ];
        
        return new Result($data);
    }
    
    private function friendsIds($cursor, $count)
    {
        $url = 'https://api.twitter.com/1.1/friends/ids.json';
        
        $url .= '?' . http_build_query([
            'cursor' => $cursor,
            'count'  => $count
        ]);
        
        $client = $this->getServer()->createHttpClient();
        
        $headers = $this->getServer()->getHeaders($this->accessToken, 'GET', $url);
        
        try {
            $response = $client->get($url, [
                'headers' => $headers,
            ]);
        } catch (BadResponseException $e) {
            $response = $e->getResponse();
            $body = $response->getBody();
            $statusCode = $response->getStatusCode();
        
            throw new \Exception(
                "Received error [$body] with status code [$statusCode] when retrieving token credentials."
            );
        }
        $data = json_decode((string) $response->getBody(), true);
        
        return $data;
    }

    public function getFriends()
    {
        $cursor = - 1;
        $friendsId = [];
        $count = 1000;
        while (true) {
            
            $response = $this->friendsIds($cursor, $count);
            if (!$response) {
                break;
            }
                
            foreach ($response['ids'] as &$value) {
                $friendsId[] = (string) $value;
            }
            if (count($response['ids']) != $count) {
                break;
            }
            
            $cursor++;
        }
        return $friendsId;
    }

    public function setAccessToken(array $params)
    {
        throw new \Exception("Not implemented");
        /*$this->accessToken = new Access();
        $this->accessToken->setParams($params);
        return $this;*/
    }
}