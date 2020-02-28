<?php

declare(strict_types=1);

namespace HelloWorld\HelloWorldApi\Api\Data;

/**
 * RestAPI Hello World interface.
 */
interface HelloWorldManagementInterface
{
    /**
     * Get Hello world action.
     *
     * @return string
     */
    public function getHelloWorld() : string;
}
