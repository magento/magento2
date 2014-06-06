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
namespace Magento\Weee\Helper;

class DataTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $_product;

    /**
     * @var \Magento\Weee\Helper\Data
     */
    protected $_helperData;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_abstractItem;

    protected function setUp()
    {
        $this->_product = $this->getMock('Magento\Catalog\Model\Product', [], [], '', false);
        $scopeConfig = $this->getMock('Magento\Framework\App\Config\ScopeConfigInterface', [], [], '', false);
        $scopeConfig->expects($this->any())->method('getValue')->will($this->returnValue(true));
        $weeeTax = $this->getMock('Magento\Weee\Model\Tax', [], [], '', false);
        $weeeTax->expects($this->any())->method('getWeeeAmount')->will($this->returnValue('11.26'));
        $this->_abstractItem = $this->getMock(
            '\Magento\Sales\Model\Quote\Item\AbstractItem',
            array(
                '__wakeup',
                'setDiscountCalculationPrice',
                'setBaseDiscountCalculationPrice',
                'getDiscountCalculationPrice',
                'getBaseDiscountCalculationPrice',
                'getCalculationPrice',
                'getBaseCalculationPrice',
                'setItemDiscountPrices',
                'getQuote',
                'getAddress',
                'getOptionByCode'
            ),
            array(),
            '',
            false
        );
        $arguments = array(
            'scopeConfig' => $scopeConfig,
            'weeeTax' => $weeeTax
        );
        $helper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->_helperData = $helper->getObject('Magento\Weee\Helper\Data', $arguments);
    }

    public function testGetAmount()
    {
        $this->assertEquals('11.26', $this->_helperData->getAmount($this->_product));
    }

    /**
     * @covers \Magento\Weee\Helper\Data::addItemDiscountPrices
     * @covers \Magento\Weee\Helper\Data::setItemDiscountPrices
     *
     * @param string $getDiscountPrice
     * @param string $basePrice
     * @param string $price
     * @param string $baseDiscountCalculationPrice
     * @param string $callPriceCalculation
     *
     * @dataProvider addItemDiscountPricesDataProvider
     */
    public function testAddItemDiscountPrices(
        $getDiscountPrice, $basePrice, $price, $baseDiscountCalculationPrice, $callPriceCalculation
    ) {
        $this->_abstractItem->expects($this->once())->method('getDiscountCalculationPrice')
            ->will($this->returnValue($getDiscountPrice));
        $this->_abstractItem->expects($this->once())->method('getBaseDiscountCalculationPrice')
            ->will($this->returnValue($getDiscountPrice));
        $this->_abstractItem->expects($this->exactly($callPriceCalculation))->method('getCalculationPrice')
            ->will($this->returnValue($price));
        $this->_abstractItem->expects($this->exactly($callPriceCalculation))->method('getBaseCalculationPrice')
            ->will($this->returnValue($price));
        $this->_abstractItem->expects($this->once())->method('setDiscountCalculationPrice')
            ->with($basePrice);
        $this->_abstractItem->expects($this->once())->method('setBaseDiscountCalculationPrice')
            ->with($baseDiscountCalculationPrice);

        $this->assertEquals(
            $this->_helperData, $this->_helperData->addItemDiscountPrices($this->_abstractItem, $basePrice, $price)
        );
    }

    public function addItemDiscountPricesDataProvider()
    {
        return array(
            array(
                'discount_price' => null,
                'base_price' => '2',
                'price' => '1',
                'base_calculation_price' => '3',
                'call_price_calculation' => 1
            ),
            array(
                'discount_price' => '1',
                'base_price' => '2',
                'price' => '1',
                'base_calculation_price' => '3',
                'call_price_calculation' => 0
            )
        );
    }

}
