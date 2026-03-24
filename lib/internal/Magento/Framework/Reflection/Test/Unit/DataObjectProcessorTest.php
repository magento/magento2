<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Reflection\Test\Unit;

use Magento\Framework\Api\ExtensionAttributesInterface;
use Magento\Framework\Reflection\CustomAttributesProcessor;
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
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
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
        $serializerMock = $this->createMock(SerializerInterface::class);
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
     * @param array $expectedOutput     */
    #[DataProvider('buildOutputDataArrayDataProvider')]
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
                'extensionAttributes' => $this->createMock(
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

    /**
     * Test that UnstructuredArray is preserved as is without processing elements
     */
    public function testBuildOutputDataArrayWithUnstructuredArray()
    {
        $objectManager = new ObjectManager($this);

        $typeCaster = $objectManager->getObject(TypeCaster::class);
        $fieldNamer = $objectManager->getObject(FieldNamer::class);
        $customAttributesProcessor = $objectManager->getObject(
            CustomAttributesProcessor::class
        );

        $this->dataObjectProcessor = new DataObjectProcessor(
            $this->methodsMapProcessor,
            $typeCaster,
            $fieldNamer,
            $customAttributesProcessor,
            $this->extensionAttributesProcessorMock
        );

        $unstructuredArrayData = [
            ['sku' => 'product1', 'name' => 'Product 1'],
            ['sku' => 'product2', 'name' => 'Product 2'],
            'some_string_value',
            123,
            ['nested' => ['array' => 'value']]
        ];

        $testDataObject = $objectManager->getObject(
            TestDataObjectWithUnstructuredArray::class,
            ['items' => $unstructuredArrayData]
        );

        $outputData = $this->dataObjectProcessor->buildOutputDataArray(
            $testDataObject,
            TestDataObjectWithUnstructuredArray::class
        );

        $this->assertArrayHasKey('items', $outputData);
        $this->assertEquals($unstructuredArrayData, $outputData['items']);
        $this->assertSame($unstructuredArrayData, $outputData['items']);
    }
}
