<?php

declare(strict_types=1);

namespace Autowp\ExternalLoginService;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class PluginManagerFactory implements FactoryInterface
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param string $name
     */
    public function __invoke(ContainerInterface $container, $name, ?array $options = null): PluginManager
    {
        $pluginManager = new PluginManager($container, $options ?: []);

        // If this is in a zend-mvc application, the ServiceListener will inject
        // merged configuration during bootstrap.
        if ($container->has('ServiceListener')) {
            return $pluginManager;
        }

        return $pluginManager;
    }
}
