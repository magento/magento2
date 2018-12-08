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
 * {@inheritdoc}
 */
class EnumFactory implements ConfigElementFactoryInterface
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var EnumValueFactory
     */
    private $enumValueFactory;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param EnumValueFactory $enumValueFactory
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        EnumValueFactory $enumValueFactory
    ) {
        $this->objectManager = $objectManager;
        $this->enumValueFactory = $enumValueFactory;
    }

    /**
     * Create an enum based off a configured enum type. Name and values required.
     *
     * @param string $name
     * @param EnumValue[] $values
     * @param string $description
     * @return Enum
     */
    public function create(string $name, array $values, string $description = ''): Enum
    {
        return $this->objectManager->create(
            Enum::class,
            [
                'name' => $name,
                'values' => $values,
                'description' => $description
            ]
        );
    }

    /**
     * Instantiate an object representing 'enum' GraphQL config element.
     *
     * @param array $data
     * @return ConfigElementInterface
     */
    public function createFromConfigData(array $data): ConfigElementInterface
    {
        $values = [];
        foreach ($data['items'] as $item) {
            $values[$item['_value']] = $this->enumValueFactory->create(
                $item['name'],
                $item['_value'],
                isset($item['description']) ? $item['description'] : ''
            );
        }
        return $this->create(
            $data['name'],
            $values,
            isset($data['description']) ? $data['description'] : ''
        );
    }
}
