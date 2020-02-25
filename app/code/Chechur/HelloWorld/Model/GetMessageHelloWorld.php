<?php

declare(strict_types=1);

namespace Chechur\HelloWorld\Model;

use Chechur\HelloWorldApi\Api\GetMessageHelloWorldInterface;

/**
 * @inheritDoc
 */
class GetMessageHelloWorld implements GetMessageHelloWorldInterface
{
    /**
     * @inheritDoc
     */
    public function execute(): string
    {
        return 'Hello World';
    }
}
