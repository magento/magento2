<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\ObjectManager\Test\Unit\Factory;

use \Magento\Framework\ObjectManager\Factory\Compiled;

class CompiledTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Object manager
     *
     * @var \Magento\Framework\ObjectManagerInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManager;

    /**
     * Object manager config
     *
     * @var \Magento\Framework\ObjectManager\ConfigInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $config;

    /**
     * @var Compiled
     */
    protected $factory;

    /**
     * @var array
     */
    private $sharedInstances;

    protected function setUp()
    {
        $this->objectManager = $this->getMockBuilder('Magento\Framework\ObjectManagerInterface')
            ->setMethods([])
            ->getMock();

        $this->config = $this->getMockBuilder('Magento\Framework\ObjectManager\ConfigInterface')
            ->setMethods([])
            ->getMock();

        $this->sharedInstances = [];
        $this->factory = new Compiled($this->config, $this->sharedInstances, []);
        $this->factory->setObjectManager($this->objectManager);
    }

    public function testCreateSimple()
    {
        $expectedConfig = $this->getSimpleConfig();

        $requestedType = 'requestedType';
        $type = 'Magento\Framework\ObjectManager\Test\Unit\Factory\Fixture\Compiled\SimpleClassTesting';
        $sharedType = 'Magento\Framework\ObjectManager\Test\Unit\Factory\Fixture\Compiled\DependencySharedTesting';
        $nonSharedType = 'Magento\Framework\ObjectManager\Test\Unit\Factory\Fixture\Compiled\DependencyTesting';

        $this->config->expects($this->any())
            ->method('getArguments')
            ->willReturnMap(
                [
                    [$requestedType, $expectedConfig],
                    [$sharedType, null],
                    [$nonSharedType, null]
                ]
            );
        $this->config->expects($this->any())
            ->method('getInstanceType')
            ->willReturnMap(
                [
                    [$requestedType, $type],
                    [$sharedType, $sharedType],
                    [$nonSharedType, $nonSharedType]
                ]
            );

        $this->factory->setArguments(
            [
                'globalValue' => 'GLOBAL_ARGUMENT',
            ]
        );

        /** @var \Magento\Framework\ObjectManager\Test\Unit\Factory\Fixture\Compiled\SimpleClassTesting $result */
        $result = $this->factory->create($requestedType, []);

        $this->assertInstanceOf(
            'Magento\Framework\ObjectManager\Test\Unit\Factory\Fixture\Compiled\SimpleClassTesting',
            $result
        );
        $this->assertInstanceOf($sharedType, $result->getSharedDependency());
        $this->assertInstanceOf($nonSharedType, $result->getNonSharedDependency());
        $this->assertEquals('value', $result->getValue());
        $this->assertEquals(['default_value1', 'default_value2'], $result->getValueArray());
        $this->assertEquals('GLOBAL_ARGUMENT', $result->getGlobalValue());
        $this->assertNull($result->getNullValue());
    }

    public function testCreateSimpleConfiguredArguments()
    {
        $expectedConfig = $this->getSimpleNestedConfig();

        $type = 'Magento\Framework\ObjectManager\Test\Unit\Factory\Fixture\Compiled\SimpleClassTesting';
        $requestedType = 'requestedType';
        $sharedType = 'Magento\Framework\ObjectManager\Test\Unit\Factory\Fixture\Compiled\DependencySharedTesting';
        $nonSharedType = 'Magento\Framework\ObjectManager\Test\Unit\Factory\Fixture\Compiled\DependencyTesting';

        $this->config->expects($this->any())
            ->method('getArguments')
            ->willReturnMap(
                [
                    [$requestedType, $expectedConfig],
                    [$sharedType, null],
                    [$nonSharedType, null]
                ]
            );
        $this->config->expects($this->any())
            ->method('getInstanceType')
            ->willReturnMap(
                [
                    [$requestedType, $type],
                    [$sharedType, $sharedType],
                    [$nonSharedType, $nonSharedType]
                ]
            );

        $this->factory->setArguments(
            [
                'array_global_existing_argument' => 'GLOBAL_ARGUMENT',
                'globalValue' => 'GLOBAL_ARGUMENT',
            ]
        );

        /** @var \Magento\Framework\ObjectManager\Test\Unit\Factory\Fixture\Compiled\SimpleClassTesting $result */
        $result = $this->factory->create($requestedType, []);

        $this->assertInstanceOf(
            'Magento\Framework\ObjectManager\Test\Unit\Factory\Fixture\Compiled\SimpleClassTesting',
            $result
        );
        $this->assertInstanceOf($sharedType, $result->getSharedDependency());
        $this->assertInstanceOf($nonSharedType, $result->getNonSharedDependency());
        $this->assertEquals('value', $result->getValue());
        $this->assertEquals(
            [
                'array_value' => 'value',
                'array_configured_instance' => new $sharedType,
                'array_configured_array' => [
                    'array_array_value' => 'value',
                    'array_array_configured_instance' => new $nonSharedType,
                ],
                'array_global_argument' => null,
                'array_global_existing_argument' => 'GLOBAL_ARGUMENT',
                'array_global_argument_def' => 'DEFAULT_VALUE'
            ],
            $result->getValueArray()
        );
        $this->assertEquals('GLOBAL_ARGUMENT', $result->getGlobalValue());
        $this->assertNull($result->getNullValue());
    }

    /**
     * Returns simple config
     *
     * @return array
     */
    private function getSimpleConfig()
    {
        return [
            'nonSharedDependency' => [
                '_ins_' => 'Magento\Framework\ObjectManager\Test\Unit\Factory\Fixture\Compiled\DependencyTesting',
            ],
            'sharedDependency' => [
                '_i_' => 'Magento\Framework\ObjectManager\Test\Unit\Factory\Fixture\Compiled\DependencySharedTesting',
            ],
            'value' => [
                '_v_' => 'value',
            ],
            'value_array' => [
                '_v_' => ['default_value1', 'default_value2'],
            ],
            'globalValue' => [
                '_a_' => 'globalValue',
                '_d_' => null
            ],
            'nullValue' => [
                '_vn_' => true
            ]
        ];
    }

    /**
     * Returns nested config
     *
     * @return array
     */
    private function getSimpleNestedConfig()
    {
        return [
            'nonSharedDependency' => [
                '_ins_' => 'Magento\Framework\ObjectManager\Test\Unit\Factory\Fixture\Compiled\DependencyTesting',
            ],
            'sharedDependency' => [
                '_i_' => 'Magento\Framework\ObjectManager\Test\Unit\Factory\Fixture\Compiled\DependencySharedTesting',
            ],
            'value' => [
                '_v_' => 'value',
            ],
            'value_array' => [
                '_vac_' => [
                    'array_value' => 'value',
                    'array_configured_instance' => [
                        '_i_' => 'Magento\Framework\ObjectManager\Test\Unit'
                            . '\Factory\Fixture\Compiled\DependencySharedTesting',
                    ],
                    'array_configured_array' => [
                        'array_array_value' => 'value',
                        'array_array_configured_instance' => [
                            '_ins_' => 'Magento\Framework\ObjectManager'
                                . '\Test\Unit\Factory\Fixture\Compiled\DependencyTesting',
                        ],
                    ],
                    'array_global_argument' => [
                        '_a_' => 'global_argument_configured',
                        '_d_' => null
                    ],
                    'array_global_existing_argument' => [
                        '_a_' => 'array_global_existing_argument',
                        '_d_' => null
                    ],
                    'array_global_argument_def' => [
                        '_a_' => 'array_global_argument_def',
                        '_d_' => 'DEFAULT_VALUE'
                    ]
                ],
            ],
            'globalValue' => [
                '_a_' => 'globalValue',
                '_d_' => null
            ],
            'nullValue' => [
                '_vn_' => true
            ]
        ];
    }
}
