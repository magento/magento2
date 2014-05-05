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
     * @var \Magento\Catalog\Model\Product|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $saleableItemMock;

    /**
     * @var \Magento\Framework\Pricing\Adjustment\Calculator|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $calculatorMock;

    /**
     * Setup
     */
    public function setUp()
    {
        $this->saleableItemMock =  $this->getMock('Magento\Catalog\Model\Product', [], [], '', false);
        $this->calculatorMock = $this->getMock('Magento\Framework\Pricing\Adjustment\Calculator', [], [], '', false);

        $this->finalPrice = new \Magento\GroupedProduct\Pricing\Price\FinalPrice
        (
            $this->saleableItemMock,
            null,
            $this->calculatorMock
        );
    }

    public function testGetMinProduct()
    {
        $product1 = $this->getProductMock(10);
        $product2 = $this->getProductMock(20);

        $typeInstanceMock = $this->getMock(
            'Magento\GroupedProduct\Model\Product\Type\Grouped',
            [],
            [],
            '',
            false
        );
        $typeInstanceMock->expects($this->once())
            ->method('getAssociatedProducts')
            ->with($this->equalTo($this->saleableItemMock))
            ->will($this->returnValue([$product1, $product2]));

        $this->saleableItemMock->expects($this->once())
            ->method('getTypeInstance')
            ->will($this->returnValue($typeInstanceMock));

        $this->assertEquals($product1, $this->finalPrice->getMinProduct());
    }

    public function testGetValue()
    {
        $product1 = $this->getProductMock(10);
        $product2 = $this->getProductMock(20);

        $typeInstanceMock = $this->getMock(
            'Magento\GroupedProduct\Model\Product\Type\Grouped',
            [],
            [],
            '',
            false
        );
        $typeInstanceMock->expects($this->once())
            ->method('getAssociatedProducts')
            ->with($this->equalTo($this->saleableItemMock))
            ->will($this->returnValue([$product1, $product2]));

        $this->saleableItemMock->expects($this->once())
            ->method('getTypeInstance')
            ->will($this->returnValue($typeInstanceMock));

        $this->assertEquals(10, $this->finalPrice->getValue());
    }

    protected function getProductMock($price)
    {
        $priceTypeMock = $this->getMock('Magento\Catalog\Pricing\Price\FinalPrice', [], [], '', false);
        $priceTypeMock->expects($this->any())
            ->method('getValue')
            ->will($this->returnValue($price));

        $priceInfoMock = $this->getMock('Magento\Framework\Pricing\PriceInfo\Base', [], [], '', false);
        $priceInfoMock->expects($this->any())
            ->method('getPrice')
            ->with($this->equalTo(\Magento\Catalog\Pricing\Price\FinalPrice::PRICE_CODE))
            ->will($this->returnValue($priceTypeMock));

        $productMock = $this->getMock('Magento\Catalog\Model\Product', [], [], '', false);
        $productMock->expects($this->any())
            ->method('setQty')
            ->with($this->equalTo(\Magento\Framework\Pricing\PriceInfoInterface::PRODUCT_QUANTITY_DEFAULT));
        $productMock->expects($this->any())
            ->method('getPriceInfo')
            ->will($this->returnValue($priceInfoMock));

        return $productMock;
    }
}
