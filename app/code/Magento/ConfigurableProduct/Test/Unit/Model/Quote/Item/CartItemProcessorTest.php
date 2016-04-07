<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Test\Unit\Model\Quote\Item;

use Magento\ConfigurableProduct\Test\Unit\Model\Product\ProductOptionExtensionAttributes;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

class CartItemProcessorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\ConfigurableProduct\Model\Quote\Item\CartItemProcessor
     */
    protected $model;

    /**
     * @var MockObject
     */
    protected $objectFactoryMock;

    /**
     * @var MockObject
     */
    protected $optionFactoryMock;

    /**
     * @var MockObject
     */
    protected $optionExtensionFactoryMock;

    /**
     * @var MockObject
     */
    protected $optionValueFactoryMock;

    /**
     * @var ProductOptionExtensionAttributes|MockObject
     */
    private $productOptionExtensionAttributes;

    protected function setUp()
    {
        $this->objectFactoryMock = $this->getMock('\Magento\Framework\DataObject\Factory', ['create'], [], '', false);
        $this->optionFactoryMock = $this->getMock(
            '\Magento\Quote\Model\Quote\ProductOptionFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->optionExtensionFactoryMock = $this->getMock(
            '\Magento\Quote\Api\Data\ProductOptionExtensionFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->optionValueFactoryMock = $this->getMock(
            '\Magento\ConfigurableProduct\Model\Quote\Item\ConfigurableItemOptionValueFactory',
            ['create'],
            [],
            '',
            false
        );

        $this->productOptionExtensionAttributes = $this->getMockForAbstractClass(
            ProductOptionExtensionAttributes::class,
            [],
            '',
            false,
            true,
            true,
            ['setConfigurableItemOptions']
        );

        $this->model = new \Magento\ConfigurableProduct\Model\Quote\Item\CartItemProcessor(
            $this->objectFactoryMock,
            $this->optionFactoryMock,
            $this->optionExtensionFactoryMock,
            $this->optionValueFactoryMock
        );
    }

    public function testConvertToBuyRequestIfNoProductOption()
    {
        $cartItemMock = $this->getMock('\Magento\Quote\Api\Data\CartItemInterface');
        $cartItemMock->expects($this->once())->method('getProductOption')->willReturn(null);
        $this->assertNull($this->model->convertToBuyRequest($cartItemMock));
    }

    public function testConvertToBuyRequest()
    {
        $optionId = 'option_id';
        $optionValue = 'option_value';

        $productOptionMock = $this->getMock('\Magento\Quote\Api\Data\ProductOptionInterface');
        $cartItemMock = $this->getMock('\Magento\Quote\Api\Data\CartItemInterface');
        $cartItemMock->expects($this->exactly(3))->method('getProductOption')->willReturn($productOptionMock);
        $extAttributesMock = $this->getMock(
            '\Magento\Quote\Api\Data\ProductOption',
            ['getConfigurableItemOptions'],
            [],
            '',
            false
        );
        $productOptionMock
            ->expects($this->exactly(2))
            ->method('getExtensionAttributes')
            ->willReturn($extAttributesMock);

        $optionValueMock = $this->getMock('\Magento\ConfigurableProduct\Api\Data\ConfigurableItemOptionValueInterface');
        $extAttributesMock->expects($this->once())
            ->method('getConfigurableItemOptions')
            ->willReturn([$optionValueMock]);

        $optionValueMock->expects($this->once())->method('getOptionId')->willReturn($optionId);
        $optionValueMock->expects($this->once())->method('getOptionValue')->willReturn($optionValue);

        $requestData = [
            'super_attribute' => [
                $optionId => $optionValue
            ]
        ];
        $buyRequestMock = new \Magento\Framework\DataObject($requestData);
        $this->objectFactoryMock->expects($this->once())
            ->method('create')
            ->with($requestData)
            ->willReturn($buyRequestMock);

        $this->assertEquals($buyRequestMock, $this->model->convertToBuyRequest($cartItemMock));
    }

    public function testProcessProductOptionsIfOptionNotSelected()
    {
        $customOption = $this->getMock('\Magento\Catalog\Model\Product\Configuration\Item\Option', [], [], '', false);
        $customOption->expects($this->once())->method('getValue')->willReturn('');

        $productMock = $this->getMock('\Magento\Catalog\Model\Product', [], [], '', false);
        $productMock->expects($this->once())->method('getCustomOption')->with('attributes')->willReturn($customOption);

        $cartItemMock = $this->getMock('\Magento\Quote\Model\Quote\Item', ['getProduct'], [], '', false);
        $cartItemMock->expects($this->once())->method('getProduct')->willReturn($productMock);
        $this->assertEquals($cartItemMock, $this->model->processOptions($cartItemMock));
    }

    public function testProcessProductOptions()
    {
        $optionId = 'option_id';
        $optionValue = 'option_value';

        $customOption = $this->getMock('\Magento\Catalog\Model\Product\Configuration\Item\Option', [], [], '', false);
        $customOption->expects($this->once())->method('getValue')->willReturn(serialize([$optionId => $optionValue]));
        $productMock = $this->getMock('\Magento\Catalog\Model\Product', [], [], '', false);
        $productMock->expects($this->once())->method('getCustomOption')->with('attributes')->willReturn($customOption);

        $cartItemMock = $this->getMock(
            '\Magento\Quote\Model\Quote\Item',
            ['getProduct', 'getProductOption', 'setProductOption'],
            [],
            '',
            false
        );
        $cartItemMock->expects($this->once())->method('getProduct')->willReturn($productMock);
        $cartItemMock->expects($this->once())->method('getProductOption')->willReturn(null);

        $optionValueMock = $this->getMock('\Magento\ConfigurableProduct\Api\Data\ConfigurableItemOptionValueInterface');
        $this->optionValueFactoryMock->expects($this->once())->method('create')->willReturn($optionValueMock);
        $optionValueMock->expects($this->once())->method('setOptionId')->with($optionId)->willReturnSelf();
        $optionValueMock->expects($this->once())->method('setOptionValue')->with($optionValue)->willReturnSelf();

        $productOptionMock = $this->getMock('\Magento\Quote\Api\Data\ProductOptionInterface');
        $this->optionFactoryMock->expects($this->once())->method('create')->willReturn($productOptionMock);
        $productOptionMock->expects($this->once())->method('getExtensionAttributes')->willReturn(null);

        $this->optionExtensionFactoryMock->expects(static::once())
            ->method('create')
            ->willReturn($this->productOptionExtensionAttributes);
        $this->productOptionExtensionAttributes->expects($this->once())
            ->method('setConfigurableItemOptions')
            ->with([$optionValueMock])
            ->willReturnSelf();
        $productOptionMock->expects($this->once())
            ->method('setExtensionAttributes')
            ->with($this->productOptionExtensionAttributes)
            ->willReturnSelf();
        $cartItemMock->expects($this->once())->method('setProductOption')->with($productOptionMock)->willReturnSelf();

        $this->assertEquals($cartItemMock, $this->model->processOptions($cartItemMock));
    }

    public function testProcessProductOptionsIfOptionsExist()
    {
        $optionId = 'option_id';
        $optionValue = 'option_value';

        $customOption = $this->getMock('\Magento\Catalog\Model\Product\Configuration\Item\Option', [], [], '', false);
        $customOption->expects($this->once())->method('getValue')->willReturn(serialize([$optionId => $optionValue]));
        $productMock = $this->getMock('\Magento\Catalog\Model\Product', [], [], '', false);
        $productMock->expects($this->once())->method('getCustomOption')->with('attributes')->willReturn($customOption);

        $cartItemMock = $this->getMock(
            '\Magento\Quote\Model\Quote\Item',
            ['getProduct', 'getProductOption', 'setProductOption'],
            [],
            '',
            false
        );

        $optionValueMock = $this->getMock('\Magento\ConfigurableProduct\Api\Data\ConfigurableItemOptionValueInterface');
        $this->optionValueFactoryMock->expects($this->once())->method('create')->willReturn($optionValueMock);
        $optionValueMock->expects($this->once())->method('setOptionId')->with($optionId)->willReturnSelf();
        $optionValueMock->expects($this->once())->method('setOptionValue')->with($optionValue)->willReturnSelf();

        $this->productOptionExtensionAttributes->expects($this->once())
            ->method('setConfigurableItemOptions')
            ->with([$optionValueMock])
            ->willReturnSelf();

        $productOptionMock = $this->getMock('\Magento\Quote\Api\Data\ProductOptionInterface');
        $productOptionMock->expects(static::exactly(2))
            ->method('getExtensionAttributes')
            ->willReturn($this->productOptionExtensionAttributes);

        $cartItemMock->expects($this->once())->method('getProduct')->willReturn($productMock);
        $cartItemMock->expects($this->exactly(2))->method('getProductOption')->willReturn($productOptionMock);

        $productOptionMock->expects($this->once())
            ->method('setExtensionAttributes')
            ->with($this->productOptionExtensionAttributes)
            ->willReturnSelf();
        $cartItemMock->expects($this->once())->method('setProductOption')->with($productOptionMock)->willReturnSelf();

        $this->assertEquals($cartItemMock, $this->model->processOptions($cartItemMock));
    }
}
