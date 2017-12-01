<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GraphQl\Model\Type\Handler;

use Magento\Framework\GraphQl\Type\Definition\ListOfType;
use Magento\Framework\GraphQl\Type\Definition\ObjectType;
use Magento\GraphQl\Model\Type\HandlerInterface;
use Magento\Framework\GraphQl\Type\TypeFactory;

/**
 * Defines type information for custom attribute metadata
 */
class CustomAttributeMetadata implements HandlerInterface
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

        return $this->typeFactory->createObject(
            [
                'name' => $reflector->getShortName(),
                'fields' => $this->getFields()
            ]
        );
    }

    /**
     * Get and register array of fields for CustomAttributeMetadata type
     *
     * @return array
     */
    private function getFields(): array
    {
        if (!$this->typePool->isTypeRegistered('Attribute')) {
            $attributeType = $this->typeFactory->createObject([
                'name' => 'Attribute',
                'fields' => [
                    'attribute_code' => $this->typePool->getType('String'),
                    'entity_type' => $this->typePool->getType('String'),
                    'attribute_type' => $this->typePool->getType('String')
                ]
            ]);
            $this->typePool->registerType($attributeType);
        }

        return ['items' => $this->typeFactory->createList($this->typePool->getType('Attribute'))];
    }
}
