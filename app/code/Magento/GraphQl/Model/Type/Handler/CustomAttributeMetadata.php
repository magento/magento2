<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GraphQl\Model\Type\Handler;

use Magento\Framework\GraphQl\Type\Definition\ListOfType;
use Magento\Framework\GraphQl\Type\Definition\ObjectType;
use Magento\GraphQl\Model\Type\HandlerInterface;

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

        return new ObjectType(
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
            $attributeType = new ObjectType([
                'name' => 'Attribute',
                'fields' => [
                    'attribute_code' => $this->typePool->getType('String'),
                    'entity_type' => $this->typePool->getType('String'),
                    'attribute_type' => $this->typePool->getType('String')
                ]
            ]);
            $this->typePool->registerType($attributeType);
        }

        return ['items' => new ListOfType($this->typePool->getType('Attribute'))];
    }
}
