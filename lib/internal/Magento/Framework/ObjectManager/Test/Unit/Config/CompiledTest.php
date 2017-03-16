<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\ObjectManager\Test\Unit\Config;

use Magento\Framework\ObjectManager\Config\Compiled;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManager;
use Magento\Framework\Serialize\SerializerInterface;

class CompiledTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var SerializerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $serializerMock;

    /**
     * @var \Magento\Framework\ObjectManager\Config\Compiled
     */
    private $compiled;

    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);
        $this->serializerMock = $this->getMock(SerializerInterface::class);

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
                'serializer' => $this->serializerMock
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

        $this->serializerMock->expects($this->at(0))
            ->method('unserialize')
            ->with($configuration['arguments']['type1'])
            ->willReturn($expectedArguments['type1']);
        $this->serializerMock->expects($this->at(1))
            ->method('unserialize')
            ->with($configuration['arguments']['type2'])
            ->willReturn($expectedArguments['type2']);

        $this->compiled->extend($configuration);
        foreach ($expectedArguments as $type => $arguments) {
            $this->assertEquals($arguments, $this->compiled->getArguments($type));
        }
        $this->assertEquals($expectedVirtualTypes, $this->compiled->getVirtualTypes());
        $this->assertEquals($expectedPreferences, $this->compiled->getPreferences());
    }

    /**
     * Arguments defined in array, have not previously been unserialized
     */
    public function testGetArgumentsSerialized()
    {
        $unserializedArguments = ['unserialized', 'arguments'];

        // method called twice but after one unserialization, unserialized version should be stored
        $this->serializerMock->expects($this->once())->method('unserialize')
            ->with('serialized arguments')
            ->willReturn($unserializedArguments);

        $this->assertSame($unserializedArguments, $this->compiled->getArguments('class_with_arguments_serialized'));
        $this->assertSame($unserializedArguments, $this->compiled->getArguments('class_with_arguments_serialized'));
    }

    /**
     * Arguments defined in array, have not previously been unserialized
     */
    public function testGetArgumentsSerializedEmpty()
    {
        $this->serializerMock->expects($this->never())->method('unserialize');
        $this->assertSame([], $this->compiled->getArguments('class_with_no_arguments_serialized'));
    }

    /**
     * Arguments defined in array, have previously been unserialized
     */
    public function testGetArgumentsUnserialized()
    {
        $unserializedArguments = ['unserialized', 'arguments'];
        $this->serializerMock->expects($this->never())->method('unserialize');
        $this->assertSame($unserializedArguments, $this->compiled->getArguments('class_with_arguments_unserialized'));
    }

    /**
     * Arguments are defined but empty
     */
    public function testGetArgumentsUnserializedEmpty()
    {
        $this->serializerMock->expects($this->never())->method('unserialize');
        $this->assertSame([], $this->compiled->getArguments('class_with_no_arguments_unserialized'));
    }

    /**
     * Arguments not defined in array
     */
    public function testGetArgumentsNotDefined()
    {
        $this->serializerMock->expects($this->never())->method('unserialize');
        $this->assertSame(null, $this->compiled->getArguments('class_not_stored_in_config'));
    }
}
