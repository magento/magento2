<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Test\Unit\Model;

use Magento\Catalog\Model\Product\Type;

class CartItemProcessorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $objectFactoryMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $productOptionExtensionMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $bundleOptionFactoryMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $productOptionFactoryMock;

    /**
     * @var \Magento\Bundle\Model\CartItemProcessor
     */
    protected $model;

    protected function setUp(): void
    {
        $this->objectFactoryMock = $this->createPartialMock(\Magento\Framework\DataObject\Factory::class, ['create']);
        $this->productOptionExtensionMock = $this->getMockBuilder(
            \Magento\Quote\Api\Data\ProductOptionExtensionFactory::class
        )
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->bundleOptionFactoryMock = $this->createPartialMock(
            \Magento\Bundle\Api\Data\BundleOptionInterfaceFactory::class,
            ['create']
        );
        $this->productOptionFactoryMock = $this->createPartialMock(
            \Magento\Quote\Api\Data\ProductOptionInterfaceFactory::class,
            ['create']
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

        $bundleOptionMock = $this->createMock(\Magento\Bundle\Model\BundleOption::class);
        $cartItemMock = $this->createMock(\Magento\Quote\Model\Quote\Item::class);
        $productOptionMock = $this->createMock(\Magento\Quote\Model\Quote\ProductOption::class);
        $dataObjectMock = $this->createMock(\Magento\Framework\DataObject::class);
        $optionExtensionMock = $this->getMockBuilder(\Magento\Quote\Api\Data\ProductOptionExtensionInterface::class)
            ->setMethods(
                [
                    'getBundleOptions',
                    'getCustomOptions',
                    'setCustomOptions',
                    'setBundleOptions',
                    'getDownloadableOption',
                    'setDownloadableOption',
                    'getConfigurableItemOptions',
                    'setConfigurableItemOptions'
                ]
            )
            ->getMockForAbstractClass();
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
        $cartItemMock = $this->createMock(\Magento\Quote\Model\Quote\Item::class);
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
        $cartItemMock = $this->createMock(\Magento\Quote\Model\Quote\Item::class);
        $bundleOptionMock = $this->createMock(\Magento\Bundle\Model\BundleOption::class);
        $productOptionMock = $this->createMock(\Magento\Quote\Model\Quote\ProductOption::class);
        $optionExtensionMock = $this->getMockBuilder(\Magento\Quote\Api\Data\ProductOptionExtensionInterface::class)
            ->setMethods(
                [
                    'getBundleOptions',
                    'getCustomOptions',
                    'setCustomOptions',
                    'setBundleOptions',
                    'getDownloadableOption',
                    'setDownloadableOption',
                    'getConfigurableItemOptions',
                    'setConfigurableItemOptions'
                ]
            )
            ->getMockForAbstractClass();

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
        $cartItemMock = $this->createPartialMock(\Magento\Quote\Model\Quote\Item::class, ['getProductType']);
        $cartItemMock->expects($this->once())->method('getProductType')->willReturn(Type::TYPE_SIMPLE);
        $this->assertSame($cartItemMock, $this->model->processOptions($cartItemMock));
    }

    public function testProcessProductOptionsifBundleOptionsNotExists()
    {
        $buyRequestMock = new \Magento\Framework\DataObject(
            []
        );
        $methods = ['getProductType', 'getBuyRequest'];
        $cartItemMock = $this->createPartialMock(
            \Magento\Quote\Model\Quote\Item::class,
            $methods
        );
        $cartItemMock->expects($this->once())->method('getProductType')->willReturn(Type::TYPE_BUNDLE);
        $cartItemMock->expects($this->exactly(2))->method('getBuyRequest')->willReturn($buyRequestMock);
        $this->assertSame($cartItemMock, $this->model->processOptions($cartItemMock));
    }
}
