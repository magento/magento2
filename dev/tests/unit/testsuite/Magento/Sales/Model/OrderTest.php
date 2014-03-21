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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Test class for \Magento\Sales\Model\Order
 */
namespace Magento\Sales\Model;

class OrderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Prepare items for the order
     *
     * @param \PHPUnit_Framework_MockObject_MockObject $order
     * @param bool $allInvoiced
     */
    protected function _prepareOrderItems($order, $allInvoiced)
    {
        $items = array();
        if (!$allInvoiced) {
            $item = $this->getMockBuilder(
                'Magento\Sales\Model\Order\Item'
            )->setMethods(
                array('getQtyToInvoice', 'isDeleted', '__wakeup')
            )->disableOriginalConstructor()->getMock();
            $item->expects($this->any())->method('getQtyToInvoice')->will($this->returnValue(1));
            $item->expects($this->any())->method('isDeleted')->will($this->returnValue(false));
            $items[] = $item;
        }

        $itemsProperty = new \ReflectionProperty('Magento\Sales\Model\Order', '_items');
        $itemsProperty->setAccessible(true);
        $itemsProperty->setValue($order, $items);
    }

    /**
     * Prepare payment for the order
     *
     * @param \PHPUnit_Framework_MockObject_MockObject $order
     * @param array $mockedMethods
     * @return \Magento\Sales\Model\Order\Payment|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function _prepareOrderPayment($order, $mockedMethods = array())
    {
        $payment = $this->getMockBuilder('Magento\Sales\Model\Order\Payment')->disableOriginalConstructor()->getMock();
        foreach ($mockedMethods as $method => $value) {
            $payment->expects($this->any())->method($method)->will($this->returnValue($value));
        }
        $payment->expects($this->any())->method('isDeleted')->will($this->returnValue(false));

        $itemsProperty = new \ReflectionProperty('Magento\Sales\Model\Order', '_payments');
        $itemsProperty->setAccessible(true);
        $itemsProperty->setValue($order, array($payment));
        return $payment;
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
        $helper = new \Magento\TestFramework\Helper\ObjectManager($this);
        /** @var Order $order */
        $order = $helper->getObject('Magento\Sales\Model\Order');
        foreach ($actionFlags as $action => $flag) {
            $order->setActionFlag($action, $flag);
        }
        $order->setData('state', $orderState);
        $this->_prepareOrderPayment(
            $order,
            array('canReviewPayment' => $canReviewPayment, 'canFetchTransactionInfo' => $canUpdatePayment)
        );
        $this->_prepareOrderItems($order, $allInvoiced);

        // Calculate result
        $expectedResult = true;
        if ((!isset(
            $actionFlags[\Magento\Sales\Model\Order::ACTION_FLAG_UNHOLD]
        ) ||
            $actionFlags[\Magento\Sales\Model\Order::ACTION_FLAG_UNHOLD] !== false) &&
            $orderState == \Magento\Sales\Model\Order::STATE_HOLDED
        ) {
            $expectedResult = false;
        }
        if ($orderState == \Magento\Sales\Model\Order::STATE_PAYMENT_REVIEW && !$canReviewPayment && $canUpdatePayment
        ) {
            $expectedResult = false;
        }
        if ($allInvoiced || in_array(
            $orderState,
            array(
                \Magento\Sales\Model\Order::STATE_CANCELED,
                \Magento\Sales\Model\Order::STATE_COMPLETE,
                \Magento\Sales\Model\Order::STATE_CLOSED,
                \Magento\Sales\Model\Order::STATE_PAYMENT_REVIEW
            )
        )
        ) {
            $expectedResult = false;
        }
        if (isset(
            $actionFlags[\Magento\Sales\Model\Order::ACTION_FLAG_CANCEL]
        ) && $actionFlags[\Magento\Sales\Model\Order::ACTION_FLAG_CANCEL] === false
        ) {
            $expectedResult = false;
        }

        $this->assertEquals($expectedResult, $order->canCancel());
    }

    /**
     * Get action flags
     *
     * @return array
     */
    protected function _getActionFlagsValues()
    {
        return array(
            array(),
            array(
                \Magento\Sales\Model\Order::ACTION_FLAG_UNHOLD => false,
                \Magento\Sales\Model\Order::ACTION_FLAG_CANCEL => false
            ),
            array(
                \Magento\Sales\Model\Order::ACTION_FLAG_UNHOLD => false,
                \Magento\Sales\Model\Order::ACTION_FLAG_CANCEL => true
            )
        );
    }

    /**
     * Get order statuses
     *
     * @return array
     */
    protected function _getOrderStatuses()
    {
        return array(
            \Magento\Sales\Model\Order::STATE_HOLDED,
            \Magento\Sales\Model\Order::STATE_PAYMENT_REVIEW,
            \Magento\Sales\Model\Order::STATE_CANCELED,
            \Magento\Sales\Model\Order::STATE_COMPLETE,
            \Magento\Sales\Model\Order::STATE_CLOSED,
            \Magento\Sales\Model\Order::STATE_PROCESSING
        );
    }

    public function canCancelDataProvider()
    {
        $boolValues = array(true, false);

        $data = array();
        foreach ($this->_getActionFlagsValues() as $actionFlags) {
            foreach ($this->_getOrderStatuses() as $status) {
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

    /**
     * @param array $actionFlags
     * @param string $orderState
     * @dataProvider canVoidPaymentDataProvider
     */
    public function testCanVoidPayment($actionFlags, $orderState)
    {
        $helper = new \Magento\TestFramework\Helper\ObjectManager($this);
        /** @var Order $order */
        $order = $helper->getObject('Magento\Sales\Model\Order');
        foreach ($actionFlags as $action => $flag) {
            $order->setActionFlag($action, $flag);
        }
        $order->setData('state', $orderState);
        $payment = $this->_prepareOrderPayment($order);
        $canVoidOrder = true;
        if ($orderState == \Magento\Sales\Model\Order::STATE_PAYMENT_REVIEW) {
            $canVoidOrder = false;
        }
        if ($orderState == \Magento\Sales\Model\Order::STATE_HOLDED && (!isset(
            $actionFlags[\Magento\Sales\Model\Order::ACTION_FLAG_UNHOLD]
        ) || $actionFlags[\Magento\Sales\Model\Order::ACTION_FLAG_UNHOLD] !== false)
        ) {
            $canVoidOrder = false;
        }

        $expected = false;
        if ($canVoidOrder) {
            $expected = 'some value';
            $payment->expects(
                $this->once()
            )->method(
                'canVoid'
            )->with(
                new \PHPUnit_Framework_Constraint_IsIdentical($payment)
            )->will(
                $this->returnValue($expected)
            );
        } else {
            $payment->expects($this->never())->method('canVoid');
        }
        $this->assertEquals($expected, $order->canVoidPayment());
    }

    public function canVoidPaymentDataProvider()
    {
        $data = array();
        foreach ($this->_getActionFlagsValues() as $actionFlags) {
            foreach ($this->_getOrderStatuses() as $status) {
                $data[] = array($actionFlags, $status);
            }
        }
        return $data;
    }
}
