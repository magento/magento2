<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Bundle\Test\Unit\Pricing\Price;

use Magento\Bundle\Pricing\Price\BundleOptionPrice;
use Magento\Catalog\Pricing\Price\CustomOptionPrice;
use Magento\Bundle\Model\Product\Price;
use Magento\Catalog\Api\ProductCustomOptionRepositoryInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * @SuppressWarnings(PHPMD)
 */
class FinalPriceTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Bundle\Pricing\Price\FinalPrice */
    protected $finalPrice;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var \Magento\Framework\Pricing\SaleableInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $saleableInterfaceMock;

    /** @var float */
    protected $quantity = 1.;

    /** @var float*/
    protected $baseAmount;

    /** @var \Magento\Bundle\Pricing\Adjustment\BundleCalculatorInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $bundleCalculatorMock;

    /** @var \Magento\Framework\Pricing\PriceInfo\Base |\PHPUnit_Framework_MockObject_MockObject */
    protected $priceInfoMock;

    /** @var \Magento\Catalog\Pricing\Price\BasePrice|\PHPUnit_Framework_MockObject_MockObject */
    protected $basePriceMock;

    /** @var BundleOptionPrice|\PHPUnit_Framework_MockObject_MockObject */
    protected $bundleOptionMock;

    /** @var CustomOptionPrice|\PHPUnit_Framework_MockObject_MockObject */
    protected $customOptionPriceMock;

    /**
     * @var PriceCurrencyInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $priceCurrencyMock;

    /**
     * @var ProductCustomOptionRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productOptionRepositoryMock;

    /**
     * @return void
     */
    protected function prepareMock()
    {
        $this->saleableInterfaceMock = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->disableOriginalConstructor()
            ->setMethods(['getPriceType', 'getPriceInfo'])
            ->getMock();
        $this->bundleCalculatorMock = $this->getMock(
            \Magento\Bundle\Pricing\Adjustment\BundleCalculatorInterface::class
        );

        $this->basePriceMock = $this->getMock(\Magento\Catalog\Pricing\Price\BasePrice::class, [], [], '', false);
        $this->basePriceMock->expects($this->any())
            ->method('getValue')
            ->will($this->returnValue($this->baseAmount));

        $this->bundleOptionMock = $this->getMockBuilder(\Magento\Bundle\Pricing\Price\BundleOptionPrice::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->customOptionPriceMock = $this->getMockBuilder(\Magento\Catalog\Pricing\Price\CustomOptionPrice::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->priceInfoMock = $this->getMock(\Magento\Framework\Pricing\PriceInfo\Base::class, [], [], '', false);

        $this->priceInfoMock->expects($this->atLeastOnce())
            ->method('getPrice')
            ->will($this->returnValueMap([
                [\Magento\Catalog\Pricing\Price\BasePrice::PRICE_CODE, $this->basePriceMock],
                [BundleOptionPrice::PRICE_CODE, $this->bundleOptionMock],
                [CustomOptionPrice::PRICE_CODE, $this->customOptionPriceMock],
            ]));

        $this->saleableInterfaceMock->expects($this->once())
            ->method('getPriceInfo')
            ->will($this->returnValue($this->priceInfoMock));

        $this->priceCurrencyMock = $this->getMock(\Magento\Framework\Pricing\PriceCurrencyInterface::class);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->finalPrice = new \Magento\Bundle\Pricing\Price\FinalPrice(
            $this->saleableInterfaceMock,
            $this->quantity,
            $this->bundleCalculatorMock,
            $this->priceCurrencyMock
        );

        $this->productOptionRepositoryMock = $this->getMockForAbstractClass(
            ProductCustomOptionRepositoryInterface::class
        );
        $reflection = new \ReflectionClass(get_class($this->finalPrice));
        $reflectionProperty = $reflection->getProperty('productOptionRepository');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($this->finalPrice, $this->productOptionRepositoryMock);
    }

    /**
     * @dataProvider getValueDataProvider
     */
    public function testGetValue($baseAmount, $optionsValue, $result)
    {
        $this->baseAmount = $baseAmount;
        $this->prepareMock();
        $this->bundleOptionMock->expects($this->once())
            ->method('getValue')
            ->will($this->returnValue($optionsValue));

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
     * @dataProvider getValueDataProvider
     */
    public function testGetMaximalPrice($baseAmount)
    {
        $result = 3;
        $this->baseAmount = $baseAmount;
        $this->prepareMock();

        $this->bundleCalculatorMock->expects($this->once())
            ->method('getMaxAmount')
            ->with($this->equalTo($this->baseAmount), $this->equalTo($this->saleableInterfaceMock))
            ->will($this->returnValue($result));
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
            ->with($this->equalTo($this->baseAmount + $optionMaxPrice), $this->equalTo($this->saleableInterfaceMock))
            ->will($this->returnValue($result));
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
            $this->getMockBuilder(\Magento\Catalog\Api\Data\ProductCustomOptionInterface::class)
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
            ->with($this->equalTo($this->baseAmount + $optionMaxPrice), $this->equalTo($this->saleableInterfaceMock))
            ->will($this->returnValue($result));
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
            ->with($this->equalTo($this->baseAmount), $this->equalTo($this->saleableInterfaceMock))
            ->will($this->returnValue($result));
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
            ->with($this->equalTo($this->baseAmount), $this->equalTo($this->saleableInterfaceMock))
            ->will($this->returnValue($result));
        $this->assertSame($result, $this->finalPrice->getPriceWithoutOption());
        //The second call should use cached value
        $this->assertSame($result, $this->finalPrice->getPriceWithoutOption());
    }
}
