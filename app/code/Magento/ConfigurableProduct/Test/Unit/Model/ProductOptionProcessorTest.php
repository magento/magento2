<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Test\Unit\Model;

use Magento\ConfigurableProduct\Api\Data\ConfigurableItemOptionValueInterface;
use Magento\ConfigurableProduct\Model\ProductOptionProcessor;
use Magento\ConfigurableProduct\Model\Quote\Item\ConfigurableItemOptionValueFactory;
use Magento\Framework\DataObject;
use Magento\Framework\DataObject\Factory as DataObjectFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class ProductOptionProcessorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProductOptionProcessor
     */
    protected $processor;

    /**
     * @var DataObject | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $dataObject;

    /**
     * @var DataObjectFactory | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $dataObjectFactory;

    /**
     * @var ConfigurableItemOptionValueFactory | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $itemOptionValueFactory;

    /**
     * @var ConfigurableItemOptionValueInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $itemOptionValue;

    protected function setUp()
    {
        $this->dataObject = $this->getMockBuilder('Magento\Framework\DataObject')
            ->setMethods([
                'getSuperAttribute',
            ])
            ->disableOriginalConstructor()
            ->getMock();

        $this->dataObjectFactory = $this->getMockBuilder('Magento\Framework\DataObject\Factory')
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->dataObjectFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->dataObject);

        $this->itemOptionValue = $this->getMockBuilder(
            'Magento\ConfigurableProduct\Api\Data\ConfigurableItemOptionValueInterface'
        )
            ->getMockForAbstractClass();

        $this->itemOptionValueFactory = $this->getMockBuilder(
            'Magento\ConfigurableProduct\Model\Quote\Item\ConfigurableItemOptionValueFactory'
        )
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->itemOptionValueFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->itemOptionValue);

        $this->processor = new ProductOptionProcessor(
            $this->dataObjectFactory,
            $this->itemOptionValueFactory
        );
    }

    /**
     * @param array|string $options
     * @param array $requestData
     * @dataProvider dataProviderConvertToBuyRequest
     */
    public function testConvertToBuyRequest(
        $options,
        $requestData
    ) {
        $productOptionMock = $this->getMockBuilder('Magento\Catalog\Api\Data\ProductOptionInterface')
            ->getMockForAbstractClass();

        $productOptionExtensionMock = $this->getMockBuilder('Magento\Catalog\Api\Data\ProductOptionExtensionInterface')
            ->setMethods([
                'getConfigurableItemOptions',
            ])
            ->getMockForAbstractClass();

        $productOptionMock->expects($this->any())
            ->method('getExtensionAttributes')
            ->willReturn($productOptionExtensionMock);

        $productOptionExtensionMock->expects($this->any())
            ->method('getConfigurableItemOptions')
            ->willReturn($options);

        $this->dataObject->expects($this->any())
            ->method('addData')
            ->with($requestData)
            ->willReturnSelf();

        $this->assertEquals($this->dataObject, $this->processor->convertToBuyRequest($productOptionMock));
    }

    /**
     * @return array
     */
    public function dataProviderConvertToBuyRequest()
    {
        $objectManager = new ObjectManager($this);

        /** @var \Magento\ConfigurableProduct\Model\Quote\Item\ConfigurableItemOptionValue $option */
        $option = $objectManager->getObject(
            'Magento\ConfigurableProduct\Model\Quote\Item\ConfigurableItemOptionValue'
        );
        $option->setOptionId(1);
        $option->setOptionValue('test');

        return [
            [
                [$option],
                [
                    'super_attribute' => [
                        1 => 'test',
                    ],
                ],
            ],
            [[], []],
        ];
    }

    /**
     * @param array|string $options
     * @param string|null $expected
     * @dataProvider dataProviderConvertToProductOption
     */
    public function testConvertToProductOption(
        $options,
        $expected
    ) {
        $this->dataObject->expects($this->any())
            ->method('getSuperAttribute')
            ->willReturn($options);

        if (!empty($options) && is_array($options)) {
            $this->itemOptionValue->expects($this->any())
                ->method('setOptionId')
                ->with(1)
                ->willReturnSelf();
            $this->itemOptionValue->expects($this->any())
                ->method('setOptionValue')
                ->with($options[1])
                ->willReturnSelf();
        }

        $result = $this->processor->convertToProductOption($this->dataObject);

        if (!empty($expected)) {
            $this->assertArrayHasKey($expected, $result);
            $this->assertTrue(is_array($result[$expected]));
        }
    }

    /**
     * @return array
     */
    public function dataProviderConvertToProductOption()
    {
        return [
            [
                'options' => [
                    1 => 'value',
                ],
                'expected' => 'configurable_item_options',
            ],
            [
                'options' => [],
                'expected' => null,
            ],
            [
                'options' => 'is not array',
                'expected' => null,
            ],
        ];
    }
}
