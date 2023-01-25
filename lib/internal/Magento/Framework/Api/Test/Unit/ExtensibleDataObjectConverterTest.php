<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Api\Test\Unit;

use Magento\Framework\Api\AbstractExtensibleObject;
use Magento\Framework\Api\AttributeValue;
use Magento\Framework\Api\ExtensibleDataInterface;
use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ExtensibleDataObjectConverterTest extends TestCase
{
    /** @var  ExtensibleDataObjectConverter */
    protected $converter;

    /** @var  DataObjectProcessor|MockObject */
    protected $processor;

    /** @var  ExtensibleDataInterface|MockObject */
    protected $dataObject;

    protected function setUp(): void
    {
        $this->processor = $this->getMockBuilder(DataObjectProcessor::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->dataObject = $this->getMockBuilder(ExtensibleDataInterface::class)
            ->getMock();

        $objectManager = new ObjectManager($this);
        $this->converter = $objectManager->getObject(
            ExtensibleDataObjectConverter::class,
            [
                'dataObjectProcessor' => $this->processor,
            ]
        );
    }

    /**
     * Test toNestedArray() method without custom attributes.
     */
    public function testToNestedArray()
    {
        $dataArray = [
            'attribute_key' => 'attribute_value',
        ];

        $this->processor->expects($this->any())
            ->method('buildOutputDataArray')
            ->with($this->dataObject)
            ->willReturn($dataArray);

        $this->assertEquals(
            $dataArray,
            $this->converter->toNestedArray($this->dataObject)
        );
    }

    /**
     * Test toNestedArray() method with custom attributes and with skipped custom attribute.
     */
    public function testToNestedArrayCustom()
    {
        $dataArray = [
            'attribute_key' => 'attribute_value',
            AbstractExtensibleObject::CUSTOM_ATTRIBUTES_KEY => [
                [
                    AttributeValue::ATTRIBUTE_CODE => 'custom_attribute_code',
                    AttributeValue::VALUE => 'custom_attribute_value',
                ],
                [
                    AttributeValue::ATTRIBUTE_CODE => 'custom_attribute_code_multi',
                    AttributeValue::VALUE => [
                        'custom_attribute_value_multi_1',
                        'custom_attribute_value_multi_2',
                    ],
                ],
                [
                    AttributeValue::ATTRIBUTE_CODE => 'custom_attribute_code_skip',
                    AttributeValue::VALUE => 'custom_attribute_value_skip',
                ],
            ],
            'test' => [
                0 => [
                    '3rd_attribute_key' => '3rd_attribute_value',
                    AbstractExtensibleObject::CUSTOM_ATTRIBUTES_KEY => [
                        [
                            AttributeValue::ATTRIBUTE_CODE => 'another_custom_attribute_code',
                            AttributeValue::VALUE => 'another_custom_attribute_value',
                        ]
                    ]
                ]
            ]
        ];

        $resultArray = [
            'attribute_key' => 'attribute_value',
            'custom_attribute_code' => 'custom_attribute_value',
            'custom_attribute_code_multi' => [
                'custom_attribute_value_multi_1',
                'custom_attribute_value_multi_2',
            ],
            'test' => [
                0 => [
                    '3rd_attribute_key' => '3rd_attribute_value',
                    'another_custom_attribute_code' => 'another_custom_attribute_value',
                ]
            ]
        ];

        $this->processor->expects($this->any())
            ->method('buildOutputDataArray')
            ->with($this->dataObject)
            ->willReturn($dataArray);

        $this->assertEquals(
            $resultArray,
            $this->converter->toNestedArray($this->dataObject, ['custom_attribute_code_skip'])
        );
    }
}
