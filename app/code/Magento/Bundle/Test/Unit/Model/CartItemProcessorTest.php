<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Test\Unit\Model;

use Magento\Catalog\Model\Product\Type;

class CartItemProcessorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $productOptionExtensionMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $bundleOptionFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $productOptionFactoryMock;

    /**
     * @var \Magento\Bundle\Model\CartItemProcessor
     */
    protected $model;

    protected function setUp()
    {
        $this->objectFactoryMock = $this->getMock('\Magento\Framework\DataObject\Factory', ['create'], [], '', false);
        $this->productOptionExtensionMock = $this->getMock(
            '\Magento\Quote\Api\Data\ProductOptionExtensionFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->bundleOptionFactoryMock = $this->getMock(
            '\Magento\Bundle\Api\Data\BundleOptionInterfaceFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->productOptionFactoryMock = $this->getMock(
            '\Magento\Quote\Api\Data\ProductOptionInterfaceFactory',
            ['create'],
            [],
            '',
            false
        );

        $this->model = new \Magento\Bundle\Model\CartItemProcessor(
            $this->objectFactoryMock,
            $this->productOptionExtensionMock,
            $this->bundleOptionFactoryMock,
            $this->productOptionFactoryMock
        );
    }

    public function testConvertToBuyRequest()
    {
        $optionSelections = [42];
        $optionQty = 1;
        $optionId = 4;

        $bundleOptionMock = $this->getMock('\Magento\Bundle\Model\BundleOption', [], [], '', false);
        $cartItemMock = $this->getMock('\Magento\Quote\Model\Quote\Item', [], [], '', false);
        $productOptionMock = $this->getMock('\Magento\Quote\Model\Quote\ProductOption', [], [], '', false);
        $dataObjectMock = $this->getMock('\Magento\Framework\DataObject');
        $optionExtensionMock = $this->getMock(
            '\Magento\Quote\Api\Data\ProductOptionExtensionInterface',
            [
                'getBundleOptions',
                'getCustomOptions',
                'setCustomOptions',
                'setBundleOptions',
                'getDownloadableOption',
                'setDownloadableOption',
                'getConfigurableItemOptions',
                'setConfigurableItemOptions'
            ],
            [],
            '',
            false
        );
        $requestDataMock = [
            'bundle_option' => [$optionId => $optionSelections],
            'bundle_option_qty' => [$optionId => $optionQty]
        ];

        $cartItemMock->expects($this->atLeastOnce())->method('getProductOption')->willReturn($productOptionMock);
        $productOptionMock->expects($this->atLeastOnce())->method('getExtensionAttributes')
            ->willReturn($optionExtensionMock);
        $optionExtensionMock->expects($this->atLeastOnce())->method('getBundleOptions')
            ->willReturn([$bundleOptionMock]);
        $bundleOptionMock->expects($this->once())->method('getOptionSelections')->willReturn($optionSelections);
        $bundleOptionMock->expects($this->once())->method('getOptionQty')->willReturn($optionQty);
        $bundleOptionMock->expects($this->atLeastOnce())->method('getOptionId')->willReturn($optionId);
        $this->objectFactoryMock->expects($this->once())->method('create')->with($requestDataMock)
            ->willReturn($dataObjectMock);

        $this->assertEquals($dataObjectMock, $this->model->convertToBuyRequest($cartItemMock));
    }

    public function testConvertToBuyRequestInvalidData()
    {
        $cartItemMock = $this->getMock('\Magento\Quote\Model\Quote\Item', [], [], '', false);
        $this->assertNull($this->model->convertToBuyRequest($cartItemMock));
    }

    public function testProcessProductOptions()
    {
        $optionId = 4;
        $optionSelections = 42;
        $optionQty = 1;
        $bundleOption = [$optionId => $optionSelections, 5 => ""];
        $bundleOptionQty = [$optionId => $optionQty];

        $buyRequestMock = new \Magento\Framework\DataObject(
            [
                'bundle_option' => $bundleOption,
                'bundle_option_qty' => $bundleOptionQty
            ]
        );
        $cartItemMock = $this->getMock('\Magento\Quote\Model\Quote\Item', [], [], '', false);
        $bundleOptionMock = $this->getMock('\Magento\Bundle\Model\BundleOption', [], [], '', false);
        $productOptionMock = $this->getMock('\Magento\Quote\Model\Quote\ProductOption', [], [], '', false);
        $optionExtensionMock = $this->getMock(
            '\Magento\Quote\Api\Data\ProductOptionExtensionInterface',
            [
                'getBundleOptions',
                'getCustomOptions',
                'setCustomOptions',
                'setBundleOptions',
                'getDownloadableOption',
                'setDownloadableOption',
                'getConfigurableItemOptions',
                'setConfigurableItemOptions'
            ],
            [],
            '',
            false
        );

        $cartItemMock->expects($this->once())->method('getProductType')->willReturn(Type::TYPE_BUNDLE);
        $cartItemMock->expects($this->atLeastOnce())->method('getBuyRequest')->willReturn($buyRequestMock);
        $this->bundleOptionFactoryMock->expects($this->once())->method('create')->willReturn($bundleOptionMock);
        $bundleOptionMock->expects($this->once())->method('setOptionId')->with($optionId)->willReturnSelf();
        $bundleOptionMock->expects($this->once())->method('setOptionSelections')->with([$optionSelections])
            ->willReturnSelf();
        $bundleOptionMock->expects($this->once())->method('setOptionQty')->with($optionQty)->willReturnSelf();
        $this->productOptionExtensionMock->expects($this->once())->method('create')->willReturn($optionExtensionMock);
        $optionExtensionMock->expects($this->once())->method('setBundleOptions')->with([$bundleOptionMock])
            ->willReturnSelf();
        $cartItemMock->expects($this->atLeastOnce())->method('getProductOption')->willReturn($productOptionMock);
        $productOptionMock->expects($this->once())->method('setExtensionAttributes')->with($optionExtensionMock);

        $this->assertSame($cartItemMock, $this->model->processOptions($cartItemMock));
    }

    public function testProcessProductOptionsInvalidType()
    {
        $cartItemMock = $this->getMock('\Magento\Quote\Model\Quote\Item', ['getProductType'], [], '', false);
        $cartItemMock->expects($this->once())->method('getProductType')->willReturn(Type::TYPE_SIMPLE);
        $this->assertSame($cartItemMock, $this->model->processOptions($cartItemMock));
    }
}
