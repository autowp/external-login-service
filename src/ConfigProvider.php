<?php

declare(strict_types=1);

namespace Autowp\ExternalLoginService;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => $this->getDependencyConfig(),
        ];
    }

    /**
     * Return dependency mappings for this component.
     */
    public function getDependencyConfig(): array
    {
        return [
            'factories' => [
                'ExternalLoginServiceManager' => PluginManagerFactory::class,
            ],
        ];
    }
}
