<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Test\Unit\ObjectManager\Config;

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

    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);
        $this->serializerMock = $this->getMock(SerializerInterface::class);
    }

    public function testExtend()
    {
        $initialData = [
            'arguments' => [
                'type1' => 'initial serialized configuration for type1'
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
        $compiled = $this->objectManager->getObject(
            Compiled::class,
            [
                'data' => $initialData,
                'serializer' => $this->serializerMock
            ]
        );
        $compiled->extend($configuration);
        foreach ($expectedArguments as $type => $arguments) {
            $this->assertEquals($arguments, $compiled->getArguments($type));
        }
        $this->assertEquals($expectedVirtualTypes, $compiled->getVirtualTypes());
        $this->assertEquals($expectedPreferences, $compiled->getPreferences());
    }
}
