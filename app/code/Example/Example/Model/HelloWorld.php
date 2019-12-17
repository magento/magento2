<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Example\Example\Model;

use Example\ExampleApi\Api\WelcomeMessageInterface;

/**
 * HelloWorld model
 *
 * @api
 */
class HelloWorld implements WelcomeMessageInterface
{
    private const MESSAGE = 'Hello World!';

    /**
     * Returns greeting message to user.
     *
     * @return string Greeting message
     */
    public function execute(): string
    {
        return self::MESSAGE;
    }
}
