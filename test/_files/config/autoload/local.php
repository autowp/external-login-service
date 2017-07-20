<?php

namespace Autowp\ExternalLoginService;

return [
    'external_login_services' => [
        Facebook::class => [
            'clientId'        => 'xxxx',
            'clientSecret'    => 'yyyy',
            'redirectUri'     => 'http://example.com/callback',
            'scope'           => ['public_profile', 'user_friends'],
            'graphApiVersion' => 'v2.10'
        ],
        Github::class => [
            'clientId'     => 'xxxx',
            'clientSecret' => 'yyyy',
            'redirectUri'  => 'http://example.com/callback'
        ],
        GooglePlus::class => [
            'clientId'        => 'xxxx',
            'clientSecret'    => 'yyyy',
            'redirectUri'     => 'http://example.com/callback',
        ],
        Linkedin::class => [
            'clientId'        => 'xxxx',
            'clientSecret'    => 'yyyy',
            'redirectUri'     => 'http://example.com/callback',
        ],
        Twitter::class => [
            'consumerKey'     => 'xxxx',
            'consumerSecret'  => 'yyyy',
            'redirectUri'     => 'http://example.com/callback',
        ],
        Vk::class => [
            'clientId'        => 'xxxx',
            'clientSecret'    => 'yyyy',
            'redirectUri'     => 'http://example.com/callback',
        ]
    ]
];
