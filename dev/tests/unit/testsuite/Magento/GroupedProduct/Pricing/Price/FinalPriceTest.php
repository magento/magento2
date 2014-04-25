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

namespace Magento\GroupedProduct\Pricing\Price;

/**
 * Class FinalPriceTest
 */
class FinalPriceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\GroupedProduct\Pricing\Price\FinalPrice
     */
    protected $finalPrice;

    /**
     * @var \Magento\GroupedProduct\Model\Product\Type\Grouped|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $typeInstanceMock;

    /**
     * @var \Magento\Catalog\Model\Product|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $salableItemMock;

    /**
     * @var \Magento\Framework\Pricing\Adjustment\Calculator|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $calculatorMock;

    /**
     * @var \Magento\Framework\Pricing\PriceInfo\Base|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $priceInfoMock;

    /**
     * @var \Magento\Catalog\Model\Product|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $productMock;

    /**
     * @var \Magento\Framework\Pricing\Amount\AmountInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $amountMock;

    /**
     * @var \Magento\Catalog\Pricing\Price\FinalPrice|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $priceTypeMock;

    /**
     * Setup
     */
    public function setUp()
    {
        $this->salableItemMock =  $this->getMock('Magento\Catalog\Model\Product', [], [], '', false);
        $this->productMock = $this->getMock('Magento\Catalog\Model\Product', [], [], '', false);
        $this->amountMock = $this->getMock('Magento\Framework\Pricing\Amount\Base', [], [], '', false);
        $this->calculatorMock = $this->getMock('Magento\Framework\Pricing\Adjustment\Calculator', [], [], '', false);
        $this->priceInfoMock = $this->getMock('Magento\Framework\Pricing\PriceInfo\Base', [], [], '', false);
        $this->typeInstanceMock = $this->getMock('Magento\GroupedProduct\Model\Product\Type\Grouped',
            [], [], '', false);
        $this->priceTypeMock = $this->getMock('Magento\Catalog\Pricing\Price\FinalPrice', [], [], '', false);

        $this->finalPrice = new \Magento\GroupedProduct\Pricing\Price\FinalPrice
        (
            $this->salableItemMock,
            $this->calculatorMock
        );
    }

    public function testGetMinProduct()
    {
        $valueMap = [
            [90],
            [70]
        ];
        $this->salableItemMock->expects($this->once())
            ->method('getTypeInstance')
            ->will($this->returnValue($this->typeInstanceMock));

        $this->typeInstanceMock->expects($this->once())
            ->method('getAssociatedProducts')
            ->with($this->equalTo($this->salableItemMock))
            ->will($this->returnValue([$this->productMock, $this->productMock]));

        $this->productMock->expects($this->exactly(2))
            ->method('setQty')
            ->with($this->equalTo(\Magento\Framework\Pricing\PriceInfoInterface::PRODUCT_QUANTITY_DEFAULT));

        $this->productMock->expects($this->exactly(2))
            ->method('getPriceInfo')
            ->will($this->returnValue($this->priceInfoMock));

        $this->priceInfoMock->expects($this->exactly(2))
            ->method('getPrice')
            ->with($this->equalTo(\Magento\Catalog\Pricing\Price\FinalPriceInterface::PRICE_TYPE_FINAL))
            ->will($this->returnValue($this->priceTypeMock));

        $this->priceTypeMock->expects($this->exactly(2))
            ->method('getValue')
            ->will($this->returnValueMap($valueMap));
        $this->assertEquals($this->finalPrice->getMinProduct(), $this->productMock);
    }
}
