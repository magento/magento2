<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\GraphQl\Schema;

use Magento\Framework\GraphQl\Schema\Type\ObjectType;
use Magento\Framework\GraphQl\Schema\Type\InterfaceType;
use Magento\Framework\GraphQl\Schema\Type\InputObjectType;
use Magento\Framework\GraphQl\Schema\Type\EnumType;
use Magento\Framework\GraphQl\Schema\Type\ListOfType;
use Magento\Framework\GraphQl\Schema\Type\NonNull;
use Magento\Framework\GraphQl\Schema\TypeInterface;

/**
 * Factory for @see TypeInterface implementations
 */
class TypeFactory
{
    /**
     * Create an object type
     *
     * @param array $config
     * @return ObjectType
     */
    public function createObject(array $config) : ObjectType
    {
        return new ObjectType($config);
    }

    /**
     * Create an interface type
     *
     * @param array $config
     * @return InterfaceType
     */
    public function createInterface(array $config) : InterfaceType
    {
        return new InterfaceType($config);
    }

    /**
     * Create an input object type
     *
     * @param array $config
     * @return InputObjectType
     */
    public function createInputObject(array $config) : InputObjectType
    {
        return new InputObjectType($config);
    }

    /**
     * Create an enum object type
     *
     * @param array $config
     * @return EnumType
     */
    public function createEnum(array $config) : EnumType
    {
        return new EnumType($config);
    }

    /**
     * Create an list array type
     *
     * @param TypeInterface $definedType
     * @return ListOfType
     */
    public function createList(TypeInterface $definedType) : ListOfType
    {
        return new ListOfType($definedType);
    }

    /**
     * Create a non null type
     *
     * @param TypeInterface $definedType
     * @return NonNull
     */
    public function createNonNull(TypeInterface $definedType) : NonNull
    {
        return new NonNull($definedType);
    }
}
