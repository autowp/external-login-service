<?php

namespace Autowp\ExternalLoginService;

use Exception;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Config;
use Zend\ServiceManager\Factory\FactoryInterface;

class LoginServiceFactory implements FactoryInterface
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(ContainerInterface $container, $name, array $options = null)
    {
        $config = $container->get('config');
        if (! isset($config['external_login_services'][$name])) {
            throw new Exception("Config for `$name` not found");
        }
        return new $name($config['external_login_services'][$name]);
    }
}
