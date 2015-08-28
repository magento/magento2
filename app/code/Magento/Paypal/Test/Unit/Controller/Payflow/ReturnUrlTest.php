<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Paypal\Test\Unit\Controller\Payflow;

use Magento\Checkout\Block\Onepage\Success;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Http;
use Magento\Framework\App\View;
use Magento\Framework\View\LayoutInterface;
use Magento\Paypal\Controller\Payflow\ReturnUrl;
use Magento\Paypal\Helper\Checkout;
use Magento\Sales\Model\Order;
use Psr\Log\LoggerInterface;

/**
 * Class ReturnUrlTest
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ReturnUrlTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ReturnUrl
     */
    protected $returnUrl;

    /**
     * @var \Magento\Framework\App\Action\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $contextMock;

    /**
     * @var View|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $viewMock;

    /**
     * @var Http|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestMock;

    /**
     * @var Session|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $checkoutSessionMock;

    /**
     * @var \Magento\Sales\Model\OrderFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderFactoryMock;

    /**
     * @var \Magento\Paypal\Model\PayflowlinkFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $payflowlinkFactoryMock;

    /**
     * @var Checkout|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $helperCheckoutMock;

    /**
     * @var LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $loggerMock;

    /**
     * @var Success|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $blockMock;

    /**
     * @var LayoutInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $layoutMock;

    /**
     * @var Order|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderMock;

    protected function setUp()
    {
        $this->contextMock = $this->getMock('Magento\Framework\App\Action\Context', [], [], '', false);
        $this->viewMock = $this->getMock('Magento\Framework\App\ViewInterface');
        $this->requestMock = $this->getMock('Magento\Framework\App\Request\Http', [], [], '', false);
        $this->layoutMock = $this->getMock('Magento\Framework\View\LayoutInterface');
        $this->blockMock = $this
            ->getMockBuilder('\Magento\Checkout\Block\Onepage\Success')
            ->disableOriginalConstructor()
            ->getMock();
        $this->orderFactoryMock = $this->getMock('\Magento\Sales\Model\OrderFactory', ['create'], [], '', false);
        $this->payflowlinkFactoryMock = $this->getMock('\Magento\Paypal\Model\PayflowlinkFactory', [], [], '', false);
        $this->helperCheckoutMock = $this->getMock('\Magento\Paypal\Helper\Checkout', [], [], '', false);
        $this->loggerMock = $this->getMockForAbstractClass('\Psr\Log\LoggerInterface');
        $this->orderMock = $this
            ->getMockBuilder('\Magento\Sales\Model\Order')
            ->disableOriginalConstructor()
            ->getMock();
        $this->checkoutSessionMock = $this
            ->getMockBuilder('\Magento\Checkout\Model\Session')
            ->setMethods(['getLastRealOrderId', 'getLastRealOrder', 'restoreQuote'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->contextMock->expects($this->once())->method('getView')->will($this->returnValue($this->viewMock));
        $this->contextMock->expects($this->any())->method('getRequest')->will($this->returnValue($this->requestMock));

        $this->returnUrl = new ReturnUrl(
            $this->contextMock,
            $this->checkoutSessionMock,
            $this->orderFactoryMock,
            $this->payflowlinkFactoryMock,
            $this->helperCheckoutMock,
            $this->loggerMock
        );
    }

    /**
     * @return array
     */
    public function testAllowedOrderStateDataProvider()
    {
        return [
            [Order::STATE_PROCESSING],
            [Order::STATE_COMPLETE],
        ];
    }

    /**
     * @return array
     */
    public function testNotAllowedOrderStateDataProvider()
    {
        return [
            [Order::STATE_NEW, false, ''],
            [Order::STATE_NEW, true, 'paymentMethod'],
            [Order::STATE_PENDING_PAYMENT, false, ''],
            [Order::STATE_PENDING_PAYMENT, true, 'paymentMethod'],
            [Order::STATE_CLOSED, false, ''],
            [Order::STATE_CLOSED, true, 'paymentMethod'],
            [Order::STATE_CANCELED, false, ''],
            [Order::STATE_CANCELED, true, 'paymentMethod'],
            [Order::STATE_HOLDED, false, ''],
            [Order::STATE_HOLDED, true, 'paymentMethod'],
            [Order::STATE_PAYMENT_REVIEW, false, ''],
            [Order::STATE_PAYMENT_REVIEW, true, 'paymentMethod'],
        ];
    }

    /**
     * @param $state
     * @dataProvider testAllowedOrderStateDataProvider
     */
    public function testExecuteAllowedOrderState($state)
    {
        $lastRealOrderId = '000000001';

        $this->viewMock
            ->expects($this->once())
            ->method('getLayout')
            ->will($this->returnValue($this->layoutMock));

        $this->layoutMock
            ->expects($this->once())
            ->method('getBlock')
            ->will($this->returnValue($this->blockMock));

        $this->checkoutSessionMock
            ->expects($this->exactly(2))
            ->method('getLastRealOrderId')
            ->will($this->returnValue($lastRealOrderId));

        $this->orderFactoryMock
            ->expects($this->once())
            ->method('create')
            ->will($this->returnValue($this->orderMock));

        $this->orderMock
            ->expects($this->once())
            ->method('loadByIncrementId')
            ->with($lastRealOrderId)
            ->will($this->returnSelf());

        $this->orderMock
            ->expects($this->once())
            ->method('getIncrementId')
            ->will($this->returnValue($lastRealOrderId));

        $this->orderMock
            ->expects($this->once())
            ->method('getState')
            ->will($this->returnValue($state));

        $this->blockMock
            ->expects($this->once())
            ->method('setData')
            ->with('goto_success_page', true)
            ->will($this->returnSelf());

        $this->returnUrl->execute();
    }

    /**
     * @param $state
     * @param $restoreQuote
     * @param $expectedGotoSection
     * @dataProvider testNotAllowedOrderStateDataProvider
     */
    public function testExecuteNotAllowedOrderState($state, $restoreQuote, $expectedGotoSection)
    {
        $lastRealOrderId = '000000001';
        $this->viewMock
            ->expects($this->once())
            ->method('getLayout')
            ->will($this->returnValue($this->layoutMock));

        $this->layoutMock
            ->expects($this->once())
            ->method('getBlock')
            ->will($this->returnValue($this->blockMock));

        $this->checkoutSessionMock
            ->expects($this->any())
            ->method('getLastRealOrderId')
            ->will($this->returnValue($lastRealOrderId));

        $this->checkoutSessionMock
            ->expects($this->any())
            ->method('getLastRealOrder')
            ->will($this->returnValue($this->orderMock));

        $this->checkoutSessionMock
            ->expects($this->any())
            ->method('restoreQuote')
            ->will($this->returnValue($restoreQuote));

        $this->orderFactoryMock
            ->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->orderMock));

        $this->orderMock
            ->expects($this->once())
            ->method('loadByIncrementId')
            ->with($lastRealOrderId)
            ->will($this->returnSelf());

        $this->orderMock
            ->expects($this->once())
            ->method('getIncrementId')
            ->will($this->returnValue($lastRealOrderId));

        $this->orderMock
            ->expects($this->once())
            ->method('getState')
            ->will($this->returnValue($state));

        $this->blockMock
            ->expects($this->at(0))
            ->method('setData')
            ->with('goto_section', $expectedGotoSection)
            ->will($this->returnSelf());

        $this->blockMock
            ->expects($this->at(1))
            ->method('setData')
            ->with('error_msg', __('Your payment has been declined. Please try again.'))
            ->will($this->returnSelf());

        $this->returnUrl->execute();
    }
}
