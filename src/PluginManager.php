<?php

declare(strict_types=1);

namespace Autowp\ExternalLoginService;

use Interop\Container\Exception\ContainerException;
use Laminas\ServiceManager\AbstractPluginManager;
use Laminas\ServiceManager\Exception\InvalidServiceException;

use function get_class;
use function gettype;
use function is_object;
use function sprintf;

class PluginManager extends AbstractPluginManager
{
    protected $aliases = [
        'facebook'    => Facebook::class,
        'github'      => Github::class,
        'google-plus' => GooglePlus::class,
        'googleplus'  => GooglePlus::class,
        'google'      => Google::class,
        'linkedin'    => Linkedin::class,
        'linked-in'   => Linkedin::class,
        'twitter'     => Twitter::class,
        'vk'          => Vk::class,
    ];

    protected $factories = [
        Facebook::class   => LoginServiceFactory::class,
        Github::class     => LoginServiceFactory::class,
        GooglePlus::class => LoginServiceFactory::class,
        Google::class     => LoginServiceFactory::class,
        Linkedin::class   => LoginServiceFactory::class,
        Twitter::class    => LoginServiceFactory::class,
        Vk::class         => LoginServiceFactory::class,
    ];

    /**
     * Default instance type
     *
     * @var string
     */
    protected $instanceOf = AbstractService::class;

    /**
     * Validate an instance
     *
     * @param  object $plugin
     * @throws InvalidServiceException If created instance does not respect the.
     *     constraint on type imposed by the plugin manager.
     * @throws ContainerException If any other error occurs.
     */
    public function validate($plugin)
    {
        if (! $plugin instanceof $this->instanceOf) {
            throw new InvalidServiceException(sprintf(
                '%s expects only to create instances of %s; %s is invalid',
                static::class,
                $this->instanceOf,
                is_object($plugin) ? get_class($plugin) : gettype($plugin)
            ));
        }
    }
}
