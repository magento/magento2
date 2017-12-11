<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GraphQlEav\Model\Type\Handler;

use Magento\GraphQl\Model\Type\HandlerInterface;
use Magento\Framework\GraphQl\Type\TypeFactory;
use Magento\Framework\GraphQl\Type\Definition\TypeInterface;
use Magento\GraphQl\Model\Type\Handler\Pool;

/**
 * Defines type information for custom attribute metadata
 */
class CustomAttributeMetadata implements HandlerInterface
{
    const CUSTOM_ATTRIBUTE_METADATA_TYPE_NAME = 'CustomAttributeMetadata';

    const ATTRIBUTE_TYPE_NAME = 'Attribute';

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
        return $this->typeFactory->createObject(
            [
                'name' => self::CUSTOM_ATTRIBUTE_METADATA_TYPE_NAME,
                'fields' => $this->getFields()
            ]
        );
    }

    /**
     * Get and register array of fields for CustomAttributeMetadata type
     *
     * @return TypeInterface[]
     */
    private function getFields(): array
    {
        if (!$this->typePool->isTypeRegistered(self::ATTRIBUTE_TYPE_NAME)) {
            $attributeType = $this->typeFactory->createObject([
                'name' => self::ATTRIBUTE_TYPE_NAME,
                'fields' => [
                    'attribute_code' => $this->typePool->getType('String'),
                    'entity_type' => $this->typePool->getType('String'),
                    'attribute_type' => $this->typePool->getType('String')
                ]
            ]);
            $this->typePool->registerType($attributeType);
        }

        return ['items' => $this->typeFactory->createList($this->typePool->getType(self::ATTRIBUTE_TYPE_NAME))];
    }
}
