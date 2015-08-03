<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test class for \Magento\Paypal\Helper\Checkout
 */
namespace Magento\Paypal\Test\Unit\Helper;

class CheckoutTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Checkout\Model\Session|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_session;

    /**
     * @var \Magento\Quote\Model\QuoteFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_quoteFactory;

    /**
     * @var \Magento\Paypal\Helper\Checkout
     */
    protected $_checkout;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->_session = $this->getMockBuilder(
            'Magento\Checkout\Model\Session'
        )->disableOriginalConstructor()->setMethods(
            ['getLastRealOrder', 'replaceQuote', 'unsLastRealOrderId', '__wakeup']
        )->getMock();
        $this->_quoteFactory = $this->getMockBuilder(
            'Magento\Quote\Model\QuoteFactory'
        )->disableOriginalConstructor()->setMethods(
            ['create', '__wakeup']
        )->getMock();

        $this->_checkout = new \Magento\Paypal\Helper\Checkout($this->_session, $this->_quoteFactory);
    }

    /**
     * Get order mock
     *
     * @param bool $hasOrderId
     * @param array $mockMethods
     * @return \Magento\Sales\Model\Order|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getOrderMock($hasOrderId, $mockMethods = [])
    {
        $order = $this->getMockBuilder(
            'Magento\Sales\Model\Order'
        )->disableOriginalConstructor()->setMethods(
            array_merge(['getId', '__wakeup'], $mockMethods)
        )->getMock();
        $order->expects($this->once())->method('getId')->will($this->returnValue($hasOrderId ? 'order id' : null));
        return $order;
    }

    /**
     * @param bool $hasOrderId
     * @param bool $isOrderCancelled
     * @param bool $expectedResult
     * @dataProvider cancelCurrentOrderDataProvider
     */
    public function testCancelCurrentOrder($hasOrderId, $isOrderCancelled, $expectedResult)
    {
        $comment = 'Some test comment';
        $order = $this->_getOrderMock($hasOrderId, ['registerCancellation', 'save']);
        $order->setData(
            'state',
            $isOrderCancelled ? \Magento\Sales\Model\Order::STATE_CANCELED : 'some another state'
        );
        if ($expectedResult) {
            $order->expects(
                $this->once()
            )->method(
                'registerCancellation'
            )->with(
                $this->equalTo($comment)
            )->will(
                $this->returnSelf()
            );
            $order->expects($this->once())->method('save');
        } else {
            $order->expects($this->never())->method('registerCancellation');
            $order->expects($this->never())->method('save');
        }

        $this->_session->expects($this->any())->method('getLastRealOrder')->will($this->returnValue($order));
        $this->assertEquals($expectedResult, $this->_checkout->cancelCurrentOrder($comment));
    }

    /**
     * @return array
     */
    public function cancelCurrentOrderDataProvider()
    {
        return [
            [true, false, true],
            [true, true, false],
            [false, true, false],
            [false, false, false]
        ];
    }
}
