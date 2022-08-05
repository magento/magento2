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
     * @var ExtensionAttributesProcessor|MockObject
     */
    private $extensionAttributesProcessorMock;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $methodsMapProcessor = $objectManager->getObject(
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
            $methodsMapProcessor,
            'serializer',
            $serializerMock
        );

        $this->extensionAttributesProcessorMock = $this->getMockBuilder(ExtensionAttributesProcessor::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->dataObjectProcessor = $objectManager->getObject(
            DataObjectProcessor::class,
            [
                'methodsMapProcessor' => $methodsMapProcessor,
                'typeCaster' => $objectManager->getObject(TypeCaster::class),
                'fieldNamer' => $objectManager->getObject(FieldNamer::class),
                'extensionAttributesProcessor' => $this->extensionAttributesProcessorMock
            ]
        );
    }

    /**
     * @param array $extensionAttributes
     * @param array $expectedOutputDataArray
     *
     * @dataProvider buildOutputDataArrayDataProvider
     */
    public function testBuildOutputDataArray($extensionAttributes, $expectedOutputDataArray)
    {
        $objectManager =  new ObjectManager($this);
        /** @var TestDataObject $testDataObject */
        $testDataObject = $objectManager->getObject(
            TestDataObject::class,
            [
                'extensionAttributes' => $this->getMockForAbstractClass(
                    ExtensionAttributesInterface::class
                )
            ]
        );

        $this->extensionAttributesProcessorMock->expects($this->once())
            ->method('buildOutputDataArray')
            ->willReturn($extensionAttributes);

        $outputData = $this->dataObjectProcessor
            ->buildOutputDataArray($testDataObject, TestDataInterface::class);
        $this->assertEquals($expectedOutputDataArray, $outputData);
    }

    /**
     * @return array
     */
    public function buildOutputDataArrayDataProvider()
    {
        $expectedOutputDataArray = [
            'id' => '1',
            'address' => 'someAddress',
            'default_shipping' => 'true',
            'required_billing' => 'false',
        ];
        $extensionAttributeArray = [
            'attribute1' => 'value1',
            'attribute2' => 'value2'
        ];

        return [
            'No Attributes' => [[], $expectedOutputDataArray],
            'With Attributes' => [
                $extensionAttributeArray,
                array_merge(
                    $expectedOutputDataArray,
                    ['extension_attributes' => $extensionAttributeArray]
                )
            ]
        ];
    }
}
