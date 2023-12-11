<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GroupedProduct\Test\Unit\Model\Product\Type\Grouped;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Option;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\GroupedProduct\Model\Product\Type\Grouped;
use Magento\GroupedProduct\Model\Product\Type\Grouped\Price;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PriceTest extends TestCase
{
    /**
     * @var Price
     */
    protected $finalPriceModel;

    /**
     * @var MockObject
     */
    protected $productMock;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->productMock = $this->createMock(Product::class);

        $helper = new ObjectManager($this);
        $this->finalPriceModel = $helper->getObject(
            Price::class,
            []
        );
    }

    /**
     * @return void
     * @covers \Magento\GroupedProduct\Model\Product\Type\Grouped\Price::getFinalPrice
     */
    public function testGetFinalPriceIfQtyIsNullAndFinalPriceExist(): void
    {
        $finalPrice = 15;

        $this->productMock->expects(
            $this->any()
        )->method(
            'getCalculatedFinalPrice'
        )->willReturn(
            $finalPrice
        );

        $this->productMock->expects($this->never())->method('hasCustomOptions');

        $this->assertEquals($finalPrice, $this->finalPriceModel->getFinalPrice(null, $this->productMock));
    }

    /**
     * @param array $associatedProducts
     * @param array $options
     * @param $expectedPriceCall
     * @param $expectedFinalPrice
     *
     * @return void
     * @dataProvider getFinalPriceDataProvider
     * @covers \Magento\GroupedProduct\Model\Product\Type\Grouped\Price::getFinalPrice
     */
    public function testGetFinalPrice(
        array $associatedProducts,
        array $options,
        $expectedPriceCall,
        $expectedFinalPrice
    ): void {
        $rawFinalPrice = 10;

        $this->productMock->expects(
            $this->any()
        )->method(
            'getCalculatedFinalPrice'
        )->willReturn(
            $rawFinalPrice
        );

        //mock for parent::getFinal price call
        $this->productMock->expects($this->any())->method('getPrice')->willReturn($rawFinalPrice);

        $this->productMock
            ->method('setFinalPrice')
            ->withConsecutive([], [], [], [], [], [$rawFinalPrice])
            ->willReturnOnConsecutiveCalls(null, null, null, null, null, $this->productMock);

        $expectedPriceCallWithArgs = [];

        for ($index = 0; $index < $expectedPriceCall; $index++) {
            $expectedPriceCallWithArgs[] = [];
        }
        $expectedPriceCallWithArgs[] = [$expectedFinalPrice];

        $this->productMock
            ->method('setFinalPrice')
            ->withConsecutive(...$expectedPriceCallWithArgs);

        $this->productMock->expects(
            $this->any()
        )->method(
            'getData'
        )->with(
            'final_price'
        )->willReturn(
            $rawFinalPrice
        );

        //test method
        $this->productMock->expects($this->once())->method('hasCustomOptions')->willReturn(true);

        $productTypeMock = $this->createMock(Grouped::class);

        $this->productMock->expects(
            $this->once()
        )->method(
            'getTypeInstance'
        )->willReturn(
            $productTypeMock
        );

        $this->productMock->expects($this->any())->method('getStore')->willReturn('store1');

        $productTypeMock->expects(
            $this->once()
        )->method(
            'setStoreFilter'
        )->with(
            'store1',
            $this->productMock
        )->willReturn(
            $productTypeMock
        );

        $productTypeMock->expects(
            $this->once()
        )->method(
            'getAssociatedProducts'
        )->with(
            $this->productMock
        )->willReturn(
            $associatedProducts
        );

        $this->productMock->expects($this->any())->method('getCustomOption')->willReturnMap($options);

        $this->assertEquals($rawFinalPrice, $this->finalPriceModel->getFinalPrice(1, $this->productMock));
    }

    /**
     * Data provider for testGetFinalPrice.
     *
     * @return array
     */
    public function getFinalPriceDataProvider(): array
    {
        $optionMock = $this->getMockBuilder(Option::class)
            ->addMethods(['getValue'])
            ->onlyMethods(['__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();
        /* quantity of options */
        $optionMock->expects($this->any())->method('getValue')->willReturn(5);

        return [
            'custom_option_null' => [
                'associatedProducts' => [],
                'options' => [[], []],
                'expectedPriceCall' => 5, /* product call number to check final price formed correctly */
                'expectedFinalPrice' => 10 /* 10(product price) + 2(options count) * 5(qty) * 5(option price) */
            ],
            'custom_option_exist' => [
                'associatedProducts' => $this->generateAssociatedProducts(),
                'options' => [
                    ['associated_product_1', false],
                    ['associated_product_2', $optionMock],
                    ['associated_product_3', $optionMock]
                ],
                'expectedPriceCall' => 15, /* product call number to check final price formed correctly */
                'expectedFinalPrice' => 35 /* 10(product price) + 2(options count) * 5(qty) * 5(option price) */
            ]
        ];
    }

    /**
     * Generate associated product for every custom option.
     *
     * @return array
     */
    protected function generateAssociatedProducts(): array
    {
        $childProductMock = $this->createPartialMock(
            Product::class,
            ['getId', 'getFinalPrice', '__wakeup']
        );
        /* price for option taking into account quantity discounts */
        $childProductMock->expects($this->any())->method('getFinalPrice')->with(5)->willReturn(5);

        $associatedProducts = [];
        for ($i = 0; $i <= 2; $i++) {
            $childProduct = clone $childProductMock;
            $childProduct->expects($this->once())->method('getId')->willReturn($i);
            $associatedProducts[] = $childProduct;
        }

        return $associatedProducts;
    }
}
