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

class Mage_Sales_Model_Order_InvoiceTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Sales_Model_Order_Invoice
     */
    protected $_model;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject|Mage_Sales_Model_Order
     */
    protected $_orderMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject|Mage_Sales_Model_Order_Payment
     */
    protected $_paymentMock;

    public function setUp()
    {
        $helperManager = new Magento_Test_Helper_ObjectManager($this);
        $this->_orderMock = $this->getMockBuilder('Mage_Sales_Model_Order')
            ->disableOriginalConstructor()
            ->setMethods(array('getPayment'))
            ->getMock();
        $this->_paymentMock = $this->getMockBuilder('Mage_Sales_Model_Order_Payment')
            ->disableOriginalConstructor()
            ->setMethods(array('canVoid'))
            ->getMock();

        $this->_model = $helperManager->getObject('Mage_Sales_Model_Order_Invoice', array());
        $this->_model->setOrder($this->_orderMock);
    }

    /**
     * @dataProvider canVoidDataProvider
     * @param bool $canVoid
     */
    public function testCanVoid($canVoid)
    {
        $this->_orderMock->expects($this->once())->method('getPayment')->will($this->returnValue($this->_paymentMock));
        $this->_paymentMock->expects($this->once())
            ->method('canVoid')
            ->with($this->equalTo($this->_model))
            ->will($this->returnValue($canVoid));

        $this->_model->setState(Mage_Sales_Model_Order_Invoice::STATE_PAID);
        $this->assertEquals($canVoid, $this->_model->canVoid());
    }

    /**
     * @dataProvider canVoidDataProvider
     * @param bool $canVoid
     */
    public function testDefaultCanVoid($canVoid)
    {
        $this->_model->setState(Mage_Sales_Model_Order_Invoice::STATE_PAID);
        $this->_model->setCanVoidFlag($canVoid);

        $this->assertEquals($canVoid, $this->_model->canVoid());
    }

    public function canVoidDataProvider()
    {
        return array(
            array(true),
            array(false)
        );
    }
}
