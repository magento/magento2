<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GraphQlCatalog\Model\Type\Handler;

use Magento\GraphQl\Model\Type\HandlerInterface;
use Magento\Framework\GraphQl\TypeFactory;
use Magento\GraphQl\Model\Type\Handler\Pool;

/**
 * Define SimpleProduct GraphQL type
 */
class SimpleProduct implements HandlerInterface
{
    const SIMPLE_PRODUCT_TYPE_NAME = 'SimpleProduct';

    /**
     * @var Pool
     */
    private $typePool;

    /**
     * @var TypeFactory
     */
    private $typeFactory;

    /**
     * @param Pool $typePool
     * @param TypeFactory $typeFactory
     */
    public function __construct(Pool $typePool, TypeFactory $typeFactory)
    {
        $this->typePool = $typePool;
        $this->typeFactory = $typeFactory;
    }

    /**
     * {@inheritDoc}
     */
    public function getType()
    {
        $fields = [];
        $interface = $this->typePool->getType(Product::PRODUCT_TYPE_NAME);
        $fields = array_merge($fields, $interface->config['fields']);
        return $this->typeFactory->createObject(
            [
                'name' => self::SIMPLE_PRODUCT_TYPE_NAME,
                'fields' => $fields,
                'interfaces' => [$interface]
            ]
        );
    }
}
