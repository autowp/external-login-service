<?php

namespace Autowp\ExternalLoginService;

return [
    'external_login_services' => [
        Github::class => [
            'clientId'     => 'xxxx',
            'clientSecret' => 'yyyy',
            'redirectUri'  => 'http://example.com/'
        ]
    ]
];
