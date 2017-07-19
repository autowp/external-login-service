<?php

namespace Autowp\ExternalLoginService;

use Autowp\ExternalLoginService\AbstractService;
use Autowp\ExternalLoginService\Exception;

use Zend\Filter\Word\DashToCamelCase;

class Factory
{
    /**
     * @var array
     */
    private $options;

    public function __construct(array $options)
    {
        $this->options = $options;
    }

    /**
     * @param string $service
     * @return AbstractService
     * @throws Exception
     */
    public function getService($service, $optionsKey, array $options)
    {
        $service = trim($service);
        if (! isset($this->options[$optionsKey])) {
            throw new Exception("Service '$optionsKey' options not found");
        }

        $filter = new DashToCamelCase();

        $className = 'Autowp\\ExternalLoginService\\' . ucfirst($filter->filter($service));

        $serviceOptions = array_replace($this->options[$optionsKey], $options);
        $serviceObj = new $className($serviceOptions);

        if (! $serviceObj instanceof AbstractService) {
            throw new Exception("'$className' is not AbstractService");
        }

        return $serviceObj;
    }

    public function getCallbackUrl()
    {
        if (! isset($this->options['callback'])) {
            throw new Exception('`callback` not set');
        }

        return $this->options['callback'];
    }
}
