<?php

namespace Autowp\ExternalLoginService\Provider;

use League\OAuth2\Client\Provider\Facebook as LeagueFacebookProvider;

class Facebook extends LeagueFacebookProvider
{
    public function getDefaultScopes()
    {
        return ['public_profile', 'email', 'user_hometown'];
    }
}
