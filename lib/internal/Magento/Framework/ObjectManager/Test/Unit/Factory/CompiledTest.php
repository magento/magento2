<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\ObjectManager\Test\Unit\Factory;

use Magento\Framework\ObjectManager\ConfigInterface;
use Magento\Framework\ObjectManager\DefinitionInterface;
use Magento\Framework\ObjectManager\Factory\Compiled;
use Magento\Framework\ObjectManager\Test\Unit\Factory\Fixture\Compiled\DependencySharedTesting;
use Magento\Framework\ObjectManager\Test\Unit\Factory\Fixture\Compiled\DependencyTesting;
use Magento\Framework\ObjectManager\Test\Unit\Factory\Fixture\Compiled\SimpleClassTesting;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CompiledTest extends TestCase
{
    /** @var ObjectManagerInterface|MockObject */
    protected $objectManagerMock;

    /** @var ConfigInterface|MockObject */
    protected $config;

    /** @var DefinitionInterface|MockObject */
    private $definitionsMock;

    /** @var Compiled */
    protected $factory;

    /** @var array */
    private $sharedInstances;

    /** @var ObjectManager */
    private $objectManager;

    /**
     * Setup tests
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->objectManagerMock = $this->getMockBuilder(ObjectManagerInterface::class)
            ->setMethods([])
            ->getMockForAbstractClass();

        $this->config = $this->getMockBuilder(ConfigInterface::class)
            ->setMethods([])
            ->getMockForAbstractClass();

        $this->sharedInstances = [];
        $this->factory = new Compiled($this->config, $this->sharedInstances, []);
        $this->factory->setObjectManager($this->objectManagerMock);

        $this->definitionsMock = $this->getMockBuilder(DefinitionInterface::class)
            ->getMock();
        $this->objectManager->setBackwardCompatibleProperty($this->factory, 'definitions', $this->definitionsMock);
    }

    /**
     * Test create simple
     */
    public function testCreateSimple()
    {
        $expectedConfig = $this->getSimpleConfig();

        $requestedType = 'requestedType';
        $type = SimpleClassTesting::class;
        $sharedType = DependencySharedTesting::class;
        $nonSharedType = DependencyTesting::class;

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

        /** @var SimpleClassTesting $result */
        $result = $this->factory->create($requestedType, []);

        $this->assertInstanceOf(
            SimpleClassTesting::class,
            $result
        );
        $this->assertInstanceOf($sharedType, $result->getSharedDependency());
        $this->assertInstanceOf($nonSharedType, $result->getNonSharedDependency());
        $this->assertEquals('value', $result->getValue());
        $this->assertEquals(['default_value1', 'default_value2'], $result->getValueArray());
        $this->assertEquals('GLOBAL_ARGUMENT', $result->getGlobalValue());
        $this->assertNull($result->getNullValue());
    }

    /**
     * Test create simple configured arguments
     */
    public function testCreateSimpleConfiguredArguments()
    {
        $expectedConfig = $this->getSimpleNestedConfig();

        $type = SimpleClassTesting::class;
        $requestedType = 'requestedType';
        $sharedType =
            DependencySharedTesting::class;
        $nonSharedType = DependencyTesting::class;

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

        /** @var SimpleClassTesting $result */
        $result = $this->factory->create($requestedType, []);

        $this->assertInstanceOf(
            SimpleClassTesting::class,
            $result
        );
        $this->assertInstanceOf($sharedType, $result->getSharedDependency());
        $this->assertInstanceOf($nonSharedType, $result->getNonSharedDependency());
        $this->assertEquals('value', $result->getValue());
        $this->assertEquals(
            [
                'array_value' => 'value',
                'array_configured_instance' => new $sharedType(),
                'array_configured_array' => [
                    'array_array_value' => 'value',
                    'array_array_configured_instance' => new $nonSharedType(),
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
     * Test create get arguments in runtime
     */
    public function testCreateGetArgumentsInRuntime()
    {
        // Stub OM to create test assets
        $this->config->expects($this->any())->method('isShared')->willReturn(true);
        $this->objectManagerMock->expects($this->any())->method('get')->willReturnMap(
            [
                [DependencyTesting::class, new DependencyTesting()],
                [DependencySharedTesting::class, new DependencySharedTesting()]
            ]
        );

        // Simulate case where compiled DI config not found
        $type = SimpleClassTesting::class;
        $this->config->expects($this->any())->method('getArguments')->willReturn(null);
        $this->config->expects($this->any())->method('getInstanceType')->willReturnArgument(0);
        $this->definitionsMock->expects($this->once())
            ->method('getParameters')
            ->with($type)
            ->willReturn($this->getRuntimeParameters());

        $sharedType = DependencySharedTesting::class;
        $nonSharedType = DependencyTesting::class;

        // Run SUT
        /** @var SimpleClassTesting $result */
        $result = $this->factory->create($type, []);

        $this->assertInstanceOf($type, $result);
        $this->assertInstanceOf($sharedType, $result->getSharedDependency());
        $this->assertInstanceOf($nonSharedType, $result->getNonSharedDependency());
        $this->assertEquals('value', $result->getValue());
        $this->assertEquals(['default_value1', 'default_value2'], $result->getValueArray());
        $this->assertSame('', $result->getGlobalValue());
        $this->assertNull($result->getNullValue());
    }

    /**
     * Returns simple config with default constructor values for
     * \Magento\Framework\ObjectManager\Test\Unit\Factory\Fixture\Compiled\SimpleClassTesting
     *
     * @return array
     */
    private function getSimpleConfig()
    {
        return [
            'nonSharedDependency' => [
                '_ins_' => DependencyTesting::class,
            ],
            'sharedDependency' => [
                '_i_' => DependencySharedTesting::class,
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
     * Returns config for \Magento\Framework\ObjectManager\Test\Unit\Factory\Fixture\Compiled\SimpleClassTesting
     * with non-default nested array value for the $value_array parameter
     *
     * @return array
     */
    private function getSimpleNestedConfig()
    {
        return [
            'nonSharedDependency' => [
                '_ins_' => DependencyTesting::class,
            ],
            'sharedDependency' => [
                '_i_' => DependencySharedTesting::class,
            ],
            'value' => [
                '_v_' => 'value',
            ],
            'value_array' => [
                '_vac_' => [
                    'array_value' => 'value',
                    'array_configured_instance' => [
                        '_i_' => \Magento\Framework\ObjectManager\Test\Unit::class
                            . '\Factory\Fixture\Compiled\DependencySharedTesting',
                    ],
                    'array_configured_array' => [
                        'array_array_value' => 'value',
                        'array_array_configured_instance' => [
                            '_ins_' => \Magento\Framework\ObjectManager::class
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

    /**
     * Returns mock parameter list for
     * \Magento\Framework\ObjectManager\Test\Unit\Factory\Fixture\Compiled\SimpleClassTesting
     * as would be found by \Magento\Framework\ObjectManager\DefinitionInterface
     *
     * @return array
     */
    private function getRuntimeParameters()
    {
        return [
            0 => [
                0 => 'nonSharedDependency',
                1 => DependencyTesting::class,
                2 => true,
                3 => null,
                4 => false,
            ],
            1 => [
                0 => 'sharedDependency',
                1 => DependencySharedTesting::class,
                2 => true,
                3 => null,
                4 => false,
            ],
            2 => [
                0 => 'value',
                1 => null,
                2 => false,
                3 => 'value',
                4 => false,
            ],
            3 => [
                0 => 'valueArray',
                1 => null,
                2 => false,
                3 => [
                    0 => 'default_value1',
                    1 => 'default_value2',
                ],
                4 => false,
            ],
            4 => [
                0 => 'globalValue',
                1 => null,
                2 => false,
                3 => '',
                4 => false,
            ],
            5 => [
                0 => 'nullValue',
                1 => null,
                2 => false,
                3 => null,
                4 => false,
            ],
        ];
    }
}
