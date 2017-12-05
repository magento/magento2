<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GraphQlCatalog\Model\Type\Handler;

use Magento\GraphQl\Model\Type\HandlerInterface;
use Magento\Framework\GraphQl\Type\TypeFactory;
use Magento\GraphQl\Model\Type\Handler\Pool;

/**
 * Define ProductGroupSearchCriteria's GraphQL type
 */
class ProductGroupSearchCriteria implements HandlerInterface
{
    /**
     * @var Pool
     */
    private $pool;

    /**
     * @var TypeFactory
     */
    private $typeFactory;

    /**
     * @param Pool $pool
     * @param TypeFactory $typeFactory
     */
    public function __construct(Pool $pool, TypeFactory $typeFactory)
    {
        $this->pool = $pool;
        $this->typeFactory = $typeFactory;
    }

    /**
     * {@inheritDoc}
     */
    public function getType()
    {
        $reflector = new \ReflectionClass($this);
        return $this->typeFactory->createInputObject(
            [
                'name' => $reflector->getShortName(),
                'fields' => ['and' => $this->pool->getType('ProductAttributeSearchCriteria')]
            ]
        );
    }
}
