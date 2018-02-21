<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Msrp\Test\Unit\Helper;


/**
 * Class DataTest
 */
class DataTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Msrp\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $priceCurrencyMock;

    /**
     * @var \Magento\Catalog\Model\Product|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $productMock;

    protected function setUp()
    {
        $this->priceCurrencyMock = $this->getMock('\Magento\Framework\Pricing\PriceCurrencyInterface');
        $this->productMock = $this->getMockBuilder('\Magento\Catalog\Model\Product')
            ->disableOriginalConstructor()
            ->setMethods(['getMsrp', 'getPriceInfo', '__wakeup'])
            ->getMock();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->helper = $objectManager->getObject(
            'Magento\Msrp\Helper\Data',
            [
                'priceCurrency' => $this->priceCurrencyMock,
            ]
        );
    }

    public function testIsMinimalPriceLessMsrp()
    {
        $msrp = 120;
        $convertedFinalPrice = 200;
        $this->priceCurrencyMock->expects($this->any())
            ->method('convertAndRound')
            ->will($this->returnCallback(
                function ($arg) {
                    return round(2 * $arg, 2);
                }
            )
        );

        $finalPriceMock = $this->getMockBuilder('\Magento\Catalog\Pricing\Price\FinalPrice')
            ->disableOriginalConstructor()
            ->getMock();
        $finalPriceMock->expects($this->any())
            ->method('getValue')
            ->will($this->returnValue($convertedFinalPrice));

        $priceInfoMock = $this->getMockBuilder('\Magento\Framework\Pricing\PriceInfo\Base')
            ->disableOriginalConstructor()
            ->getMock();
        $priceInfoMock->expects($this->once())
            ->method('getPrice')
            ->with(\Magento\Catalog\Pricing\Price\FinalPrice::PRICE_CODE)
            ->will($this->returnValue($finalPriceMock));

        $this->productMock->expects($this->any())
            ->method('getMsrp')
            ->will($this->returnValue($msrp));
        $this->productMock->expects($this->any())
            ->method('getPriceInfo')
            ->will($this->returnValue($priceInfoMock));

        $result = $this->helper->isMinimalPriceLessMsrp($this->productMock);
        $this->assertTrue($result, "isMinimalPriceLessMsrp returned incorrect value");
    }
}
