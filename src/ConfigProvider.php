<?php

namespace Autowp\ExternalLoginService;

class ConfigProvider
{
    public function __invoke()
    {
        return [
            'dependencies' => $this->getDependencyConfig(),
        ];
    }

    /**
     * Return dependency mappings for this component.
     *
     * @return array
     */
    public function getDependencyConfig()
    {
        return [
            'factories' => [
                'ExternalLoginServiceManager' => PluginManagerFactory::class,
            ],
        ];
    }
}
