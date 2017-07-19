<?php

namespace Autowp\ExternalLoginService;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

class PluginManagerFactory implements FactoryInterface
{
    /**
     * {@inheritDoc}
     *
     * @return ValidatorPluginManager
     */
    public function __invoke(ContainerInterface $container, $name, array $options = null)
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
