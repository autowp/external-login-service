<?php

namespace Autowp\ExternalLoginService;

return [
    'external_login_services' => [
        Github::class => [
            'clientId'     => 'xxxx',
            'clientSecret' => 'yyyy',
            'redirectUri'  => 'http://example.com/'
        ],
        Facebook::class => [
            'clientId'        => 'xxxx',
            'clientSecret'    => 'yyyy',
            'redirectUri'     => 'http://example.com/',
            'scope'           => ['public_profile', 'user_friends'],
            'graphApiVersion' => 'v2.10'
        ]
    ]
];
