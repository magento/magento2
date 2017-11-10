<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GraphQl\Model\Type\Handler;

use GraphQL\Type\Definition\ListOfType;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\ObjectType;
use Magento\GraphQl\Model\Type\HandlerInterface;

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
     * @param Pool $typePool
     */
    public function __construct(Pool $typePool)
    {
        $this->typePool = $typePool;
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        $reflector = new \ReflectionClass($this);
        return new ObjectType(
            [
                'name' => $reflector->getShortName(),
                'fields' => $this->getFields(),
            ]
        );
    }

    /**
     * Retrieve Result fields
     *
     * @return Type[]
     */
    private function getFields()
    {
        $fields = [
            'items' => new ListOfType($this->typePool->getType('Product')),
            'page_info' => $this->typePool->getType('SearchResultPageInfo'),
            'total_count' => $this->typePool->getType('Int')
        ];
        return $fields;
    }
}
