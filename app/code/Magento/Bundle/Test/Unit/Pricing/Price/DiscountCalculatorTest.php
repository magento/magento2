<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Bundle\Test\Unit\Pricing\Price;

/**
 * Class DiscountCalculatorTest
 */
class DiscountCalculatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Bundle\Pricing\Price\DiscountCalculator
     */
    protected $calculator;

    /**
     * @var \Magento\Catalog\Model\Product|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $productMock;

    /**
     * @var \Magento\Framework\Pricing\PriceInfo\Base |\PHPUnit_Framework_MockObject_MockObject
     */
    protected $priceInfoMock;

    /**
     * @var \Magento\Catalog\Pricing\Price\FinalPrice|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $finalPriceMock;

    /**
     * @var \Magento\Bundle\Pricing\Price\DiscountProviderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $priceMock;

    /**
     * Test setUp
     */
    protected function setUp()
    {
        $this->productMock = $this->getMock(
            \Magento\Catalog\Model\Product::class,
            [],
            [],
            '',
            false
        );
        $this->priceInfoMock = $this->getMock(
            \Magento\Framework\Pricing\PriceInfo\Base::class,
            ['getPrice', 'getPrices'],
            [],
            '',
            false
        );
        $this->finalPriceMock = $this->getMock(
            \Magento\Catalog\Pricing\Price\FinalPrice::class,
            [],
            [],
            '',
            false
        );
        $this->priceMock = $this->getMockForAbstractClass(
            \Magento\Bundle\Pricing\Price\DiscountProviderInterface::class
        );
        $this->calculator = new \Magento\Bundle\Pricing\Price\DiscountCalculator();
    }

    /**
     * Returns price mock with specified %
     *
     * @param int $value
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getPriceMock($value)
    {
        $price = clone $this->priceMock;
        $price->expects($this->exactly(3))
            ->method('getDiscountPercent')
            ->will($this->returnValue($value));
        return $price;
    }

    /**
     * test method calculateDiscount with default price amount
     */
    public function testCalculateDiscountWithDefaultAmount()
    {
        $this->productMock->expects($this->exactly(2))
            ->method('getPriceInfo')
            ->will($this->returnValue($this->priceInfoMock));
        $this->priceInfoMock->expects($this->once())
            ->method('getPrice')
            ->with($this->equalTo(\Magento\Catalog\Pricing\Price\FinalPrice::PRICE_CODE))
            ->will($this->returnValue($this->finalPriceMock));
        $this->finalPriceMock->expects($this->once())
            ->method('getValue')
            ->will($this->returnValue(100));
        $this->priceInfoMock->expects($this->once())
            ->method('getPrices')
            ->will($this->returnValue(
                [
                    $this->getPriceMock(30),
                    $this->getPriceMock(20),
                    $this->getPriceMock(40),
                ]
            )
        );
        $this->assertEquals(20, $this->calculator->calculateDiscount($this->productMock));
    }

    /**
     * test method calculateDiscount with custom price amount
     */
    public function testCalculateDiscountWithCustomAmount()
    {
        $this->productMock->expects($this->once())
            ->method('getPriceInfo')
            ->will($this->returnValue($this->priceInfoMock));
        $this->priceInfoMock->expects($this->once())
            ->method('getPrices')
            ->will($this->returnValue(
                    [
                        $this->getPriceMock(30),
                        $this->getPriceMock(20),
                        $this->getPriceMock(40),
                    ]
                )
            );
        $this->assertEquals(10, $this->calculator->calculateDiscount($this->productMock, 50));
    }
}
