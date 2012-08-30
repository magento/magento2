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
 * @category    Magento
 * @package     Mage_Sales
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Sales_Model_Order_Invoice_Total_ShippingTest extends PHPUnit_Framework_TestCase
{

    protected $_invoice;

    protected $_order;


    protected function setUp()
    {
        $this->_invoice = $this->getMock('Mage_Sales_Model_Order_Invoice', array('getOrder'), array(), '', false);
        $this->_order = $this->getMock('Mage_Sales_Model_Order', array('getInvoiceCollection'), array(),'',false);

        $this->_invoice->expects($this->any())
            ->method('getOrder')
            ->will($this->returnValue($this->_order));
    }
    /**
     * @covers Mage_Sales_Model_Order_Invoice_Total_Shipping::collect
     * @param $collection array
     * @param $shippingAmount float
     * @dataProvider dataGetValuesAndResults
     */
    public function testCollect($collection, $shippingAmount)
    {
        $temp_collection = array();
        foreach($collection as $tempShippingAmount){
            $temp_invoice = $this->getMock('Mage_Sales_Model_Order_Invoice', null, array(), '', false);
            $temp_invoice->setShippingAmount($tempShippingAmount);
            $temp_collection[] = $temp_invoice;
        }

        $this->_order->expects($this->any())
            ->method('getInvoiceCollection')
            ->will($this->returnValue($temp_collection));
        $this->_order->setData('shipping_amount',$shippingAmount);
        $this->_order->setData('invoice_collection', $collection);
        $total = new Mage_Sales_Model_Order_Invoice_Total_Shipping();
        $total->collect($this->_invoice);
        $this->assertEquals($this->_invoice->getShippingAmount(), $this->_order->getShippingAmount());


    }

    public static function dataGetValuesAndResults()
    {
        return array(
            array(
                array("0.0000")
                ,10.00
            ),
            array(
                array("10.000"),
                0
            )
        );
    }
}