<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\GraphQl\Config\Data;

use Magento\Framework\GraphQl\Config\Data\Enum\Value;
use Magento\Framework\ObjectManagerInterface;

/**
 * Create structured objects representing various components of a GraphQL schema from configured values.
 */
class DataFactory
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(
        ObjectManagerInterface $objectManager
    ) {
        $this->objectManager = $objectManager;
    }

    /**
     * Create a field object from a configured array with optional arguments.
     *
     * Field data must contain name and type. Other values are optional and include required, itemType, description,
     * and resolver. Arguments array must be in the format of [$argumentData['name'] => $argumentData].
     *
     * @param array $fieldData
     * @param array $arguments
     * @return Field
     */
    public function createField(
        array $fieldData,
        array $arguments = []
    ) : Field {
        return $this->objectManager->create(
            Field::class,
            [
                'name' => $fieldData['name'],
                'type' => $fieldData['type'],
                'required' => isset($fieldData['required']) ? $fieldData['required'] : false,
                'itemType' => isset($fieldData['itemType']) ? $fieldData['itemType'] : "",
                'resolver' => isset($fieldData['resolver']) ? $fieldData['resolver'] : "",
                'description' => isset($fieldData['description']) ? $fieldData['description'] : "",
                'arguments' => $arguments
            ]
        );
    }

    /**
     * Create an argument object based off a configured Output/InputInterface's data.
     *
     * Argument data must contain name and type. Other values are optional and include baseType, itemType, description,
     * required, and itemsRequired.
     *
     * @param array $argumentData
     * @return Argument
     */
    public function createArgument(
        array $argumentData
    ) : Argument {
        return $this->objectManager->create(
            Argument::class,
            [
                'name' => $argumentData['name'],
                'type' => $argumentData['type'],
                'baseType' => isset($argumentData['baseType']) ? $argumentData['baseType'] : "",
                'itemType' => isset($argumentData['itemType']) ? $argumentData['itemType'] : "",
                'description' => isset($argumentData['description']) ? $argumentData['description'] : "",
                'required' => isset($argumentData['required']) ? $argumentData['required'] : false,
                'itemsRequired' => isset($argumentData['itemsRequired']) ? $argumentData['itemsRequired'] : false
            ]
        );
    }

    /**
     * Create type object based off array of configured GraphQL Output/InputType data.
     *
     * Type data must contain name and the type's fields. Optional data includes 'implements' (i.e. the interfaces
     * implemented by the types), and description. An InputType cannot implement an interface.
     *
     * @param array $typeData
     * @param array $fields
     * @return Type
     */
    public function createType(
        array $typeData,
        array $fields
    ) : Type {
        return $this->objectManager->create(
            Type::class,
            [
                'name' => $typeData['name'],
                'fields' => $fields,
                'interfaces' => isset($typeData['implements']) ? $typeData['implements'] : [],
                'description' => isset($typeData['description']) ? $typeData['description'] : ""
            ]
        );
    }

    /**
     * Create interface object based off array of configured GraphQL Output/InputInterface.
     *
     * Interface data must contain name, type resolver, and field definitions. The type resolver should point to an
     * implementation of the TypeResolverInterface that decides what concrete GraphQL type to output. Description is
     * the only optional field.
     *
     * @param array $interfaceData
     * @param array $fields
     * @return InterfaceType
     */
    public function createInterface(
        array $interfaceData,
        array $fields
    ) : InterfaceType {
        return $this->objectManager->create(
            InterfaceType::class,
            [
                'name' => $interfaceData['name'],
                'typeResolver' => $interfaceData['typeResolver'],
                'fields' => $fields,
                'description' => isset($interfaceData['description']) ? $interfaceData['description'] : ""
            ]
        );
    }

    /**
     * Create an enum value object based off a configured EnumType's data. Name and value required.
     *
     * @param string $name
     * @param string $value
     * @return Value
     */
    public function createValue(string $name, string $value): Value
    {
        return $this->objectManager->create(
            Value::class,
            [
                'name' => $name,
                'value' => $value
            ]
        );
    }

    /**
     * Create an enum based off a configured enum type. Name and values required.
     *
     * Values must be instantiated value objects.
     *
     * @param string $name
     * @param array $values
     * @return Enum
     */
    public function createEnum(string $name, array $values): Enum
    {
        return $this->objectManager->create(
            Enum::class,
            [
                'name' => $name,
                'values' => $values
            ]
        );
    }
}
