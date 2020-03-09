<?php

declare(strict_types=1);

namespace Autowp\ExternalLoginService;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

use function array_replace;
use function is_array;

class LoginServiceFactory implements FactoryInterface
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param string $name
     * @throws ExternalLoginServiceException
     */
    public function __invoke(ContainerInterface $container, $name, ?array $options = null): AbstractService
    {
        $config = $container->get('config');
        if (! isset($config['external_login_services'][$name])) {
            throw new ExternalLoginServiceException("Config for `$name` not found");
        }

        $params = $config['external_login_services'][$name];
        if (is_array($options)) {
            $params = array_replace($params, $options);
        }

        return new $name($params);
    }
}
