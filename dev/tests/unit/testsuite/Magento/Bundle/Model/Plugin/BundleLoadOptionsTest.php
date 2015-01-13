<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Model\Plugin;

class BundleLoadOptionsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Bundle\Model\Plugin\BundleLoadOptions
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $optionListMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $attributeBuilderMock;

    protected function setUp()
    {
        $this->optionListMock = $this->getMock('\Magento\Bundle\Model\Product\OptionList', [], [], '', false);
        $this->attributeBuilderMock = $this->getMock('\Magento\Framework\Api\AttributeDataBuilder', [], [], '', false);
        $this->model = new \Magento\Bundle\Model\Plugin\BundleLoadOptions(
            $this->optionListMock,
            $this->attributeBuilderMock
        );
    }

    public function testAroundLoadIfProductTypeNotBundle()
    {
        $productMock = $this->getMock('Magento\Catalog\Model\Product', ['getTypeId'], [], '', false);
        $closure = function () use ($productMock) {
            return $productMock;
        };
        $productMock->expects($this->once())
            ->method('getTypeId')
            ->willReturn(\Magento\Catalog\Model\Product\Type::TYPE_SIMPLE);
        $this->assertEquals(
            $productMock,
            $this->model->aroundLoad($productMock, $closure, 100, null)
        );
    }

    public function testAroundLoad()
    {
        $productMock = $this->getMock(
            '\Magento\Catalog\Model\Product',
            ['getTypeId', 'getCustomAttributes', 'setData'],
            [],
            '',
            false
        );
        $closure = function () use ($productMock) {
            return $productMock;
        };
        $productMock->expects($this->once())
            ->method('getTypeId')
            ->willReturn(\Magento\Catalog\Model\Product\Type::TYPE_BUNDLE);

        $optionMock = $this->getMock('\Magento\Bundle\Api\Data\OptionInterface');
        $this->optionListMock->expects($this->once())
            ->method('getItems')
            ->with($productMock)
            ->willReturn([$optionMock]);
        $this->attributeBuilderMock->expects($this->once())
            ->method('setAttributeCode')
            ->with('bundle_product_options')
            ->willReturnSelf();
        $this->attributeBuilderMock->expects($this->once())
            ->method('setValue')
            ->with([$optionMock])
            ->willReturnSelf();
        $customAttributeMock = $this->getMock('\Magento\Framework\Api\AttributeValue', [], [], '', false);
        $this->attributeBuilderMock->expects($this->once())->method('create')->willReturn($customAttributeMock);

        $productAttributeMock = $this->getMock('\Magento\Framework\Api\AttributeValue', [], [], '', false);
        $productMock->expects($this->once())->method('getCustomAttributes')->willReturn([$productAttributeMock]);
        $productMock->expects($this->once())
            ->method('setData')
            ->with('custom_attributes', ['bundle_product_options' => $customAttributeMock, $productAttributeMock])
            ->willReturnSelf();

        $this->assertEquals(
            $productMock,
            $this->model->aroundLoad($productMock, $closure, 100, null)
        );
    }
}
