<?php

namespace Autowp\ExternalLoginService;

class Module
{
    /**
     * Return default zend-validator configuration for zend-mvc applications.
     */
    public function getConfig()
    {
        $provider = new ConfigProvider();

        return [
            'service_manager' => $provider->getDependencyConfig(),
        ];
    }
}
