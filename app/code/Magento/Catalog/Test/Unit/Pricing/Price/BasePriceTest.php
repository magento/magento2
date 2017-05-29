<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Catalog\Test\Unit\Pricing\Price;

/**
 * Base price test
 */
class BasePriceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Pricing\Price\BasePrice|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $basePrice;

    /**
     * @var \Magento\Framework\Pricing\PriceInfo\Base |\PHPUnit_Framework_MockObject_MockObject
     */
    protected $priceInfoMock;

    /**
     * @var \Magento\Catalog\Model\Product|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $saleableItemMock;

    /**
     * @var \Magento\Framework\Pricing\Adjustment\Calculator|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $calculatorMock;

    /**
     * @var \Magento\Catalog\Pricing\Price\RegularPrice|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $regularPriceMock;

    /**
     * @var \Magento\Catalog\Pricing\Price\TierPrice|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $tearPriceMock;

    /**
     * @var \Magento\Catalog\Pricing\Price\SpecialPrice|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $specialPriceMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject[]
     */
    protected $prices;

    /**
     * Set up
     */
    protected function setUp()
    {
        $qty = 1;
        $this->saleableItemMock = $this->getMock(\Magento\Catalog\Model\Product::class, [], [], '', false);
        $this->priceInfoMock = $this->getMock(\Magento\Framework\Pricing\PriceInfo\Base::class, [], [], '', false);
        $this->regularPriceMock = $this->getMock(\Magento\Catalog\Pricing\Price\RegularPrice::class, [], [], '', false);
        $this->tearPriceMock = $this->getMock(\Magento\Catalog\Pricing\Price\TierPrice::class, [], [], '', false);
        $this->specialPriceMock = $this->getMock(\Magento\Catalog\Pricing\Price\SpecialPrice::class, [], [], '', false);
        $this->calculatorMock = $this->getMock(
            \Magento\Framework\Pricing\Adjustment\Calculator::class,
            [],
            [],
            '',
            false
        );

        $this->saleableItemMock->expects($this->once())
            ->method('getPriceInfo')
            ->will($this->returnValue($this->priceInfoMock));
        $this->prices = [
            'regular_price' => $this->regularPriceMock,
            'tear_price' => $this->tearPriceMock,
            'special_price' => $this->specialPriceMock,
        ];

        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->basePrice = $helper->getObject(
            \Magento\Catalog\Pricing\Price\BasePrice::class,
            [
                'saleableItem' => $this->saleableItemMock,
                'quantity' => $qty,
                'calculator' => $this->calculatorMock
            ]
        );
    }

    /**
     * test method getValue
     *
     * @dataProvider getValueDataProvider
     */
    public function testGetValue($specialPriceValue, $expectedResult)
    {
        $this->priceInfoMock->expects($this->once())
            ->method('getPrices')
            ->will($this->returnValue($this->prices));
        $this->regularPriceMock->expects($this->exactly(3))
            ->method('getValue')
            ->will($this->returnValue(100));
        $this->tearPriceMock->expects($this->exactly(2))
            ->method('getValue')
            ->will($this->returnValue(99));
        $this->specialPriceMock->expects($this->any())
            ->method('getValue')
            ->will($this->returnValue($specialPriceValue));
        $this->assertSame($expectedResult, $this->basePrice->getValue());
    }

    public function getValueDataProvider()
    {
        return [[77, 77], [0, 0], [false, 99]];
    }

    public function testGetAmount()
    {
        $amount = 20.;

        $priceMock = $this->getMockBuilder(\Magento\Framework\Pricing\Price\PriceInterface::class)
            ->getMockForAbstractClass();

        $this->priceInfoMock->expects($this->once())
            ->method('getPrices')
            ->willReturn([$priceMock]);

        $this->calculatorMock->expects($this->once())
            ->method('getAmount')
            ->with(false, $this->saleableItemMock)
            ->willReturn($amount);

        $this->assertEquals($amount, $this->basePrice->getAmount());
    }
}
