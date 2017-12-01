<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\GraphQl\Type;

use Magento\Framework\GraphQl\Type\Definition\TypeInterface;
use GraphQL\Type\Definition\Type;

class TypeFactory
{
    /**
     * @param string $type
     * @return Type
     */
    public function createScalar(string $type)
    {
        $scalarTypes = Type::getInternalTypes();
        return $scalarTypes[$type];
    }

    /**
     * @param array $config
     * @return TypeInterface
     */
    public function createObject(array $config)
    {
        return new Definition\ObjectType($config);
    }

    /**
     * @param array $config
     * @return TypeInterface
     */
    public function createInterface(array $config)
    {
        return new Definition\InterfaceType($config);
    }

    /**
     * @param array $config
     * @return TypeInterface
     */
    public function createInputObject(array $config)
    {
        return new Definition\InputObjectType($config);
    }

    /**
     * @param array $config
     * @return TypeInterface
     */
    public function createEnum(array $config)
    {
        return new Definition\EnumType($config);
    }

    /**
     * @param Type $definedType
     * @return TypeInterface
     */
    public function createList(Type $definedType)
    {
        return new Definition\ListOfType($definedType);
    }

    /**
     * @param Type $definedType
     * @return TypeInterface
     */
    public function createNonNull(Type $definedType)
    {
        return new Definition\NonNull($definedType);
    }
}
