<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Reflection\Test\Unit;

use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\Reflection\ExtensionAttributesProcessor;

class DataObjectProcessorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var DataObjectProcessor
     */
    private $dataObjectProcessor;

    /**
     * @var ExtensionAttributesProcessor|\PHPUnit_Framework_MockObject_MockObject
     */
    private $extensionAttributesProcessorMock;

    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $methodsMapProcessor = $objectManager->getObject(
            \Magento\Framework\Reflection\MethodsMap::class,
            [
                'fieldNamer' => $objectManager->getObject(\Magento\Framework\Reflection\FieldNamer::class),
                'typeProcessor' => $objectManager->getObject(\Magento\Framework\Reflection\TypeProcessor::class),
            ]
        );
        $serializerMock = $this->createMock(SerializerInterface::class);
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
            \Magento\Framework\Reflection\DataObjectProcessor::class,
            [
                'methodsMapProcessor' => $methodsMapProcessor,
                'typeCaster' => $objectManager->getObject(\Magento\Framework\Reflection\TypeCaster::class),
                'fieldNamer' => $objectManager->getObject(\Magento\Framework\Reflection\FieldNamer::class),
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
        $objectManager =  new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        /** @var \Magento\Framework\Reflection\Test\Unit\TestDataObject $testDataObject */
        $testDataObject = $objectManager->getObject(
            \Magento\Framework\Reflection\Test\Unit\TestDataObject::class,
            [
                'extensionAttributes' => $this->getMockForAbstractClass(
                    \Magento\Framework\Api\ExtensionAttributesInterface::class
                )
            ]
        );

        $this->extensionAttributesProcessorMock->expects($this->once())
            ->method('buildOutputDataArray')
            ->willReturn($extensionAttributes);

        $outputData = $this->dataObjectProcessor
            ->buildOutputDataArray($testDataObject, \Magento\Framework\Reflection\Test\Unit\TestDataInterface::class);
        $this->assertEquals($expectedOutputDataArray, $outputData);
    }

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
