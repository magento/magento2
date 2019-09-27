<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Dto\Mock;

use Magento\Framework\Api\ExtensionAttribute\Config;
use Magento\Framework\Dto\DtoConfig;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Configure test dtos for integration tests
 */
class ConfigureTestDtos
{
    /**
     * @inheritDoc
     */
    public static function execute(): void
    {
        $config = Bootstrap::getObjectManager()->get(DtoConfig::class);

        self::addImmutableDto($config);
        self::addImmutableDtoWithExtensionAttributes($config);
        self::addImmutableDto2($config);
        self::addImmutableNestedDto($config);
        self::addMutableDto($config);
    }

    /**
     * @param DtoConfig $config
     */
    private static function addImmutableDto(DtoConfig $config): void
    {
        self::addDto(
            $config,
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

    /**
     * @param DtoConfig $config
     */
    private static function addImmutableDtoWithExtensionAttributes(DtoConfig $config): void
    {
        /** @var Config $config */
        $eaConfig = Bootstrap::getObjectManager()->get(Config::class);
        $eaConfig->merge(['Magento\Framework\Dto\Mock\ImmutableDtoWithExtensionAttributes' => [
            'attribute1' => [
                'type' => 'string',
                'resourceRefs' => '',
                'join' => null
            ],
            'attribute2' => [
                'type' => 'string',
                'resourceRefs' => '',
                'join' => null
            ],
            'attribute3' => [
                'type' => 'Magento\Framework\Dto\Mock\ImmutableDtoInterface[]',
                'resourceRefs' => '',
                'join' => null
            ]
        ]]);

        self::addDto(
            $config,
            'Magento\Framework\Dto\Mock\ImmutableDtoWithExtensionAttributes',
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

    /**
     * @param DtoConfig $config
     */
    private static function addImmutableDto2(DtoConfig $config): void
    {
        self::addDto(
            $config,
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

    /**
     * @param DtoConfig $config
     */
    private static function addMutableDto(DtoConfig $config): void
    {
        self::addDto(
            $config,
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

    /**
     * @param DtoConfig $config
     */
    private static function addImmutableNestedDto(DtoConfig $config): void
    {
        self::addDto(
            $config,
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
     * @param DtoConfig $config
     * @param string $className
     * @param bool $mutable
     * @param array $properties
     */
    private static function addDto(
        DtoConfig $config,
        string $className,
        bool $mutable,
        array $properties
    ): void {
        $interfaceName = $className . 'Interface';

        $config->merge([
            'interface' => [
                $interfaceName => [
                    'mutable' => $mutable,
                    'type' => 'class',
                    'interface' => $interfaceName,
                    'properties' => $properties
                ]
            ]
        ]);

        $config->merge([
            'class' => [
                $className => $interfaceName
            ]
        ]);
    }
}
