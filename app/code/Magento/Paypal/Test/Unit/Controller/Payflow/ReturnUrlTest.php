<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Paypal\Test\Unit\Controller\Payflow;

use Magento\Checkout\Block\Onepage\Success;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Http;
use Magento\Framework\App\View;
use Magento\Framework\App\ViewInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\Paypal\Controller\Payflow\ReturnUrl;
use Magento\Paypal\Controller\Payflowadvanced\ReturnUrl as PayflowadvancedReturnUrl;
use Magento\Paypal\Helper\Checkout;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;
use Psr\Log\LoggerInterface;
use Magento\Paypal\Model\Config;
use Magento\Framework\App\Action\Context;
use Magento\Sales\Model\OrderFactory;
use Magento\Paypal\Model\PayflowlinkFactory;

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
     * @var Context|\PHPUnit_Framework_MockObject_MockObject
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
     * @var OrderFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderFactoryMock;

    /**
     * @var PayflowlinkFactory|\PHPUnit_Framework_MockObject_MockObject
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

    /**
     * @var Payment|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $paymentMock;

    const LAST_REAL_ORDER_ID = '000000001';

    protected function setUp()
    {
        $this->contextMock = $this->getMock(Context::class, [], [], '', false);
        $this->viewMock = $this->getMock(ViewInterface::class);
        $this->requestMock = $this->getMock(Http::class, ['getParam'], [], '', false);
        $this->layoutMock = $this->getMock(LayoutInterface::class);
        $this->blockMock = $this
            ->getMockBuilder(Success::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->orderFactoryMock = $this->getMock(OrderFactory::class, ['create'], [], '', false);
        $this->payflowlinkFactoryMock = $this->getMock(PayflowlinkFactory::class, [], [], '', false);
        $this->helperCheckoutMock = $this->getMock(Checkout::class, ['cancelCurrentOrder'], [], '', false);
        $this->loggerMock = $this->getMockForAbstractClass(LoggerInterface::class);
        $this->orderMock = $this
            ->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->paymentMock = $this
            ->getMockBuilder(Payment::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->orderMock->expects($this->any())->method('getPayment')->will($this->returnValue($this->paymentMock));

        $this->checkoutSessionMock = $this
            ->getMockBuilder(Session::class)
            ->setMethods(['getLastRealOrderId', 'getLastRealOrder', 'restoreQuote'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->contextMock->expects($this->any())->method('getView')->will($this->returnValue($this->viewMock));
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
    public function allowedOrderStateDataProvider()
    {
        return [
            [Order::STATE_PROCESSING],
            [Order::STATE_COMPLETE],
        ];
    }

    /**
     * @return array
     */
    public function notAllowedOrderStateDataProvider()
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
     * @dataProvider allowedOrderStateDataProvider
     */
    public function testExecuteAllowedOrderState($state)
    {
        $this->initLayoutMock();
        $this->initOrderMock(self::LAST_REAL_ORDER_ID, $state);

        $this->requestMock
            ->expects($this->never())
            ->method('getParam');

        $this->checkoutSessionMock
            ->expects($this->exactly(2))
            ->method('getLastRealOrderId')
            ->will($this->returnValue(self::LAST_REAL_ORDER_ID));

        $this->blockMock
            ->expects($this->once())
            ->method('setData')
            ->with('goto_success_page', true)
            ->will($this->returnSelf());

        $this->paymentMock
            ->expects($this->never())
            ->method('getMethod');

        $this->returnUrl->execute();
    }

    /**
     * @param $state
     * @param $restoreQuote
     * @param $expectedGotoSection
     * @dataProvider notAllowedOrderStateDataProvider
     */
    public function testExecuteNotAllowedOrderState($state, $restoreQuote, $expectedGotoSection)
    {
        $this->initLayoutMock();
        $this->initOrderMock(self::LAST_REAL_ORDER_ID, $state);
        $this->initCheckoutSessionMock(self::LAST_REAL_ORDER_ID, $restoreQuote);

        $this->requestMock
            ->expects($this->once())
            ->method('getParam')
            ->with('RESPMSG')
            ->will($this->returnValue('message'));

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

        $this->paymentMock
            ->expects($this->once())
            ->method('getMethod')
            ->will($this->returnValue(Config::METHOD_PAYFLOWLINK));

        $this->returnUrl->execute();
    }

    public function testCheckRejectByPaymentMethod()
    {
        $this->initLayoutMock();
        $this->initOrderMock(self::LAST_REAL_ORDER_ID, Order::STATE_NEW);

        $this->requestMock
            ->expects($this->never())
            ->method('getParam');

        $this->checkoutSessionMock
            ->expects($this->any())
            ->method('getLastRealOrderId')
            ->will($this->returnValue(self::LAST_REAL_ORDER_ID));

        $this->blockMock
            ->expects($this->at(0))
            ->method('setData')
            ->with('goto_section', false)
            ->will($this->returnSelf());

        $this->blockMock
            ->expects($this->at(1))
            ->method('setData')
            ->with('error_msg', __('Requested payment method does not match with order.'))
            ->will($this->returnSelf());

        $this->paymentMock
            ->expects($this->once())
            ->method('getMethod')
            ->will($this->returnValue('something_else'));

        $this->returnUrl->execute();
    }

    /**
     * @return array
     */
    public function checkXSSEscapedDataProvider()
    {
        return [
            ['simple', 'simple'],
            ['<script>alert(1)</script>', 'alert(1)'],
            ['<div style="background-image:url(javascript:alert(1))">', '']
        ];
    }

    /**
     * @param $errorMsg
     * @param $errorMsgEscaped
     * @dataProvider checkXSSEscapedDataProvider
     */
    public function testCheckXSSEscaped($errorMsg, $errorMsgEscaped)
    {
        $this->initLayoutMock();
        $this->initOrderMock(self::LAST_REAL_ORDER_ID, Order::STATE_NEW);
        $this->initCheckoutSessionMock(self::LAST_REAL_ORDER_ID, true);

        $this->requestMock
            ->expects($this->once())
            ->method('getParam')
            ->with('RESPMSG')
            ->will($this->returnValue($errorMsg));

        $this->helperCheckoutMock
            ->expects($this->once())
            ->method('cancelCurrentOrder')
            ->with($errorMsgEscaped)
            ->will($this->returnValue(self::LAST_REAL_ORDER_ID));

        $this->blockMock
            ->expects($this->at(0))
            ->method('setData')
            ->with('goto_section', 'paymentMethod')
            ->will($this->returnSelf());

        $this->blockMock
            ->expects($this->at(1))
            ->method('setData')
            ->with('error_msg', __('Your payment has been declined. Please try again.'))
            ->will($this->returnSelf());

        $this->paymentMock
            ->expects($this->once())
            ->method('getMethod')
            ->will($this->returnValue(Config::METHOD_PAYFLOWLINK));

        $this->returnUrl->execute();
    }

    public function testCheckAdvancedAcceptingByPaymentMethod()
    {
        $this->initLayoutMock();
        $this->initOrderMock(self::LAST_REAL_ORDER_ID, Order::STATE_NEW);
        $this->initCheckoutSessionMock(self::LAST_REAL_ORDER_ID, true);

        $this->requestMock
            ->expects($this->once())
            ->method('getParam')
            ->with('RESPMSG')
            ->will($this->returnValue('message'));

        $this->blockMock
            ->expects($this->at(0))
            ->method('setData')
            ->with('goto_section', 'paymentMethod')
            ->will($this->returnSelf());

        $this->blockMock
            ->expects($this->at(1))
            ->method('setData')
            ->with('error_msg', __('Your payment has been declined. Please try again.'))
            ->will($this->returnSelf());

        $this->paymentMock
            ->expects($this->once())
            ->method('getMethod')
            ->will($this->returnValue(Config::METHOD_PAYFLOWADVANCED));

        $payflowadvancedReturnUrl = new PayflowadvancedReturnUrl(
            $this->contextMock,
            $this->checkoutSessionMock,
            $this->orderFactoryMock,
            $this->payflowlinkFactoryMock,
            $this->helperCheckoutMock,
            $this->loggerMock
        );

        $payflowadvancedReturnUrl->execute();
    }

    private function initOrderMock($orderId, $state)
    {
        $this->orderFactoryMock
            ->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->orderMock));

        $this->orderMock
            ->expects($this->once())
            ->method('loadByIncrementId')
            ->with($orderId)
            ->will($this->returnSelf());

        $this->orderMock
            ->expects($this->once())
            ->method('getIncrementId')
            ->will($this->returnValue($orderId));

        $this->orderMock
            ->expects($this->once())
            ->method('getState')
            ->will($this->returnValue($state));
    }

    private function initLayoutMock()
    {
        $this->viewMock
            ->expects($this->once())
            ->method('getLayout')
            ->will($this->returnValue($this->layoutMock));

        $this->viewMock
            ->expects($this->once())
            ->method('loadLayout')
            ->willReturnSelf();

        $this->viewMock
            ->expects($this->once())
            ->method('renderLayout')
            ->willReturnSelf();

        $this->layoutMock
            ->expects($this->once())
            ->method('getBlock')
            ->will($this->returnValue($this->blockMock));
    }

    private function initCheckoutSessionMock($orderId, $restoreQuote)
    {
        $this->checkoutSessionMock
            ->expects($this->any())
            ->method('getLastRealOrderId')
            ->will($this->returnValue($orderId));

        $this->checkoutSessionMock
            ->expects($this->any())
            ->method('getLastRealOrder')
            ->will($this->returnValue($this->orderMock));

        $this->checkoutSessionMock
            ->expects($this->any())
            ->method('restoreQuote')
            ->will($this->returnValue($restoreQuote));
    }
}
