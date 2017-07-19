<?php

namespace Autowp\ExternalLoginService;

use Zend\ServiceManager\AbstractPluginManager;

class PluginManager extends AbstractPluginManager
{
    protected $aliases = [
        'facebook'    => Facebook::class,
        'github'      => Github::class,
        'google-plus' => GooglePlus::class,
        'googleplus'  => GooglePlus::class,
        'linkedin'    => Linkedin::class,
        'twitter'     => Twitter::class,
        'vk'          => Vk::class,
    ];

    protected $factories = [
        Facebook::class   => LoginServiceFactory::class,
        Github::class     => LoginServiceFactory::class,
        GooglePlus::class => LoginServiceFactory::class,
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
     * @return void
     * @throws InvalidServiceException If created instance does not respect the
     *     constraint on type imposed by the plugin manager
     * @throws ContainerException if any other error occurs
     */
    public function validate($plugin)
    {
        if (! $plugin instanceof $this->instanceOf) {
            throw new InvalidServiceException(sprintf(
                '%s expects only to create instances of %s; %s is invalid',
                get_class($this),
                $this->instanceOf,
                (is_object($plugin) ? get_class($plugin) : gettype($plugin))
            ));
        }
    }
}
