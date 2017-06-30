<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Api\Test\Unit;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Api\AbstractExtensibleObject;
use Magento\Framework\Api\AttributeValue;

class ExtensibleDataObjectConverterTest extends \PHPUnit_Framework_TestCase
{
    /** @var  \Magento\Framework\Api\ExtensibleDataObjectConverter */
    protected $converter;

    /** @var  \Magento\Framework\Reflection\DataObjectProcessor|\PHPUnit_Framework_MockObject_MockObject */
    protected $processor;

    /** @var  \Magento\Framework\Api\ExtensibleDataInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $dataObject;

    protected function setUp()
    {
        $this->processor = $this->getMockBuilder(\Magento\Framework\Reflection\DataObjectProcessor::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->dataObject = $this->getMockBuilder(\Magento\Framework\Api\ExtensibleDataInterface::class)
            ->getMock();

        $objectManager = new ObjectManager($this);
        $this->converter = $objectManager->getObject(
            \Magento\Framework\Api\ExtensibleDataObjectConverter::class,
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
        ];

        $resultArray = [
            'attribute_key' => 'attribute_value',
            'custom_attribute_code' => 'custom_attribute_value',
            'custom_attribute_code_multi' => [
                'custom_attribute_value_multi_1',
                'custom_attribute_value_multi_2',
            ],
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
