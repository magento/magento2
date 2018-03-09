<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\GraphQl\Type\Definition;

/**
 * Wrapper for GraphQl ScalarType
 */
class ScalarTypes
{
    /**
     * @var string[]
     */
    private $scalarTypes = [
        'Boolean' => BooleanType::class,
        'Float' => FloatType::class,
        'ID' => IdType::class,
        'Int' => IntType::class,
        'String' => StringType::class
    ];

    /**
     * @var TypeInterface
     */
    private $scalarTypesInstances = [

    ];

    /**
     * @param string $typeName
     * @return bool
     */
    public function hasScalarTypeClass(string $typeName) : bool
    {
        return isset($this->scalarTypes[$typeName]) ? true : false;
    }

    /**
     * @param string $typeName
     * @return string|null
     * @throws \LogicException
     */
    public function getScalarTypeClass(string $typeName) : string
    {
        if ($this->hasScalarTypeClass($typeName)) {
            return $this->scalarTypes[$typeName];
        }
        throw new \LogicException(sprintf('Scalar type class with name %s doesn\'t exist', $typeName));
    }

    /**
     * @param string $typeName
     * @return TypeInterface|null
     * @throws \LogicException
     */
    public function getScalarTypeInstance(string $typeName) : TypeInterface
    {
        if ($this->hasScalarTypeClass($typeName)) {
            if (!isset($this->scalarTypesInstances[$typeName])) {
                $scalarClassName = $this->getScalarTypeClass($typeName);
                $this->scalarTypesInstances[$typeName] = new $scalarClassName();
            }
            return $this->scalarTypesInstances[$typeName];
        } else {
            throw new \LogicException(sprintf('Scalar type %s doesn\'t exist', $typeName));
        }
    }
}
