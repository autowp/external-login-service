<?php

namespace Autowp\ExternalLoginService\Service;

use ZendService\Twitter\Response;
use ZendService\Twitter\Twitter as ZendTwitterService;

class Twitter extends ZendTwitterService
{
    /**
     * Verify Account Credentials
     *
     * @throws Http\Client\Exception\ExceptionInterface if HTTP request fails or times out
     * @throws Exception\DomainException if unable to decode JSON payload
     * @return Response
     */
    public function accountVerifyCredentials(array $params = [])
    {
        $this->init();
        $response = $this->get('account/verify_credentials', $params);
        return new Response($response);
    }
}