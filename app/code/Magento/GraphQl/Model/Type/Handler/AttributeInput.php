<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GraphQl\Model\Type\Handler;

use Magento\Framework\GraphQl\Type\Definition\InputObjectType;
use Magento\GraphQl\Model\Type\HandlerInterface;
use Magento\Framework\GraphQl\Type\TypeFactory;

/**
 * Defines input type for attributes in ['attribute_code' => 'value', 'entity_type' => 'value'] format
 */
class AttributeInput implements HandlerInterface
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
     * {@inheritDoc}
     */
    public function getType()
    {
        $reflector = new \ReflectionClass($this);

        return $this->typeFactory->createInputObject(
            [
                'name' => $reflector->getShortName(),
                'fields' => [
                    'attribute_code' => $this->typePool->getType('String'),
                    'entity_type' => $this->typePool->getType('String')
                ],
            ]
        );
    }
}
