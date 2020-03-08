<?php

declare(strict_types=1);

namespace Autowp\ExternalLoginService;

abstract class AbstractService
{
    /** @var array */
    protected array $options;

    abstract public function getState(): string;

    abstract public function getLoginUrl(): string;

    abstract public function getFriendsUrl(): string;

    /**
     * @return mixed
     */
    abstract public function callback(array $params);

    abstract public function getData(array $options): Result;

    abstract public function getFriends(): array;

    public function __construct(array $options)
    {
        $this->options = $options;
    }
}
