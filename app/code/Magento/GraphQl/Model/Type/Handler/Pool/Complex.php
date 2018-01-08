<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GraphQl\Model\Type\Handler\Pool;

use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Type\Definition\TypeInterface;
use Magento\GraphQl\Model\Type\HandlerConfig;
use Magento\GraphQl\Model\Type\HandlerFactory;

/**
 * Retrieves complex type definitions from their handlers and registers them to the pool.
 */
class Complex
{
    /**
     * @var HandlerConfig
     */
    private $typeConfig;

    /**
     * @var HandlerFactory
     */
    private $typeHandlerFactory;

    /**
     * @param HandlerConfig $typeConfig
     * @param HandlerFactory $typeHandlerFactory
     */
    public function __construct(HandlerConfig $typeConfig, HandlerFactory $typeHandlerFactory)
    {
        $this->typeConfig = $typeConfig;
        $this->typeHandlerFactory = $typeHandlerFactory;
    }

    /**
     * Retrieve type's configuration based off name
     *
     * @param string $typeName
     * @return TypeInterface
     * @throws \LogicException Type Handler could not be found, and type does not exist in registry
     * @throws GraphQlInputException
     */
    public function getComplexType(string $typeName)
    {
        $typeHandlerName = $this->typeConfig->getHandlerNameForType($typeName);
        $typeHandler = $this->typeHandlerFactory->create($typeHandlerName);

        return $typeHandler->getType();
    }
}
