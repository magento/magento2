<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types = 1);

namespace Magento\Framework\GraphQl;

use Magento\Framework\GraphQl\Type\Definition\ObjectType;
use Magento\Framework\GraphQl\Type\Definition\InterfaceType;
use Magento\Framework\GraphQl\Type\Definition\InputObjectType;
use Magento\Framework\GraphQl\Type\Definition\EnumType;
use Magento\Framework\GraphQl\Type\Definition\ListOfType;
use Magento\Framework\GraphQl\Type\Definition\NonNull;
use Magento\Framework\GraphQl\Type\Definition\TypeInterface;
use Magento\Framework\GraphQl\Type\Definition\ScalarTypes;

/**
 * Factory for @see TypeInterface implementations
 */
class TypeFactory
{
    /**
     * @var ScalarTypes
     */
    private $scalarTypes;

    /**
     * TypeFactory constructor.
     * @param ScalarTypes $scalarTypes
     */
    public function __construct(ScalarTypes $scalarTypes)
    {
        $this->scalarTypes = $scalarTypes;
    }

    /**
     * Get instance of a scalar type as singleton
     *
     * @param string $type
     * @return TypeInterface|null
     */
    public function getScalar(string $type) : ?TypeInterface
    {
        if ($this->scalarTypes->hasScalarTypeClass($type)) {
            return $this->scalarTypes->getScalarTypeInstance($type);
        }
        return null;
    }

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
