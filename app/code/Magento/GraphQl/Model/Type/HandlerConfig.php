<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GraphQl\Model\Type;

use Magento\Framework\GraphQl\Exception\GraphQlInputException;

/**
 * Holds handler names for configured GraphQL types
 */
class HandlerConfig
{
    /**
     * @var string[]
     */
    private $handlers;

    /**
     * @param string[] $handlers
     */
    public function __construct(array $handlers = [])
    {
        $this->handlers = $handlers;
    }

    /**
     * Return all configured [type name => handler name] pairs.
     *
     * @return string[]
     */
    public function getTypes()
    {
        return $this->handlers;
    }

    /**
     * Return specific handler name for configured type name
     *
     * @param string $typeName
     * @return string
     * @throws GraphQlInputException
     */
    public function getHandlerNameForType(string $typeName)
    {
        if (!isset($this->handlers[$typeName])) {
            throw new GraphQlInputException(__('The %1 type has not been defined or configured', $typeName));
        }

        return $this->handlers[$typeName];
    }
}
