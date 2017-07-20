<?php

namespace Autowp\ExternalLoginService;

return [
    'external_login_services' => [
        Facebook::class => [
            'clientId'        => 'xxxx',
            'clientSecret'    => 'yyyy',
            'redirectUri'     => 'http://example.com/',
            'scope'           => ['public_profile', 'user_friends'],
            'graphApiVersion' => 'v2.10'
        ],
        Github::class => [
            'clientId'     => 'xxxx',
            'clientSecret' => 'yyyy',
            'redirectUri'  => 'http://example.com/'
        ],
        GooglePlus::class => [
            'clientId'        => 'xxxx',
            'clientSecret'    => 'yyyy',
            'redirectUri'     => 'http://example.com/',
        ],
        Linkedin::class => [
            'clientId'        => 'xxxx',
            'clientSecret'    => 'yyyy',
            'redirectUri'     => 'http://example.com/',
        ],
        Twitter::class => [
            'consumerKey'     => 'xxxx',
            'consumerSecret'  => 'yyyy',
            'redirectUri'     => 'http://example.com/',
        ],
        Vk::class => [
            'clientId'        => 'xxxx',
            'clientSecret'    => 'yyyy',
            'redirectUri'     => 'http://example.com/',
        ]
    ]
];
