<?php

declare(strict_types=1);

namespace HelloWorld\HelloWorld\Model;

use HelloWorld\HelloWorldApi\Api\Data\HelloWorldManagementInterface;

/**
 * HelloWorldManagement Class for Rest Api result
 */
class HelloWorldManagement implements HelloWorldManagementInterface
{
    /**
     * @var string
     */
    public $prefix = '';

    /**
     * @inheritDoc
     */
    public function getHelloWorld(): string
    {
        return 'Hello World';
    }
}
