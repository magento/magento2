<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Msrp\Helper;

use \Magento\Framework\Pricing\PriceInfoInterface;

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

        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);

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
