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
 * @package     Magento_Sales
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Test class for Mage_Sales_Model_Order
 */
class Mage_Sales_Model_OrderTest extends PHPUnit_Framework_TestCase
{
    /**
     * Prepare items for the order
     *
     * @param PHPUnit_Framework_MockObject_MockObject $order
     * @param bool $allInvoiced
     */
    protected function _prepareOrderItems($order, $allInvoiced)
    {
        $items = array();
        if (!$allInvoiced) {
            $item = $this->getMockBuilder('Mage_Sales_Model_Order_Item')
                ->setMethods(array('getQtyToInvoice', 'isDeleted'))
                ->disableOriginalConstructor()
                ->getMock();
            $item->expects($this->any())
                ->method('getQtyToInvoice')
                ->will($this->returnValue(1));
            $item->expects($this->any())
                ->method('isDeleted')
                ->will($this->returnValue(false));
            $items[] = $item;
        }

        $itemsProperty = new ReflectionProperty('Mage_Sales_Model_Order', '_items');
        $itemsProperty->setAccessible(true);
        $itemsProperty->setValue($order, $items);
    }

    /**
     * Prepare payment for the order
     *
     * @param PHPUnit_Framework_MockObject_MockObject $order
     * @param bool $canReviewPayment
     * @param bool $canUpdatePayment
     */
    protected function _prepareOrderPayment($order, $canReviewPayment, $canUpdatePayment)
    {
        $payment = $this->getMockBuilder('Mage_Sales_Model_Order_Payment')
            ->disableOriginalConstructor()
            ->getMock();
        $payment->expects($this->any())
            ->method('canReviewPayment')
            ->will($this->returnValue($canReviewPayment));
        $payment->expects($this->any())
            ->method('canFetchTransactionInfo')
            ->will($this->returnValue($canUpdatePayment));
        $payment->expects($this->any())
            ->method('isDeleted')
            ->will($this->returnValue(false));

        $itemsProperty = new ReflectionProperty('Mage_Sales_Model_Order', '_payments');
        $itemsProperty->setAccessible(true);
        $itemsProperty->setValue($order, array($payment));
    }

    /**
     * @SuppressWarnings("complexity")
     *
     * @param array $actionFlags
     * @param string $orderState
     * @param bool $canReviewPayment
     * @param bool $canUpdatePayment
     * @param bool $allInvoiced
     * @dataProvider canCancelDataProvider
     */
    public function testCanCancel($actionFlags, $orderState, $canReviewPayment, $canUpdatePayment, $allInvoiced)
    {
        /** @var $order Mage_Sales_Model_Order */
        $order = $this->getMockBuilder('Mage_Sales_Model_Order')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();
        foreach ($actionFlags as $action => $flag) {
            $order->setActionFlag($action, $flag);
        }
        $order->setData('state', $orderState);
        $this->_prepareOrderPayment($order, $canReviewPayment, $canUpdatePayment);
        $this->_prepareOrderItems($order, $allInvoiced);

        // Calculate result
        $expectedResult = true;
        if ((!isset($actionFlags[Mage_Sales_Model_Order::ACTION_FLAG_UNHOLD])
            || $actionFlags[Mage_Sales_Model_Order::ACTION_FLAG_UNHOLD] !== false)
            && $orderState == Mage_Sales_Model_Order::STATE_HOLDED
        ) {
            $expectedResult = false;
        }
        if ($orderState == Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW && !$canReviewPayment && $canUpdatePayment) {
            $expectedResult = false;
        }
        if ($allInvoiced || in_array($orderState, array(
            Mage_Sales_Model_Order::STATE_CANCELED,
            Mage_Sales_Model_Order::STATE_COMPLETE,
            Mage_Sales_Model_Order::STATE_CLOSED
        ))) {
            $expectedResult = false;
        }
        if (isset($actionFlags[Mage_Sales_Model_Order::ACTION_FLAG_CANCEL])
            && $actionFlags[Mage_Sales_Model_Order::ACTION_FLAG_CANCEL] === false
        ) {
            $expectedResult = false;
        }

        $this->assertEquals($expectedResult, $order->canCancel());
    }

    public function canCancelDataProvider()
    {
        $actionFlagsValues = array(
            array(),
            array(
                Mage_Sales_Model_Order::ACTION_FLAG_UNHOLD => false,
                Mage_Sales_Model_Order::ACTION_FLAG_CANCEL => false,
            ),
            array(
                Mage_Sales_Model_Order::ACTION_FLAG_UNHOLD => false,
                Mage_Sales_Model_Order::ACTION_FLAG_CANCEL => true,
            ),
        );
        $boolValues = array(true, false);
        $orderStatuses = array(
            Mage_Sales_Model_Order::STATE_HOLDED,
            Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW,
            Mage_Sales_Model_Order::STATE_CANCELED,
            Mage_Sales_Model_Order::STATE_COMPLETE,
            Mage_Sales_Model_Order::STATE_CLOSED,
            Mage_Sales_Model_Order::STATE_PROCESSING,
        );

        $data = array();
        foreach ($actionFlagsValues as $actionFlags) {
            foreach ($orderStatuses as $status) {
                foreach ($boolValues as $canReviewPayment) {
                    foreach ($boolValues as $canUpdatePayment) {
                        foreach ($boolValues as $allInvoiced) {
                            $data[] = array($actionFlags, $status, $canReviewPayment, $canUpdatePayment, $allInvoiced);
                        }
                    }
                }
            }
        }

        return $data;
    }
}
