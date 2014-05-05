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
namespace Magento\Catalog\Pricing\Price;

/**
 * Final Price test
 */
class FinalPriceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Pricing\Price\FinalPrice
     */
    protected $model;

    /**
     * @var \Magento\Framework\Pricing\PriceInfoInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $priceInfoMock;

    /**
     * @var \Magento\Catalog\Pricing\Price\BasePrice|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $basePriceMock;

    /**
     * @var \Magento\Framework\Pricing\Object\SaleableInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $saleableMock;

    /**
     * @var \Magento\Framework\Pricing\Adjustment\Calculator|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $calculatorMock;

    /**
     * Set up function
     */
    public function setUp()
    {
        $this->saleableMock = $this->getMock('Magento\Catalog\Model\Product', [], [], '', false);
        $this->priceInfoMock = $this->basePriceMock = $this->getMock(
            'Magento\Framework\Pricing\PriceInfo\Base',
            [],
            [],
            '',
            false
        );
        $this->basePriceMock = $this->getMock(
            'Magento\Catalog\Pricing\Price\BasePrice',
            [],
            [],
            '',
            false
        );

        $this->calculatorMock = $this->getMock(
            'Magento\Framework\Pricing\Adjustment\Calculator',
            [],
            [],
            '',
            false
        );

        $this->saleableMock->expects($this->once())
            ->method('getPriceInfo')
            ->will($this->returnValue($this->priceInfoMock));
        $this->priceInfoMock->expects($this->once())
            ->method('getPrice')
            ->with($this->equalTo(\Magento\Catalog\Pricing\Price\BasePrice::PRICE_CODE))
            ->will($this->returnValue($this->basePriceMock));
        $this->model = new \Magento\Catalog\Pricing\Price\FinalPrice($this->saleableMock, 1, $this->calculatorMock);
    }

    /**
     * test for getValue
     */
    public function testGetValue()
    {
        $price = 10;
        $this->basePriceMock->expects($this->once())
            ->method('getValue')
            ->will($this->returnValue($price));
        $result = $this->model->getValue();
        $this->assertEquals($price, $result);
    }

    /**
     * Test getMinimalPrice()
     */
    public function testGetMinimalPrice()
    {
        $basePrice = 10;
        $minimalPrice = 5;
        $this->basePriceMock->expects($this->once())
            ->method('getValue')
            ->will($this->returnValue($basePrice));
        $this->calculatorMock->expects($this->once())
            ->method('getAmount')
            ->with($this->equalTo($basePrice))
            ->will($this->returnValue($minimalPrice));
        $this->saleableMock->expects($this->once())
            ->method('getMinimalPrice')
            ->will($this->returnValue(null));
        $result = $this->model->getMinimalPrice();
        $this->assertEquals($minimalPrice, $result);
    }

    /**
     * Test getMaximalPrice()
     */
    public function testGetMaximalPrice()
    {
        $basePrice = 10;
        $minimalPrice = 5;
        $this->basePriceMock->expects($this->once())
            ->method('getValue')
            ->will($this->returnValue($basePrice));
        $this->calculatorMock->expects($this->once())
            ->method('getAmount')
            ->with($this->equalTo($basePrice))
            ->will($this->returnValue($minimalPrice));
        $result = $this->model->getMaximalPrice();
        $this->assertEquals($minimalPrice, $result);
    }
}
