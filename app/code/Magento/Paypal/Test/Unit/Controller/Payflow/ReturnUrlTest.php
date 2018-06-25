<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Test\Unit\Controller\Payflow;

use Magento\Sales\Api\PaymentFailuresInterface;
use Magento\Checkout\Block\Onepage\Success;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Http;
use Magento\Framework\App\View;
use Magento\Framework\App\ViewInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\Paypal\Controller\Payflow\ReturnUrl;
use Magento\Paypal\Controller\Payflowadvanced\ReturnUrl as PayflowadvancedReturnUrl;
use Magento\Paypal\Helper\Checkout;
use Magento\Quote\Api\Data\CartInterface;
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
 * @SuppressWarnings(PHPMD.TooManyFields)
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
    protected $context;

    /**
     * @var View|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $view;

    /**
     * @var Http|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $request;

    /**
     * @var Session|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $checkoutSession;

    /**
     * @var OrderFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderFactory;

    /**
     * @var PayflowlinkFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $payflowlinkFactory;

    /**
     * @var Checkout|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $helperCheckout;

    /**
     * @var LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $logger;

    /**
     * @var Success|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $block;

    /**
     * @var LayoutInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $layout;

    /**
     * @var Order|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $order;

    /**
     * @var Payment|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $payment;

    /**
     * @var PaymentFailuresInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $paymentFailures;

    /**
     * @var CartInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quote;

    const LAST_REAL_ORDER_ID = '000000001';

    protected function setUp()
    {
        $this->context = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->view = $this->getMockBuilder(ViewInterface::class)
            ->getMockForAbstractClass();

        $this->request = $this->getMockBuilder(Http::class)
            ->disableOriginalConstructor()
            ->setMethods(['getParam'])
            ->getMock();

        $this->layout = $this->getMockBuilder(LayoutInterface::class)
            ->getMockForAbstractClass();

        $this->block = $this->getMockBuilder(Success::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->orderFactory = $this->getMockBuilder(OrderFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->payflowlinkFactory = $this->getMockBuilder(PayflowlinkFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->helperCheckout = $this->getMockBuilder(Checkout::class)
            ->disableOriginalConstructor()
            ->setMethods(['cancelCurrentOrder'])
            ->getMock();

        $this->logger = $this->getMockForAbstractClass(LoggerInterface::class);

        $this->order = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->payment = $this->getMockBuilder(Payment::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->order->expects($this->any())->method('getPayment')->will($this->returnValue($this->payment));

        $this->checkoutSession = $this->getMockBuilder(Session::class)
            ->setMethods(['getLastRealOrderId', 'getLastRealOrder', 'restoreQuote', 'getQuote'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->quote = $this->getMockBuilder(CartInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->checkoutSession
            ->method('getQuote')
            ->willReturn($this->quote);

        $this->context->expects($this->any())->method('getView')->will($this->returnValue($this->view));
        $this->context->expects($this->any())->method('getRequest')->will($this->returnValue($this->request));

        $this->paymentFailures = $this->getMockBuilder(PaymentFailuresInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->returnUrl = new ReturnUrl(
            $this->context,
            $this->checkoutSession,
            $this->orderFactory,
            $this->payflowlinkFactory,
            $this->helperCheckout,
            $this->logger,
            $this->paymentFailures
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

        $this->request
            ->expects($this->never())
            ->method('getParam');

        $this->checkoutSession
            ->expects($this->exactly(2))
            ->method('getLastRealOrderId')
            ->will($this->returnValue(self::LAST_REAL_ORDER_ID));

        $this->block
            ->expects($this->once())
            ->method('setData')
            ->with('goto_success_page', true)
            ->will($this->returnSelf());

        $this->payment
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
        $this->initcheckoutSession(self::LAST_REAL_ORDER_ID, $restoreQuote);

        $this->request
            ->expects($this->once())
            ->method('getParam')
            ->with('RESPMSG')
            ->will($this->returnValue('message'));

        $this->block
            ->expects($this->at(0))
            ->method('setData')
            ->with('goto_section', $expectedGotoSection)
            ->will($this->returnSelf());

        $this->block
            ->expects($this->at(1))
            ->method('setData')
            ->with('error_msg', __('Your payment has been declined. Please try again.'))
            ->will($this->returnSelf());

        $this->payment
            ->expects($this->once())
            ->method('getMethod')
            ->will($this->returnValue(Config::METHOD_PAYFLOWLINK));

        $this->returnUrl->execute();
    }

    public function testCheckRejectByPaymentMethod()
    {
        $this->initLayoutMock();
        $this->initOrderMock(self::LAST_REAL_ORDER_ID, Order::STATE_NEW);

        $this->request
            ->expects($this->never())
            ->method('getParam');

        $this->checkoutSession
            ->expects($this->any())
            ->method('getLastRealOrderId')
            ->will($this->returnValue(self::LAST_REAL_ORDER_ID));

        $this->block
            ->expects($this->at(0))
            ->method('setData')
            ->with('goto_section', false)
            ->will($this->returnSelf());

        $this->block
            ->expects($this->at(1))
            ->method('setData')
            ->with('error_msg', __('Requested payment method does not match with order.'))
            ->will($this->returnSelf());

        $this->payment
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
        $this->initcheckoutSession(self::LAST_REAL_ORDER_ID, true);

        $this->request
            ->expects($this->once())
            ->method('getParam')
            ->with('RESPMSG')
            ->will($this->returnValue($errorMsg));

        $this->helperCheckout
            ->expects($this->once())
            ->method('cancelCurrentOrder')
            ->with($errorMsgEscaped)
            ->will($this->returnValue(self::LAST_REAL_ORDER_ID));

        $this->block
            ->expects($this->at(0))
            ->method('setData')
            ->with('goto_section', 'paymentMethod')
            ->will($this->returnSelf());

        $this->block
            ->expects($this->at(1))
            ->method('setData')
            ->with('error_msg', __('Your payment has been declined. Please try again.'))
            ->will($this->returnSelf());

        $this->payment
            ->expects($this->once())
            ->method('getMethod')
            ->will($this->returnValue(Config::METHOD_PAYFLOWLINK));

        $this->returnUrl->execute();
    }

    public function testCheckAdvancedAcceptingByPaymentMethod()
    {
        $this->initLayoutMock();
        $this->initOrderMock(self::LAST_REAL_ORDER_ID, Order::STATE_NEW);
        $this->initcheckoutSession(self::LAST_REAL_ORDER_ID, true);

        $this->request
            ->expects($this->once())
            ->method('getParam')
            ->with('RESPMSG')
            ->will($this->returnValue('message'));

        $this->block
            ->expects($this->at(0))
            ->method('setData')
            ->with('goto_section', 'paymentMethod')
            ->will($this->returnSelf());

        $this->block
            ->expects($this->at(1))
            ->method('setData')
            ->with('error_msg', __('Your payment has been declined. Please try again.'))
            ->will($this->returnSelf());

        $this->payment
            ->expects($this->once())
            ->method('getMethod')
            ->will($this->returnValue(Config::METHOD_PAYFLOWADVANCED));

        $payflowadvancedReturnUrl = new PayflowadvancedReturnUrl(
            $this->context,
            $this->checkoutSession,
            $this->orderFactory,
            $this->payflowlinkFactory,
            $this->helperCheckout,
            $this->logger,
            $this->paymentFailures
        );

        $payflowadvancedReturnUrl->execute();
    }

    /**
     * @param $orderId
     * @param $state
     */
    private function initOrderMock($orderId, $state)
    {
        $this->orderFactory
            ->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->order));

        $this->order
            ->expects($this->once())
            ->method('loadByIncrementId')
            ->with($orderId)
            ->will($this->returnSelf());

        $this->order
            ->expects($this->once())
            ->method('getIncrementId')
            ->will($this->returnValue($orderId));

        $this->order
            ->expects($this->once())
            ->method('getState')
            ->will($this->returnValue($state));
    }

    private function initLayoutMock()
    {
        $this->view
            ->expects($this->once())
            ->method('getLayout')
            ->will($this->returnValue($this->layout));

        $this->view
            ->expects($this->once())
            ->method('loadLayout')
            ->willReturnSelf();

        $this->view
            ->expects($this->once())
            ->method('renderLayout')
            ->willReturnSelf();

        $this->layout
            ->expects($this->once())
            ->method('getBlock')
            ->will($this->returnValue($this->block));
    }

    /**
     * @param $orderId
     * @param $restoreQuote
     */
    private function initcheckoutSession($orderId, $restoreQuote)
    {
        $this->checkoutSession
            ->expects($this->any())
            ->method('getLastRealOrderId')
            ->will($this->returnValue($orderId));

        $this->checkoutSession
            ->expects($this->any())
            ->method('getLastRealOrder')
            ->will($this->returnValue($this->order));

        $this->checkoutSession
            ->expects($this->any())
            ->method('restoreQuote')
            ->will($this->returnValue($restoreQuote));
    }
}
