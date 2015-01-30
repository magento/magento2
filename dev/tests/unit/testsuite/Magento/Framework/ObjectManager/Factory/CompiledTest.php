<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\ObjectManager\Factory;

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
     * Definition list
     *
     * @var \Magento\Framework\ObjectManager\DefinitionInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $definitions;

    /**
     * @var Compiled
     */
    protected $factory;

    public function setUp()
    {
        $this->objectManager = $this->getMockBuilder('Magento\Framework\ObjectManagerInterface')
            ->setMethods([])
            ->getMock();

        $this->config = $this->getMockBuilder('Magento\Framework\ObjectManager\ConfigInterface')
            ->setMethods([])
            ->getMock();

        $this->definitions = $this->getMockBuilder('Magento\Framework\ObjectManager\DefinitionInterface')
            ->setMethods([])
            ->getMock();

        $this->factory = new Compiled($this->config, $this->objectManager, $this->definitions, []);
    }

    public function testCreateSimple()
    {
        $expectedConfig = $this->getSimpleConfig();

        $requestedType = 'Magento\Framework\ObjectManager\Factory\Fixture\Compiled\SimpleClassTesting';

        $this->config->expects($this->once())
            ->method('getInstanceType')
            ->with($requestedType)
            ->willReturn($requestedType);
        $this->config->expects($this->once())
            ->method('getArguments')
            ->with($requestedType)
            ->willReturn($expectedConfig);

        $this->objectManager->expects($this->once())
            ->method('create')
            ->with('Dependency\StdClass')
            ->willReturn(new \StdClass);
        $this->objectManager->expects($this->once())
            ->method('get')
            ->with('Dependency\Shared\StdClass')
            ->willReturn(new \StdClass);

        /** @var \Magento\Framework\ObjectManager\Factory\Fixture\Compiled\SimpleClassTesting $result */
        $result = $this->factory->create($requestedType, []);

        $this->assertInstanceOf(
            'Magento\Framework\ObjectManager\Factory\Fixture\Compiled\SimpleClassTesting',
            $result
        );
        $this->assertInstanceOf('StdClass', $result->getSharedDependency());
        $this->assertInstanceOf('StdClass', $result->getNonSharedDependency());
        $this->assertEquals('value', $result->getValue());
        $this->assertEquals(['default_value1', 'default_value2'], $result->getValueArray());
    }

    public function testCreateSimpleConfiguredArguments()
    {
        $expectedConfig = $this->getSimpleNestedConfig();

        $requestedType = 'Magento\Framework\ObjectManager\Factory\Fixture\Compiled\SimpleClassTesting';

        $this->config->expects($this->once())
            ->method('getInstanceType')
            ->with($requestedType)
            ->willReturn($requestedType);
        $this->config->expects($this->once())
            ->method('getArguments')
            ->with($requestedType)
            ->willReturn($expectedConfig);

        $this->objectManager->expects($this->exactly(2))
            ->method('create')
            ->with('Dependency\StdClass')
            ->willReturn(new \StdClass);
        $this->objectManager->expects($this->exactly(2))
            ->method('get')
            ->with('Dependency\Shared\StdClass')
            ->willReturn(new \StdClass);
        $this->factory->setArguments(['array_global_existing_argument' => 'GLOBAL_ARGUMENT']);

        /** @var \Magento\Framework\ObjectManager\Factory\Fixture\Compiled\SimpleClassTesting $result */
        $result = $this->factory->create($requestedType, []);

        $this->assertInstanceOf(
            'Magento\Framework\ObjectManager\Factory\Fixture\Compiled\SimpleClassTesting',
            $result
        );
        $this->assertInstanceOf('StdClass', $result->getSharedDependency());
        $this->assertInstanceOf('StdClass', $result->getNonSharedDependency());
        $this->assertEquals('value', $result->getValue());
        $this->assertEquals(
            [
                'array_value' => 'value',
                'array_configured_instance' => new \StdClass,
                'array_configured_array' => [
                    'array_array_value' => 'value',
                    'array_array_configured_instance' => new \StdClass,
                ],
                'array_global_argument' => null,
                'array_global_existing_argument' => 'GLOBAL_ARGUMENT',
                'array_global_argument_def' => 'DEFAULT_VALUE'
            ],
            $result->getValueArray()
        );
    }

    /**
     * Returns simple config
     *
     * @return array
     */
    private function getSimpleConfig()
    {
        return [
            'type_dependency' => [
                '_i_' => 'Dependency\StdClass',
                '_s_' => false,
            ],
            'type_dependency_shared' => [
                '_i_' => 'Dependency\Shared\StdClass',
                '_s_' => true,
            ],
            'value' => [
                '_v_' => 'value',
            ],
            'value_array' => [
                '_v_' => ['default_value1', 'default_value2'],
            ],
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
            'type_dependency' => [
                '_i_' => 'Dependency\StdClass',
                '_s_' => false,
            ],
            'type_dependency_shared' => [
                '_i_' => 'Dependency\Shared\StdClass',
                '_s_' => true,
            ],
            'value' => [
                '_v_' => 'value',
            ],
            'value_array' => [
                '_v_' => [
                    'array_value' => 'value',
                    'array_configured_instance' => [
                        '_i_' => 'Dependency\Shared\StdClass',
                        '_s_' => true,
                    ],
                    'array_configured_array' => [
                        'array_array_value' => 'value',
                        'array_array_configured_instance' => [
                            '_i_' => 'Dependency\StdClass',
                            '_s_' => false,
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
            ]
        ];
    }
}
