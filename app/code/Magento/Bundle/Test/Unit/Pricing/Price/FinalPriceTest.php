<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Bundle\Test\Unit\Pricing\Price;

use Magento\Bundle\Model\Product\Price;
use Magento\Bundle\Pricing\Adjustment\BundleCalculatorInterface;
use Magento\Bundle\Pricing\Price\BundleOptionPrice;
use Magento\Bundle\Pricing\Price\FinalPrice;
use Magento\Catalog\Api\Data\ProductCustomOptionInterface;
use Magento\Catalog\Api\ProductCustomOptionRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Pricing\Price\BasePrice;
use Magento\Catalog\Pricing\Price\CustomOptionPrice;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Pricing\PriceInfo\Base;
use Magento\Framework\Pricing\SaleableInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test class for \Magento\Bundle\Pricing\Price\FinalPrice
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class FinalPriceTest extends TestCase
{
    /**
     * @var FinalPrice
     */
    private $finalPrice;

    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var SaleableInterface|MockObject
     */
    private $saleableInterfaceMock;

    /**
     * @var float
     */
    private $quantity = 1.;

    /**
     * @var BundleCalculatorInterface|MockObject
     */
    private $bundleCalculatorMock;

    /**
     * @var PriceCurrencyInterface|MockObject
     */
    private $priceCurrencyMock;

    /**
     * @var ProductCustomOptionRepositoryInterface|MockObject
     */
    private $productOptionRepositoryMock;

    /**
     * @var float
     */
    private $baseAmount;

    /**
     * @var Base|MockObject
     */
    private $priceInfoMock;

    /**
     * @var BasePrice|MockObject
     */
    private $basePriceMock;

    /**
     * @var BundleOptionPrice|MockObject
     */
    private $bundleOptionMock;

    /**
     * @var CustomOptionPrice|MockObject
     */
    private $customOptionPriceMock;

    /**
     * @return void
     */
    protected function prepareMock()
    {
        $this->saleableInterfaceMock = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->setMethods(['getPriceType', 'getPriceInfo'])
            ->getMock();
        $this->bundleCalculatorMock = $this->createMock(
            BundleCalculatorInterface::class
        );

        $this->basePriceMock = $this->createMock(BasePrice::class);
        $this->basePriceMock->method('getValue')
            ->willReturn($this->baseAmount);

        $this->bundleOptionMock = $this->getMockBuilder(BundleOptionPrice::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->customOptionPriceMock = $this->getMockBuilder(CustomOptionPrice::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->priceInfoMock = $this->createMock(Base::class);

        $this->priceInfoMock->expects($this->atLeastOnce())
            ->method('getPrice')
            ->willReturnMap(
                [
                    [BasePrice::PRICE_CODE, $this->basePriceMock],
                    [BundleOptionPrice::PRICE_CODE, $this->bundleOptionMock],
                    [CustomOptionPrice::PRICE_CODE, $this->customOptionPriceMock],
                ]
            );

        $this->saleableInterfaceMock->expects($this->once())
            ->method('getPriceInfo')
            ->willReturn($this->priceInfoMock);

        $this->priceCurrencyMock = $this->getMockForAbstractClass(PriceCurrencyInterface::class);
        $this->productOptionRepositoryMock = $this->getMockForAbstractClass(
            ProductCustomOptionRepositoryInterface::class
        );

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->finalPrice = $this->objectManagerHelper->getObject(
            FinalPrice::class,
            [
                'saleableItem' => $this->saleableInterfaceMock,
                'quantity' => $this->quantity,
                'calculator' => $this->bundleCalculatorMock,
                'priceCurrency' => $this->priceCurrencyMock,
                'productOptionRepository' => $this->productOptionRepositoryMock
            ]
        );
    }

    /**
     * @param $baseAmount
     * @param $optionsValue
     * @param $result
     * @dataProvider getValueDataProvider
     */
    public function testGetValue($baseAmount, $optionsValue, $result)
    {
        $this->baseAmount = $baseAmount;
        $this->prepareMock();
        $this->bundleOptionMock->expects($this->once())
            ->method('getValue')
            ->willReturn($optionsValue);

        $this->assertSame($result, $this->finalPrice->getValue());
    }

    /**
     * @return array
     */
    public function getValueDataProvider()
    {
        return [
            [false, false, 0],
            [0, 1.2, 1.2],
            [1, 2, 3]
        ];
    }

    /**
     * @param $baseAmount
     * @dataProvider getValueDataProvider
     */
    public function testGetMaximalPrice($baseAmount)
    {
        $result = 3;
        $this->baseAmount = $baseAmount;
        $this->prepareMock();

        $this->bundleCalculatorMock->expects($this->once())
            ->method('getMaxAmount')
            ->with($this->baseAmount, $this->saleableInterfaceMock)
            ->willReturn($result);
        $this->assertSame($result, $this->finalPrice->getMaximalPrice());
        //The second call should use cached value
        $this->assertSame($result, $this->finalPrice->getMaximalPrice());
    }

    public function testGetMaximalPriceFixedBundleWithOption()
    {
        $optionMaxPrice = 2;
        $this->baseAmount = 5;
        $result = 7;
        $this->prepareMock();

        $this->saleableInterfaceMock->expects($this->once())
            ->method('getPriceType')
            ->willReturn(Price::PRICE_TYPE_FIXED);
        $this->customOptionPriceMock->expects($this->once())
            ->method('getCustomOptionRange')
            ->with(false)
            ->willReturn($optionMaxPrice);

        $this->bundleCalculatorMock->expects($this->once())
            ->method('getMaxAmount')
            ->with($this->baseAmount + $optionMaxPrice, $this->saleableInterfaceMock)
            ->willReturn($result);
        $this->assertSame($result, $this->finalPrice->getMaximalPrice());
        //The second call should use cached value
        $this->assertSame($result, $this->finalPrice->getMaximalPrice());
    }

    public function testGetMinimalPriceFixedBundleWithOption()
    {
        $optionMaxPrice = 2;
        $this->baseAmount = 5;
        $result = 7;
        $this->prepareMock();
        $customOptions = [
            $this->getMockBuilder(ProductCustomOptionInterface::class)
                ->setMethods(['setProduct'])
                ->getMockForAbstractClass()
        ];

        $this->productOptionRepositoryMock->expects(static::once())
            ->method('getProductOptions')
            ->with($this->saleableInterfaceMock)
            ->willReturn($customOptions);

        $this->saleableInterfaceMock->expects($this->once())
            ->method('getPriceType')
            ->willReturn(Price::PRICE_TYPE_FIXED);
        $this->customOptionPriceMock->expects($this->once())
            ->method('getCustomOptionRange')
            ->with(true)
            ->willReturn($optionMaxPrice);

        $this->bundleCalculatorMock->expects($this->once())
            ->method('getAmount')
            ->with($this->baseAmount + $optionMaxPrice, $this->saleableInterfaceMock)
            ->willReturn($result);
        $this->assertSame($result, $this->finalPrice->getMinimalPrice());
        //The second call should use cached value
        $this->assertSame($result, $this->finalPrice->getMinimalPrice());
    }

    /**
     * @dataProvider getValueDataProvider
     */
    public function testGetMinimalPrice($baseAmount)
    {
        $result = 5;
        $this->baseAmount = $baseAmount;
        $this->prepareMock();

        $this->bundleCalculatorMock->expects($this->once())
            ->method('getAmount')
            ->with($this->baseAmount, $this->saleableInterfaceMock)
            ->willReturn($result);
        $this->assertSame($result, $this->finalPrice->getMinimalPrice());
        //The second call should use cached value
        $this->assertSame($result, $this->finalPrice->getMinimalPrice());
    }

    public function testGetPriceWithoutOption()
    {
        $result = 5;
        $this->prepareMock();
        $this->bundleCalculatorMock->expects($this->once())
            ->method('getAmountWithoutOption')
            ->with($this->baseAmount, $this->saleableInterfaceMock)
            ->willReturn($result);
        $this->assertSame($result, $this->finalPrice->getPriceWithoutOption());
        //The second call should use cached value
        $this->assertSame($result, $this->finalPrice->getPriceWithoutOption());
    }
}
