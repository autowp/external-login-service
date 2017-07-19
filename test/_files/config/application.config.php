<?php

return [
    'modules' => [
        'Zend\Router',
        'Autowp\ExternalLoginService'
    ],
    'module_listener_options' => [
        'module_paths' => [
            './vendor',
        ],
        'config_glob_paths' => [
            'test/_files/config/autoload/local.php',
        ],
    ]
];
