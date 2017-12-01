<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GraphQl\Model\Type\Handler;

use Magento\Framework\GraphQl\Type\Definition\ListOfType;
use Magento\Framework\GraphQl\Type\Definition\Type;
use Magento\Framework\GraphQl\Type\Definition\ObjectType;
use Magento\GraphQl\Model\Type\HandlerInterface;
use Magento\Framework\GraphQl\Type\TypeFactory;

/**
 * Define GraphQL type for search result of Products
 */
class Products implements HandlerInterface
{
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
     * {@inheritdoc}
     */
    public function getType()
    {
        $reflector = new \ReflectionClass($this);
        return $this->typeFactory->createObject(
            [
                'name' => $reflector->getShortName(),
                'fields' => $this->getFields(),
            ]
        );
    }

    /**
     * Retrieve the result fields
     *
     * @return Type[]
     */
    private function getFields()
    {
        $fields = [
            'items' => $this->typeFactory->createList($this->typePool->getType('Product')),
            'page_info' => $this->typePool->getType('SearchResultPageInfo'),
            'total_count' => $this->typePool->getType('Int')
        ];
        return $fields;
    }
}
