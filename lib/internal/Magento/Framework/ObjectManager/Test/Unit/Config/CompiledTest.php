<?php
/**
 * Copyright © 2013-2018 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\ObjectManager\Test\Unit\Config;

use Magento\Framework\ObjectManager\Config\Compiled;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManager;

class CompiledTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var \Magento\Framework\ObjectManager\Config\Compiled
     */
    private $compiled;

    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);

        $initialData = [
            'arguments' => [
                'type1' => 'initial serialized configuration for type1',
                'class_with_no_arguments_serialized' => null,
                'class_with_arguments_serialized' => 'serialized arguments',
                'class_with_arguments_unserialized' => ['unserialized', 'arguments'],
                'class_with_no_arguments_unserialized' => [],
            ],
            'instanceTypes' => [
                'instanceType1' => 'instanceTypeValue1',
                'instanceType2' => 'instanceTypeValue2'
            ],
            'preferences' => [
                'preference1' => 'preferenceValue1',
                'preference2' => 'preferenceValue2'
            ]
        ];

        $this->compiled = $this->objectManager->getObject(
            Compiled::class,
            [
                'data' => $initialData,
            ]
        );
    }

    public function testExtend()
    {

        $configuration = [
            'arguments' => [
                'type1' => 'serialized configuration for type1',
                'type2' => 'serialized configuration for type2'
            ],
            'instanceTypes' => [
                'instanceType2' => 'newInstanceTypeValue2',
                'instanceType3' => 'newInstanceTypeValue3'
            ],
            'preferences' => [
                'preference1' => 'newPreferenceValue1'
            ]
        ];
        $expectedArguments = [
            'type1' => [
                'argument1_1' => 'newArgumentValue1_1'
            ],
            'type2' => [
                'argument2_1' => 'newArgumentValue2_1'
            ]
        ];
        $expectedVirtualTypes = [
            'instanceType1' => 'instanceTypeValue1',
            'instanceType2' => 'newInstanceTypeValue2',
            'instanceType3' => 'newInstanceTypeValue3'
        ];
        $expectedPreferences = [
            'preference1' => 'newPreferenceValue1',
            'preference2' => 'preferenceValue2'
        ];

        $this->compiled->extend($configuration);
        foreach ($expectedArguments as $type => $arguments) {
            $this->assertEquals($arguments, $this->compiled->getArguments($type));
        }
        $this->assertEquals($expectedVirtualTypes, $this->compiled->getVirtualTypes());
        $this->assertEquals($expectedPreferences, $this->compiled->getPreferences());
    }
}
