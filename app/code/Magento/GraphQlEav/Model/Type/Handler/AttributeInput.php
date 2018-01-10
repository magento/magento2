<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GraphQlEav\Model\Type\Handler;

use Magento\GraphQl\Model\Type\HandlerInterface;
use Magento\Framework\GraphQl\TypeFactory;
use Magento\GraphQl\Model\Type\Handler\Pool;

/**
 * Defines input type for attributes in ['attribute_code' => 'value', 'entity_type' => 'value'] format
 */
class AttributeInput implements HandlerInterface
{
    const ATTRIBUTE_INPUT_TYPE_NAME = 'AttributeInput';

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
        return $this->typeFactory->createInputObject(
            [
                'name' => self::ATTRIBUTE_INPUT_TYPE_NAME,
                'fields' => [
                    'attribute_code' => $this->typePool->getType(Pool::TYPE_STRING),
                    'entity_type' => $this->typePool->getType(Pool::TYPE_STRING)
                ],
            ]
        );
    }
}
