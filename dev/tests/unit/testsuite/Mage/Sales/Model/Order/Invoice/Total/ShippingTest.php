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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Sales_Model_Order_Invoice_Total_ShippingTest extends PHPUnit_Framework_TestCase
{
    /**
     * Retrieve new invoice collection from an array of invoices' data
     *
     * @param array $invoicesData
     * @return Varien_Data_Collection
     */
    protected function _getInvoiceCollection(array $invoicesData)
    {
        $className = 'Mage_Sales_Model_Order_Invoice';
        $result = new Varien_Data_Collection();
        $objectManagerHelper = new Magento_Test_Helper_ObjectManager($this);
        foreach ($invoicesData as $oneInvoiceData) {
            $arguments = $objectManagerHelper->getConstructArguments($className, array('data' => $oneInvoiceData));
            /** @var $prevInvoice Mage_Sales_Model_Order_Invoice */
            $prevInvoice = $this->getMock($className, array('_init'), $arguments);
            $result->addItem($prevInvoice);
        }
        return $result;
    }

    /**
     * @dataProvider collectDataProvider
     * @param array $prevInvoicesData
     * @param float $orderShipping
     * @param float $invoiceShipping
     * @param float $expectedShipping
     */
    public function testCollect(array $prevInvoicesData, $orderShipping, $invoiceShipping, $expectedShipping)
    {
        /** @var $order Mage_Sales_Model_Order|PHPUnit_Framework_MockObject_MockObject */
        $order = $this->getMock('Mage_Sales_Model_Order', array('_init', 'getInvoiceCollection'), array(), '', false);
        $order->setData('shipping_amount', $orderShipping);
        $order->expects($this->any())
            ->method('getInvoiceCollection')
            ->will($this->returnValue($this->_getInvoiceCollection($prevInvoicesData)))
        ;
        /** @var $invoice Mage_Sales_Model_Order_Invoice|PHPUnit_Framework_MockObject_MockObject */
        $invoice = $this->getMock('Mage_Sales_Model_Order_Invoice', array('_init'), array(), '', false);
        $invoice->setData('shipping_amount', $invoiceShipping);
        $invoice->setOrder($order);

        $total = new Mage_Sales_Model_Order_Invoice_Total_Shipping();
        $total->collect($invoice);

        $this->assertEquals($expectedShipping, $invoice->getShippingAmount());
    }

    public static function collectDataProvider()
    {
        return array(
            'no previous invoices' => array(
                'prevInvoicesData' => array(array()),
                'orderShipping'    => 10.00,
                'invoiceShipping'  => 5.00,
                'expectedShipping' => 10.00
            ),
            'zero shipping in previous invoices' => array(
                'prevInvoicesData' => array(array('shipping_amount' => '0.0000')),
                'orderShipping'    => 10.00,
                'invoiceShipping'  => 5.00,
                'expectedShipping' => 10.00
            ),
            'non-zero shipping in previous invoices' => array(
                'prevInvoicesData' => array(array('shipping_amount' => '10.000')),
                'orderShipping'    => 10.00,
                'invoiceShipping'  => 5.00,
                'expectedShipping' => 0
            ),
        );
    }
}
