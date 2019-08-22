<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\GraphQl\Config\Element;

use Magento\Framework\ObjectManagerInterface;

/**
 * Factory for config elements of 'enum value' type.
 */
class EnumValueFactory
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
     * Create an enum value object based off a configured EnumType's data. Name and value required.
     *
     * @param string $name
     * @param string $value
     * @param string $description
     * @return EnumValue
     */
    public function create(string $name, string $value, string $description = ''): EnumValue
    {
        return $this->objectManager->create(
            EnumValue::class,
            [
                'name' => $name,
                'value' => $value,
                'description' => $description
            ]
        );
    }
}
