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
 * Factory for config elements of 'interface' type.
 */
class InterfaceFactory implements ConfigElementFactoryInterface
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
     * Instantiate an object representing 'interface' GraphQL config element.
     */
    public function createFromConfigData(array $data): ConfigElementInterface
    {
        $fields = [];
        foreach ($data['fields'] as $field) {
            $arguments = [];
            foreach ($field['arguments'] as $argument) {
                $arguments[$argument['name']] = $this->argumentFactory->createFromConfigData($argument);
            }
            $fields[$field['name']] = $this->fieldFactory->createFromConfigData($field, $arguments);
        }
        return $this->create($data, $fields);
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
    public function create(
        array $interfaceData,
        array $fields
    ) : InterfaceType {
        return $this->objectManager->create(
            InterfaceType::class,
            [
                'name' => $interfaceData['name'],
                'typeResolver' => $interfaceData['typeResolver'],
                'fields' => $fields,
                'description' => isset($interfaceData['description']) ? $interfaceData['description'] : ''
            ]
        );
    }
}
