<?php

declare(strict_types=1);

namespace Autowp\ExternalLoginService\Provider;

use League\OAuth2\Client\Provider\Facebook as LeagueFacebookProvider;

class FacebookProvider extends LeagueFacebookProvider
{
    public function getDefaultScopes(): array
    {
        return ['public_profile', 'email', 'user_hometown'];
    }
}
