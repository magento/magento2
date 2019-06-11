<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Dto\Mock;

use Magento\Framework\Dto\DtoConfig;

class MockDtoConfig extends DtoConfig
{
    /**
     * @inheritDoc
     */
    protected function initData()
    {
        parent::initData();

        $this->addImmutableDto();
        $this->addImmutableDto2();
        $this->addImmutableNestedDto();
        $this->addMutableDto();
    }

    private function addImmutableDto(): void
    {
        $this->addDto(
            'Magento\Framework\Dto\Mock\ImmutableDto',
            false,
            [
                'prop1' => [
                    'type' => 'int',
                    'nullable' => false,
                    'optional' => false
                ],
                'prop2' => [
                    'type' => 'string',
                    'nullable' => false,
                    'optional' => true
                ],
                'prop3' => [
                    'type' => 'array',
                    'nullable' => true,
                    'optional' => false
                ],
                'prop4' => [
                    'type' => 'int[]',
                    'nullable' => true,
                    'optional' => true
                ]
            ]
        );
    }

    private function addImmutableDto2(): void
    {
        $this->addDto(
            'Magento\Framework\Dto\Mock\ImmutableDtoTwo',
            false,
            [
                'propOne' => [
                    'type' => 'int',
                    'nullable' => false,
                    'optional' => false
                ],
                'propTwo' => [
                    'type' => 'string',
                    'nullable' => false,
                    'optional' => true
                ],
                'propThree' => [
                    'type' => 'array',
                    'nullable' => true,
                    'optional' => false
                ],
                'propFour' => [
                    'type' => 'int[]',
                    'nullable' => true,
                    'optional' => true
                ]
            ]
        );
    }

    private function addMutableDto(): void
    {
        $this->addDto(
            'Magento\Framework\Dto\Mock\MutableDto',
            true,
            [
                'prop1' => [
                    'type' => 'int',
                    'nullable' => false,
                    'optional' => false
                ],
                'prop2' => [
                    'type' => 'string',
                    'nullable' => false,
                    'optional' => true
                ],
                'prop3' => [
                    'type' => 'array',
                    'nullable' => true,
                    'optional' => false
                ],
                'prop4' => [
                    'type' => 'int[]',
                    'nullable' => true,
                    'optional' => true
                ]
            ]
        );
    }

    private function addImmutableNestedDto(): void
    {
        $this->addDto(
            'Magento\Framework\Dto\Mock\ImmutableNestedDto',
            false,
            [
                'id' => [
                    'type' => 'string',
                    'nullable' => false,
                    'optional' => false
                ],
                'testDto1' => [
                    'type' => 'Magento\Framework\Dto\Mock\ImmutableDto',
                    'nullable' => false,
                    'optional' => false
                ],
                'testDto2' => [
                    'type' => 'Magento\Framework\Dto\Mock\ImmutableDto',
                    'nullable' => false,
                    'optional' => false
                ],
                'testDtoArray' => [
                    'type' => 'Magento\Framework\Dto\Mock\ImmutableDto[]',
                    'nullable' => false,
                    'optional' => false
                ]
            ]
        );
    }

    /**
     * @param string $className
     * @param bool $mutable
     * @param array $properties
     */
    private function addDto(
        string $className,
        bool $mutable,
        array $properties
    ): void {
        $interfaceName = $className . 'Interface';

        $this->_data[$className] = [
            'mutable' => $mutable,
            'type' => 'class',
            'interface' => $interfaceName,
            'properties' => $properties
        ];
        $this->_data[$interfaceName] = [
            'mutable' => $mutable,
            'type' => 'interface',
            'class' => $className,
            'properties' => $properties
        ];
    }
}
