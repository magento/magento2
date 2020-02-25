<?php

declare(strict_types=1);

namespace Chechur\HelloWorldChangeMessage\Plugin\HelloWorldApi\Api\GetMessageHelloWorld;

use Chechur\HelloWorldApi\Api\GetMessageHelloWorldInterface;

/**
 * Add h1 tags around the message.
 */
class AddTagsToMessage
{
    /**
     * Add h1 tags around original message.
     *
     * @param GetMessageHelloWorldInterface $getMessageHelloWorld
     * @param \Closure $proceed
     * @param string $result
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundExecute(
        GetMessageHelloWorldInterface $getMessageHelloWorld,
        \Closure $proceed,
        string $result
    ): string {
        return "<h1>{$result}{$proceed()}</h1>";
    }
}
