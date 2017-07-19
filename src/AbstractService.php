<?php

namespace Autowp\ExternalLoginService;

use Autowp\ExternalLoginService\Result;

abstract class AbstractService
{
    /**
     * @var array
     */
    protected $options;

    /**
     * @return string
     */
    abstract public function getState();

    abstract public function getLoginUrl();

    /**
     * @return string
     */
    abstract public function getFriendsUrl();

    /**
     * @param array $params
     * @return bool
     */
    abstract public function callback(array $params);

    /**
     * @return Result
     */
    abstract public function getData(array $options);

    /**
     * @return string
     */
    abstract public function getFriends();

    public function __construct(array $options)
    {
        $this->options = $options;
    }
}
