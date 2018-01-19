<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\GraphQl;

use Magento\Framework\GraphQl\Type\Definition\ObjectType;
use Magento\Framework\GraphQl\Type\Definition\InterfaceType;
use Magento\Framework\GraphQl\Type\Definition\InputObjectType;
use Magento\Framework\GraphQl\Type\Definition\EnumType;
use Magento\Framework\GraphQl\Type\Definition\ListOfType;
use Magento\Framework\GraphQl\Type\Definition\NonNull;
use Magento\Framework\GraphQl\Type\Definition\TypeInterface;
use GraphQL\Type\Definition\Type;

/**
 * Factory for @see TypeInterface implementations
 */
class TypeFactory
{
    /**
     * @param string $type
     * @return Type|null
     */
    public function createScalar(string $type)
    {
        $scalarTypes = Type::getInternalTypes();
        return isset($scalarTypes[$type]) ? $scalarTypes[$type] : null;
    }

    /**
     * @param array $config
     * @return TypeInterface
     */
    public function createObject(array $config)
    {
        return new ObjectType($config);
    }

    /**
     * @param array $config
     * @return TypeInterface
     */
    public function createInterface(array $config)
    {
        return new InterfaceType($config);
    }

    /**
     * @param array $config
     * @return TypeInterface
     */
    public function createInputObject(array $config)
    {
        return new InputObjectType($config);
    }

    /**
     * @param array $config
     * @return TypeInterface
     */
    public function createEnum(array $config)
    {
        return new EnumType($config);
    }

    /**
     * @param TypeInterface|Type $definedType
     * @return TypeInterface
     */
    public function createList(Type $definedType)
    {
        return new ListOfType($definedType);
    }

    /**
     * @param TypeInterface|Type $definedType
     * @return TypeInterface
     */
    public function createNonNull(Type $definedType)
    {
        return new NonNull($definedType);
    }
}
