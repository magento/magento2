<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Test\Unit\ObjectManager\Config;

use Magento\Framework\ObjectManager\Config\Compiled as CompiledConfig;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class CompiledTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    protected function setUp()
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);
    }

    /**
     * @param array $initialData
     * @param array $configuration
     * @param array $expectedArguments
     * @param array $expectedVirtualTypes
     * @param array $expectedPreferences
     *
     * @dataProvider extendDataProvider
     */
    public function testExtend(
        array $initialData,
        array $configuration,
        array $expectedArguments,
        array $expectedVirtualTypes,
        array $expectedPreferences
    ) {
        /** @var CompiledConfig $compiledConfig */
        $compiledConfig = $this->objectManagerHelper->getObject(CompiledConfig::class, ['data' => $initialData]);
        $compiledConfig->extend($configuration);

        foreach ($expectedArguments as $type => $arguments) {
            $this->assertEquals($arguments, $compiledConfig->getArguments($type));
        }

        $this->assertEquals($expectedVirtualTypes, $compiledConfig->getVirtualTypes());
        $this->assertEquals($expectedPreferences, $compiledConfig->getPreferences());
    }

    /**
     * @return array
     */
    public function extendDataProvider()
    {
        return [
            [
                'initialData' => [
                    'arguments' => [
                        'type1' => serialize(['argument1_1' => 'argumentValue1_1', 'argument1_2' => 'argumentValue1_2'])
                    ],
                    'instanceTypes' => [
                        'instanceType1' => 'instanceTypeValue1', 'instanceType2' => 'instanceTypeValue2'
                    ],
                    'preferences' => [
                        'preference1' => 'preferenceValue1',
                        'preference2' => 'preferenceValue2'
                    ]
                ],
                'configuration' => [
                    'arguments' => [
                        'type1' => serialize(['argument1_1' => 'newArgumentValue1_1']),
                        'type2' => serialize(['argument2_1' => 'newArgumentValue2_1'])
                    ],
                    'instanceTypes' => [
                        'instanceType2' => 'newInstanceTypeValue2',
                        'instanceType3' => 'newInstanceTypeValue3'
                    ],
                    'preferences' => [
                        'preference1' => 'newPreferenceValue1'
                    ]
                ],
                'expectedArguments' => [
                    'type1' => ['argument1_1' => 'newArgumentValue1_1'],
                    'type2' => ['argument2_1' => 'newArgumentValue2_1']
                ],
                'expectedVirtualTypes' => [
                    'instanceType1' => 'instanceTypeValue1',
                    'instanceType2' => 'newInstanceTypeValue2',
                    'instanceType3' => 'newInstanceTypeValue3'
                ],
                'expectedPreferences' => [
                    'preference1' => 'newPreferenceValue1',
                    'preference2' => 'preferenceValue2'
                ]
            ],

            [
                'initialData' => [
                    'arguments' => null,
                    'instanceTypes' => null,
                    'preferences' => null
                ],
                'configuration' => [
                    'arguments' => [
                        'type1' => serialize(['argument1_1' => 'newArgumentValue1_1']),
                        'type2' => serialize(['argument2_1' => 'newArgumentValue2_1'])
                    ],
                    'instanceTypes' => [
                        'instanceType2' => 'newInstanceTypeValue2',
                        'instanceType3' => 'newInstanceTypeValue3'
                    ],
                    'preferences' => ['preference1' => 'newPreferenceValue1']
                ],
                'expectedArguments' => [
                    'type1' => ['argument1_1' => 'newArgumentValue1_1'],
                    'type2' => ['argument2_1' => 'newArgumentValue2_1']
                ],
                'expectedVirtualTypes' => [
                    'instanceType2' => 'newInstanceTypeValue2',
                    'instanceType3' => 'newInstanceTypeValue3'
                ],
                'expectedPreferences' => [
                    'preference1' => 'newPreferenceValue1'
                ]
            ]
        ];
    }
}
