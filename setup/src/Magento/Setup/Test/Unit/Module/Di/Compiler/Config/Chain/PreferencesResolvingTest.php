<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Module\Di\Compiler\Config\Chain;

use Magento\Setup\Module\Di\Compiler\Config\Chain\PreferencesResolving;
use PHPUnit\Framework\TestCase;

class PreferencesResolvingTest extends TestCase
{
    public function testEmptyConfigModify()
    {
        $inputConfig = [
            'data' => []
        ];

        $modification = new PreferencesResolving();
        $this->assertSame($inputConfig, $modification->modify($inputConfig));
    }

    public function testPreferencesResolvingModify()
    {
        $inputConfig = [
            'arguments' => $this->getInputArguments(),
            'preferences' => $this->getPreferences()
        ];
        $outputConfig = [
            'arguments' => $this->getOutputArguments(),
            'preferences' => $this->getPreferences()
        ];

        $modification = new PreferencesResolving();
        $this->assertEquals($outputConfig, $modification->modify($inputConfig));
    }

    /**
     * @return array
     */
    private function getInputArguments()
    {
        return [
            'SimpleClass' => [
                'type_dependency' => [
                    '_ins_' => 'Type\DependencyInterface',
                ],
                'type_dependency_shared' => [
                    '_i_' => 'Type\Dependency\SharedInterface',
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
                'virtual_preferece' => [
                    '_i_' => 'Type\DependencyInterface2'
                ]
            ],
            'ComplexClass' => [
                'type_dependency_configured' => [
                    '_ins_' => 'Type\Dependency\ConfiguredInterface',
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
                            '_i_' => 'Type\Dependency\Shared\ConfiguredInterface',
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
            ]
        ];
    }

    /**
     * @return array
     */
    private function getOutputArguments()
    {
        return [
            'SimpleClass' => [
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
                'virtual_preferece' => [
                    '_i_' => 'Type\DependencyVirtual3'
                ]
            ],
            'ComplexClass' => [
                'type_dependency_configured' => [
                    '_ins_' => 'Type\Dependency\Configured',
                ],
                'type_dependency_shared_configured' => [
                    '_i_' => 'Type\Dependency\Shared\ConfiguredPreference',
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
                            '_i_' => 'Type\Dependency\Shared\ConfiguredPreference',
                        ],
                        'array_configured_array' => [
                            'array_array_value' => 'value',
                            'array_array_configured_instance' => [
                                '_ins_' => 'Type\Dependency\Shared\ConfiguredPreference',
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
            ]
        ];
    }

    /**
     * @return array
     */
    private function getPreferences()
    {
        return [
            'Type\DependencyInterface' => 'Type\Dependency',
            'Type\Dependency\SharedInterface' => 'Type\Dependency\Shared',
            'Type\Dependency\ConfiguredInterface' => 'Type\Dependency\Configured',
            'Type\Dependency\Shared\ConfiguredInterface' => 'Type\Dependency\Shared\ConfiguredPreference',
            'Type\Dependency\Shared\Configured' => 'Type\Dependency\Shared\ConfiguredPreference',
            'Type\DependencyInterface2' => 'Type\DependencyVirtual',
            'Type\DependencyVirtual' => 'Type\DependencyVirtual2',
            'Type\DependencyVirtual2' => 'Type\DependencyVirtual3'
        ];
    }
}
