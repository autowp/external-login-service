<?php

declare(strict_types=1);

namespace Autowp\ExternalLoginService;

class Module
{
    /**
     * Return default zend-validator configuration for zend-mvc applications.
     */
    public function getConfig(): array
    {
        $provider = new ConfigProvider();

        return [
            'service_manager' => $provider->getDependencyConfig(),
        ];
    }
}
