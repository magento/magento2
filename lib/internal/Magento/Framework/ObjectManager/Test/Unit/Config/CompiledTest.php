<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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
                'class_with_arguments_string' => 'string arguments',
                'class_with_arguments_array' => ['unserialized', 'arguments'],
                'class_with_no_arguments_empty_array' => [],
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

    /**
     * Test is it possible extend/overwrite arguments for the DI.
     *
     */
    public function testExtendArguments()
    {
        $configuration = [
            'arguments' => [
                'type1' => 'configuration for type1',
                'type2' => [
                    'argument2_1' => 'newArgumentValue2_1',
                ]
            ],
            'instanceTypes' => [
                'instanceType2' => 'newInstanceTypeValue2',
                'instanceType3' => 'newInstanceTypeValue3',
            ],
            'preferences' => [
                'preference1' => 'newPreferenceValue1',
            ],
        ];
        $expectedArguments = [
            'type1' => 'configuration for type1',
            'type2' => [
                'argument2_1' => 'newArgumentValue2_1',
            ]
        ];

        $this->compiled->extend($configuration);
        foreach ($expectedArguments as $type => $arguments) {
            $this->assertEquals($arguments, $this->compiled->getArguments($type));
        }
    }

    /**
     * Test getting virtual types from the DI.
     */
    public function testVirtualTypes()
    {
        $configuration = [
            'instanceTypes' => [
                'instanceType2' => 'newInstanceTypeValue2',
                'instanceType3' => 'newInstanceTypeValue3'
            ],
        ];
        $expectedTypes = [
            'instanceType1' => 'instanceTypeValue1',
            'instanceType2' => 'newInstanceTypeValue2',
            'instanceType3' => 'newInstanceTypeValue3'
        ];
        $this->compiled->extend($configuration);
        $this->assertEquals($expectedTypes, $this->compiled->getVirtualTypes());
    }

    /**
     * Test getting preferences from the DI.
     */
    public function testPreferences()
    {
        $configuration = [
            'preferences' => [
                'preference1' => 'newPreferenceValue1'
            ]
        ];
        $expectedPreferences = [
            'preference1' => 'newPreferenceValue1',
            'preference2' => 'preferenceValue2'
        ];
        $this->compiled->extend($configuration);
        $this->assertEquals($expectedPreferences, $this->compiled->getPreferences());
    }

    /**
     * Arguments defined in array, have not previously been unserialized
     */
    public function testGetArgumentsSerialized()
    {
        $unserializedArguments = 'string arguments';

        $this->assertSame($unserializedArguments, $this->compiled->getArguments('class_with_arguments_string'));
        $this->assertSame($unserializedArguments, $this->compiled->getArguments('class_with_arguments_string'));
    }

    /**
     * Arguments defined in array, have not previously been unserialized
     */
    public function testGetArgumentsSerializedEmpty()
    {
        $this->assertSame([], $this->compiled->getArguments('class_with_no_arguments_serialized'));
    }

    /**
     * Arguments defined in array, have previously been unserialized
     */
    public function testGetArgumentsUnserialized()
    {
        $unserializedArguments = ['unserialized', 'arguments'];
        $this->assertSame($unserializedArguments, $this->compiled->getArguments('class_with_arguments_array'));
    }

    /**
     * Arguments are defined but empty
     */
    public function testGetArgumentsUnserializedEmpty()
    {
        $this->assertSame([], $this->compiled->getArguments('class_with_no_arguments_empty_array'));
    }

    /**
     * Arguments not defined in array
     */
    public function testGetArgumentsNotDefined()
    {
        $this->assertSame(null, $this->compiled->getArguments('class_not_stored_in_config'));
    }
}
