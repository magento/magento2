<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\GraphQl\Config\Element;

use Magento\Framework\GraphQl\Config\ConfigElementFactoryInterface;
use Magento\Framework\GraphQl\Config\ConfigElementInterface;
use Magento\Framework\ObjectManagerInterface;

/**
 * Factory for config elements of 'type' type.
 */
class TypeFactory implements ConfigElementFactoryInterface
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var ArgumentFactory
     */
    private $argumentFactory;

    /**
     * @var FieldFactory
     */
    private $fieldFactory;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param ArgumentFactory $argumentFactory
     * @param FieldFactory $fieldFactory
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        ArgumentFactory $argumentFactory,
        FieldFactory $fieldFactory
    ) {
        $this->objectManager = $objectManager;
        $this->argumentFactory = $argumentFactory;
        $this->fieldFactory = $fieldFactory;
    }

    /**
     * Instantiate an object representing 'type' GraphQL config element.
     *
     * @param array $data
     * @return ConfigElementInterface
     */
    public function createFromConfigData(array $data): ConfigElementInterface
    {
        $fields = [];
        $data['fields'] = isset($data['fields']) ? $data['fields'] : [];
        foreach ($data['fields'] as $field) {
            $arguments = [];
            foreach ($field['arguments'] as $argument) {
                $arguments[$argument['name']] = $this->argumentFactory->createFromConfigData($argument);
            }
            $fields[$field['name']] = $this->fieldFactory->createFromConfigData(
                $field,
                $arguments
            );
        }
        return $this->create(
            $data,
            $fields
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
    public function create(
        array $typeData,
        array $fields
    ) : Type {
        return $this->objectManager->create(
            Type::class,
            [
                'name' => $typeData['name'],
                'fields' => $fields,
                'interfaces' => isset($typeData['implements']) ? $typeData['implements'] : [],
                'description' => isset($typeData['description']) ? $typeData['description'] : ''
            ]
        );
    }
}
