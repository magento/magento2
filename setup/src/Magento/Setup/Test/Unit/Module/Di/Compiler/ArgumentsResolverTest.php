<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Module\Di\Compiler;

use Magento\Framework\ObjectManager\ConfigInterface;
use Magento\Setup\Module\Di\Compiler\ArgumentsResolver;
use Magento\Setup\Module\Di\Compiler\ConstructorArgument;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ArgumentsResolverTest extends TestCase
{
    /**
     * @var ArgumentsResolver
     */
    protected $model;

    /**
     * @var MockObject
     */
    protected $diContainerConfig;

    protected function setUp(): void
    {
        $this->diContainerConfig = $this->getMockForAbstractClass(ConfigInterface::class);
        $this->model = new ArgumentsResolver($this->diContainerConfig);
    }

    public function testGetResolvedArgumentsConstructorFormat()
    {
        $expectedResultDefault = $this->getResolvedSimpleConfigExpectation();

        $constructor = [
            new ConstructorArgument(['type_dependency', 'Type\Dependency', true, null]),
            new ConstructorArgument(['type_dependency_shared', 'Type\Dependency\Shared', true, null]),
            new ConstructorArgument(['value', null, false, 'value']),
            new ConstructorArgument(['value_array', null, false, ['default_value1', 'default_value2']]),
            new ConstructorArgument(['value_null', null, false, null]),
        ];
        $this->diContainerConfig->expects($this->any())
            ->method('isShared')
            ->willReturnMap(
                [
                    ['Type\Dependency', false],
                    ['Type\Dependency\Shared', true]
                ]
            );

        $type = 'Class';
        $this->diContainerConfig->expects($this->any())
            ->method('getArguments')
            ->with($type)
            ->willReturn([]);

        $this->assertSame(
            $expectedResultDefault,
            $this->model->getResolvedConstructorArguments($type, $constructor)
        );
    }

    public function testGetResolvedArgumentsConstructorConfiguredFormat()
    {
        $expectedResultConfigured = $this->getResolvedConfigurableConfigExpectation();

        $constructor = [
            new ConstructorArgument(['type_dependency_configured', 'Type\Dependency', true, null]),
            new ConstructorArgument(['type_dependency_shared_configured', 'Type\Dependency\Shared', true, null]),
            new ConstructorArgument(['global_argument', null, false, null]),
            new ConstructorArgument(['global_argument_def', null, false, []]),
            new ConstructorArgument(['value_configured', null, false, 'value']),
            new ConstructorArgument(['value_array_configured', null, false, []]),
            new ConstructorArgument(['value_null', null, false, null]),
        ];

        $this->diContainerConfig->expects($this->any())
            ->method('isShared')
            ->willReturnMap(
                [
                    ['Type\Dependency', false],
                    ['Type\Dependency\Shared', true],
                    ['Type\Dependency\Configured', false],
                    ['Type\Dependency\Shared\Configured', true]
                ]
            );

        $type = 'Class';
        $this->diContainerConfig->expects($this->any())
            ->method('getArguments')
            ->with($type)
            ->willReturn(
                $this->getConfiguredArguments()
            );

        $this->assertSame(
            $expectedResultConfigured,
            $this->model->getResolvedConstructorArguments($type, $constructor)
        );
    }

    /**
     * Returns resolved simple config expectation
     *
     * @return array
     */
    private function getResolvedSimpleConfigExpectation()
    {
        return [
            'type_dependency' => [
                '_ins_' => 'Type\Dependency',
            ],
            'type_dependency_shared' => [
                '_i_' => 'Type\Dependency\Shared',
            ],
            'value' => [
                '_v_' => 'value',
            ],
            'value_array' => [
                '_v_' => ['default_value1', 'default_value2'],
            ],
            'value_null' => [
                '_vn_' => true,
            ],
        ];
    }

    /**
     * Returns configured arguments expectation
     *
     * @return array
     */
    private function getConfiguredArguments()
    {
        return [
            'type_dependency_configured' => ['instance' => 'Type\Dependency\Configured'],
            'type_dependency_shared_configured' => ['instance' => 'Type\Dependency\Shared\Configured'],
            'global_argument' => ['argument' => 'global_argument_configured'],
            'global_argument_def' => ['argument' => 'global_argument_configured'],
            'value_configured' => 'value_configured',
            'value_array_configured' => [
                'array_value' => 'value',
                'array_configured_instance' => ['instance' => 'Type\Dependency\Shared\Configured'],
                'array_configured_array' => [
                    'array_array_value' => 'value',
                    'array_array_configured_instance' => [
                        'instance' => 'Type\Dependency\Shared\Configured',
                        'shared' => false
                    ]
                ],
                'array_global_argument' => ['argument' => 'global_argument_configured']
            ],
            'value_null' => null,
        ];
    }

    /**
     * Returns resolved configurable config expectation
     *
     * @return array
     */
    private function getResolvedConfigurableConfigExpectation()
    {
        return [
            'type_dependency_configured' => [
                '_ins_' => 'Type\Dependency\Configured',
            ],
            'type_dependency_shared_configured' => [
                '_i_' => 'Type\Dependency\Shared\Configured',
            ],
            'global_argument' => [
                '_a_' => 'global_argument_configured',
                '_d_' => null
            ],
            'global_argument_def' => [
                '_a_' => 'global_argument_configured',
                '_d_' => []
            ],
            'value_configured' => [
                '_v_' => 'value_configured',
            ],
            'value_array_configured' => [
                '_vac_' => [
                    'array_value' => 'value',
                    'array_configured_instance' => [
                        '_i_' => 'Type\Dependency\Shared\Configured',
                    ],
                    'array_configured_array' => [
                        'array_array_value' => 'value',
                        'array_array_configured_instance' => [
                            '_ins_' => 'Type\Dependency\Shared\Configured',
                        ],
                    ],
                    'array_global_argument' => [
                        '_a_' => 'global_argument_configured',
                        '_d_' => null
                    ]
                ],
            ],
            'value_null' => [
                '_vn_' => true,
            ],
        ];
    }
}
