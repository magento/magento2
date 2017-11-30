<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GraphQl\Model\Type\Handler;

use Magento\Framework\GraphQl\Type\Definition\InputObjectType;
use Magento\GraphQl\Model\Type\HandlerInterface;

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
     * @param Pool $typePool
     */
    public function __construct(Pool $typePool)
    {
        $this->typePool = $typePool;
    }

    /**
     * {@inheritDoc}
     */
    public function getType()
    {
        $reflector = new \ReflectionClass($this);

        return new InputObjectType(
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
