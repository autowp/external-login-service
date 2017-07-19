<?php

namespace Autowp\ExternalLoginService;

use Autowp\ExternalLoginService\Exception;

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

    public function getCallbackUrl()
    {
        if (! isset($this->options['callback'])) {
            throw new Exception('`callback` not set');
        }

        return $this->options['callback'];
    }
}
