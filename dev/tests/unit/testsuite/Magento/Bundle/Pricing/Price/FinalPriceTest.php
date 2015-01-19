<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Bundle\Pricing\Price;

use Magento\TestFramework\Helper\ObjectManager as ObjectManagerHelper;

class FinalPriceTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Bundle\Pricing\Price\FinalPrice */
    protected $finalPrice;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var \Magento\Framework\Pricing\Object\SaleableInterface|\PHPUnit_Framework_MockObject_MockObject */
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

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $priceCurrencyMock;

    /**
     * @return void
     */
    protected function prepareMock()
    {
        $this->saleableInterfaceMock = $this->getMock('Magento\Catalog\Model\Product', [], [], '', false);
        $this->bundleCalculatorMock = $this->getMock('Magento\Bundle\Pricing\Adjustment\BundleCalculatorInterface');

        $this->basePriceMock = $this->getMock('Magento\Catalog\Pricing\Price\BasePrice', [], [], '', false);
        $this->basePriceMock->expects($this->any())
            ->method('getValue')
            ->will($this->returnValue($this->baseAmount));

        $this->bundleOptionMock = $this->getMockBuilder('Magento\Bundle\Pricing\Price\BundleOptionPrice')
            ->disableOriginalConstructor()
            ->getMock();

        $this->priceInfoMock = $this->getMock('Magento\Framework\Pricing\PriceInfo\Base', [], [], '', false);

        $this->priceInfoMock->expects($this->atLeastOnce())
            ->method('getPrice')
            ->will($this->returnValueMap([
                [\Magento\Catalog\Pricing\Price\BasePrice::PRICE_CODE, $this->basePriceMock],
                [BundleOptionPrice::PRICE_CODE, $this->bundleOptionMock],
            ]));

        $this->saleableInterfaceMock->expects($this->once())
            ->method('getPriceInfo')
            ->will($this->returnValue($this->priceInfoMock));

        $this->priceCurrencyMock = $this->getMock('\Magento\Framework\Pricing\PriceCurrencyInterface');

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->finalPrice = new \Magento\Bundle\Pricing\Price\FinalPrice(
            $this->saleableInterfaceMock,
            $this->quantity,
            $this->bundleCalculatorMock,
            $this->priceCurrencyMock
        );
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
