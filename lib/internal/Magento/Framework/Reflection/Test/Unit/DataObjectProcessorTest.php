<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Reflection\Test\Unit;

use Magento\Framework\Api\ExtensionAttributesInterface;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\Reflection\ExtensionAttributesProcessor;
use Magento\Framework\Reflection\FieldNamer;
use Magento\Framework\Reflection\MethodsMap;
use Magento\Framework\Reflection\TypeCaster;
use Magento\Framework\Reflection\TypeProcessor;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DataObjectProcessorTest extends TestCase
{
    /**
     * @var DataObjectProcessor
     */
    private $dataObjectProcessor;

    /**
     * @var MethodsMap
     */
    private $methodsMapProcessor;

    /**
     * @var ExtensionAttributesProcessor|MockObject
     */
    private $extensionAttributesProcessorMock;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->methodsMapProcessor = $objectManager->getObject(
            MethodsMap::class,
            [
                'fieldNamer' => $objectManager->getObject(FieldNamer::class),
                'typeProcessor' => $objectManager->getObject(TypeProcessor::class),
            ]
        );
        $serializerMock = $this->getMockForAbstractClass(SerializerInterface::class);
        $serializerMock->method('serialize')
            ->willReturn('serializedData');
        $serializerMock->method('unserialize')
            ->willReturn(['unserializedData']);

        $objectManager->setBackwardCompatibleProperty(
            $this->methodsMapProcessor,
            'serializer',
            $serializerMock
        );

        $this->extensionAttributesProcessorMock = $this->getMockBuilder(ExtensionAttributesProcessor::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @param array $extensionAttributes
     * @param array $excludedMethodsClassMap
     * @param array $expectedOutput
     * @dataProvider buildOutputDataArrayDataProvider
     */
    public function testBuildOutputDataArray(
        array $extensionAttributes,
        array $excludedMethodsClassMap,
        array $expectedOutput
    ) {
        $objectManager = new ObjectManager($this);

        $this->dataObjectProcessor = $objectManager->getObject(
            DataObjectProcessor::class,
            [
                'methodsMapProcessor' => $this->methodsMapProcessor,
                'typeCaster' => $objectManager->getObject(TypeCaster::class),
                'fieldNamer' => $objectManager->getObject(FieldNamer::class),
                'extensionAttributesProcessor' => $this->extensionAttributesProcessorMock,
                'excludedMethodsClassMap' => $excludedMethodsClassMap,
            ]
        );

        /** @var TestDataObject $testDataObject */
        $testDataObject = $objectManager->getObject(
            TestDataObject::class,
            [
                'extensionAttributes' => $this->getMockForAbstractClass(
                    ExtensionAttributesInterface::class
                )
            ]
        );

        if (in_array('getExtensionAttributes', $excludedMethodsClassMap[TestDataInterface::class] ?? [])) {
            $expectedTimes = $this->never();
        } else {
            $expectedTimes = $this->once();
        }

        $this->extensionAttributesProcessorMock->expects($expectedTimes)
            ->method('buildOutputDataArray')
            ->willReturn($extensionAttributes);

        $outputData = $this->dataObjectProcessor
            ->buildOutputDataArray($testDataObject, TestDataInterface::class);
        $this->assertEquals($expectedOutput, $outputData);
    }

    /**
     * @return array
     */
    public static function buildOutputDataArrayDataProvider()
    {
        $expectedOutput = [
            'id' => '1',
            'address' => 'someAddress',
            'default_shipping' => 'true',
            'required_billing' => 'false',
        ];

        $extensionAttributes = [
            'attribute1' => 'value1',
            'attribute2' => 'value2',
        ];

        return [
            'No Extension Attributes or Excluded Methods' => [
                [],
                [],
                $expectedOutput,
            ],
            'With Extension Attributes' => [
                $extensionAttributes,
                [],
                array_merge(
                    $expectedOutput,
                    ['extension_attributes' => $extensionAttributes]
                ),
            ],
            'With Excluded Method' => [
                [],
                [
                    TestDataInterface::class => [
                        'getAddress',
                    ],
                ],
                array_diff_key($expectedOutput, array_flip(['address'])),
            ],
            'With getExtensionAttributes as Excluded Method' => [
                $extensionAttributes,
                [
                    TestDataInterface::class => [
                        'getExtensionAttributes',
                    ],
                ],
                $expectedOutput,
            ],
        ];
    }
}
