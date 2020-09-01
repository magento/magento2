<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Bundle\Test\Unit\Model;

use Magento\Bundle\Api\Data\BundleOptionInterfaceFactory;
use Magento\Bundle\Model\BundleOption;
use Magento\Bundle\Model\CartItemProcessor;
use Magento\Catalog\Model\Product\Type;
use Magento\Framework\DataObject;
use Magento\Framework\DataObject\Factory;
use Magento\Quote\Api\Data\ProductOptionExtensionFactory;
use Magento\Quote\Api\Data\ProductOptionExtensionInterface;
use Magento\Quote\Api\Data\ProductOptionInterfaceFactory;
use Magento\Quote\Model\Quote\Item;
use Magento\Quote\Model\Quote\ProductOption;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CartItemProcessorTest extends TestCase
{
    /**
     * @var MockObject
     */
    protected $objectFactoryMock;

    /**
     * @var MockObject
     */
    protected $productOptionExtensionMock;

    /**
     * @var MockObject
     */
    protected $bundleOptionFactoryMock;

    /**
     * @var MockObject
     */
    protected $productOptionFactoryMock;

    /**
     * @var CartItemProcessor
     */
    protected $model;

    protected function setUp(): void
    {
        $this->objectFactoryMock = $this->createPartialMock(Factory::class, ['create']);
        $this->productOptionExtensionMock = $this->getMockBuilder(
            ProductOptionExtensionFactory::class
        )
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->bundleOptionFactoryMock = $this->createPartialMock(
            BundleOptionInterfaceFactory::class,
            ['create']
        );
        $this->productOptionFactoryMock = $this->createPartialMock(
            ProductOptionInterfaceFactory::class,
            ['create']
        );

        $this->model = new CartItemProcessor(
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

        $bundleOptionMock = $this->createMock(BundleOption::class);
        $cartItemMock = $this->createMock(Item::class);
        $productOptionMock = $this->createMock(ProductOption::class);
        $dataObjectMock = $this->createMock(DataObject::class);
        $optionExtensionMock = $this->getMockBuilder(ProductOptionExtensionInterface::class)
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
        $cartItemMock = $this->createMock(Item::class);
        $this->assertNull($this->model->convertToBuyRequest($cartItemMock));
    }

    public function testProcessProductOptions()
    {
        $optionId = 4;
        $optionSelections = 42;
        $optionQty = 1;
        $bundleOption = [$optionId => $optionSelections, 5 => ""];
        $bundleOptionQty = [$optionId => $optionQty];

        $buyRequestMock = new DataObject(
            [
                'bundle_option' => $bundleOption,
                'bundle_option_qty' => $bundleOptionQty
            ]
        );
        $cartItemMock = $this->createMock(Item::class);
        $bundleOptionMock = $this->createMock(BundleOption::class);
        $productOptionMock = $this->createMock(ProductOption::class);
        $optionExtensionMock = $this->getMockBuilder(ProductOptionExtensionInterface::class)
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
        $cartItemMock = $this->createPartialMock(Item::class, ['getProductType']);
        $cartItemMock->expects($this->once())->method('getProductType')->willReturn(Type::TYPE_SIMPLE);
        $this->assertSame($cartItemMock, $this->model->processOptions($cartItemMock));
    }

    public function testProcessProductOptionsifBundleOptionsNotExists()
    {
        $buyRequestMock = new DataObject(
            []
        );
        $methods = ['getProductType', 'getBuyRequest'];
        $cartItemMock = $this->createPartialMock(
            Item::class,
            $methods
        );
        $cartItemMock->expects($this->once())->method('getProductType')->willReturn(Type::TYPE_BUNDLE);
        $cartItemMock->expects($this->exactly(2))->method('getBuyRequest')->willReturn($buyRequestMock);
        $this->assertSame($cartItemMock, $this->model->processOptions($cartItemMock));
    }
}
