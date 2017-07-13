<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Test\Unit\Controller\Payflow;

use Magento\Checkout\Block\Onepage\Success;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Http;
use Magento\Framework\App\ViewInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\LayoutInterface;
use Magento\Paypal\Controller\Payflow\ReturnUrl;
use Magento\Paypal\Controller\Payflowadvanced\ReturnUrl as PayflowadvancedReturnUrl;
use Magento\Paypal\Helper\Checkout;
use Magento\Paypal\Model\Config;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;
use Magento\Sales\Model\OrderFactory;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Class ReturnUrlTest
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ReturnUrlTest extends \PHPUnit\Framework\TestCase
{
    const LAST_REAL_ORDER_ID = '000000001';

    /**
     * @var ReturnUrl
     */
    private $returnUrl;

    /**
     * @var Context|MockObject
     */
    private $context;

    /**
     * @var ViewInterface|MockObject
     */
    private $view;

    /**
     * @var Http|MockObject
     */
    private $request;

    /**
     * @var Session|MockObject
     */
    private $checkoutSession;

    /**
     * @var OrderFactory|MockObject
     */
    private $orderFactory;

    /**
     * @var Checkout|MockObject
     */
    private $checkoutHelper;

    /**
     * @var Success|MockObject
     */
    private $block;

    /**
     * @var LayoutInterface|MockObject
     */
    private $layout;

    /**
     * @var Order|MockObject
     */
    private $order;

    /**
     * @var Payment|MockObject
     */
    private $payment;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);

        $this->context = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->view = $this->getMockBuilder(ViewInterface::class)
            ->getMock();

        $this->request = $this->getMockBuilder(Http::class)
            ->disableOriginalConstructor()
            ->setMethods(['getParam'])
            ->getMock();

        $this->layout = $this->getMockBuilder(LayoutInterface::class)
            ->getMock();

        $this->block = $this->getMockBuilder(Success::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->orderFactory = $this->getMockBuilder(OrderFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->checkoutHelper = $this->getMockBuilder(Checkout::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->order = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->payment = $this->getMockBuilder(Payment::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->checkoutSession = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->setMethods(['getLastRealOrderId', 'getLastRealOrder', 'restoreQuote'])
            ->getMock();

        $this->context->method('getView')
            ->willReturn($this->view);
        $this->context->method('getRequest')
            ->willReturn($this->request);

        $this->returnUrl = $this->objectManager->getObject(ReturnUrl::class, [
            'context' => $this->context,
            'checkoutSession' => $this->checkoutSession,
            'orderFactory' => $this->orderFactory,
            'checkoutHelper' => $this->checkoutHelper,
        ]);
    }

    /**
     * Checks a test case when action processes order with allowed state.
     *
     * @param string $state
     * @dataProvider allowedOrderStateDataProvider
     */
    public function testExecuteAllowedOrderState($state)
    {
        $this->withLayout();
        $this->withOrder(self::LAST_REAL_ORDER_ID, $state);

        $this->checkoutSession->method('getLastRealOrderId')
            ->willReturn(self::LAST_REAL_ORDER_ID);

        $this->block->method('setData')
            ->with('goto_success_page', true)
            ->willReturnSelf();

        $result = $this->returnUrl->execute();
        $this->assertNull($result);
    }

    /**
     * Gets list of allowed order states.
     *
     * @return array
     */
    public function allowedOrderStateDataProvider()
    {
        return [
            [Order::STATE_PROCESSING],
            [Order::STATE_COMPLETE],
            [Order::STATE_PAYMENT_REVIEW],
        ];
    }

    /**
     * Checks a test case when action processes order with not allowed state.
     *
     * @param string $state
     * @param bool $restoreQuote
     * @param string $expectedGotoSection
     * @dataProvider notAllowedOrderStateDataProvider
     */
    public function testExecuteNotAllowedOrderState($state, $restoreQuote, $expectedGotoSection)
    {
        $errMessage = 'Transaction has been canceled.';
        $this->withLayout();
        $this->withOrder(self::LAST_REAL_ORDER_ID, $state);
        $this->withCheckoutSession(self::LAST_REAL_ORDER_ID, $restoreQuote);

        $this->request->method('getParam')
            ->with('RESPMSG')
            ->willReturn($errMessage);

        $this->payment->method('getMethod')
            ->willReturn(Config::METHOD_PAYFLOWLINK);

        $this->checkoutHelper->method('cancelCurrentOrder')
            ->with(self::equalTo($errMessage));

        $this->withBlockContent($expectedGotoSection, 'Your payment has been declined. Please try again.');

        $this->returnUrl->execute();
    }

    /**
     * Gets list of not allowed order states and different redirect behaviours.
     *
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
        ];
    }

    /**
     * Checks a test case when action is triggered for unsupported payment method.
     */
    public function testCheckRejectByPaymentMethod()
    {
        $this->withLayout();
        $this->withOrder(self::LAST_REAL_ORDER_ID, Order::STATE_NEW);

        $this->checkoutSession->method('getLastRealOrderId')
            ->willReturn(self::LAST_REAL_ORDER_ID);

        $this->withBlockContent(false, 'Requested payment method does not match with order.');

        $this->payment->expects(self::once())
            ->method('getMethod')
            ->willReturn('something_else');

        $this->returnUrl->execute();
    }

    /**
     * @param string $errorMsg
     * @param string $errorMsgEscaped
     * @dataProvider checkXSSEscapedDataProvider
     */
    public function testCheckXSSEscaped($errorMsg, $errorMsgEscaped)
    {
        $this->withLayout();
        $this->withOrder(self::LAST_REAL_ORDER_ID, Order::STATE_NEW);
        $this->withCheckoutSession(self::LAST_REAL_ORDER_ID, true);

        $this->request->method('getParam')
            ->with('RESPMSG')
            ->willReturn($errorMsg);

        $this->checkoutHelper->method('cancelCurrentOrder')
            ->with(self::equalTo($errorMsgEscaped));

        $this->withBlockContent('paymentMethod', 'Your payment has been declined. Please try again.');

        $this->payment->method('getMethod')
            ->willReturn(Config::METHOD_PAYFLOWLINK);

        $this->returnUrl->execute();
    }

    /**
     * Gets list of response messages with JS code and HTML markup.
     *
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
     * Checks a case when Payflow Advanced methods uses inherited behavior.
     */
    public function testCheckAdvancedAcceptingByPaymentMethod()
    {
        $this->withLayout();
        $this->withOrder(self::LAST_REAL_ORDER_ID, Order::STATE_NEW);
        $this->withCheckoutSession(self::LAST_REAL_ORDER_ID, true);

        $this->request->method('getParam')
            ->with('RESPMSG')
            ->willReturn('message');

        $this->withBlockContent('paymentMethod', 'Your payment has been declined. Please try again.');

        $this->payment->method('getMethod')
            ->willReturn(Config::METHOD_PAYFLOWADVANCED);

        $returnUrl = $this->objectManager->getObject(PayflowadvancedReturnUrl::class, [
            'context' => $this->context,
            'checkoutSession' => $this->checkoutSession,
            'orderFactory' => $this->orderFactory,
            'checkoutHelper' => $this->checkoutHelper,
        ]);

        $returnUrl->execute();
    }

    /**
     * Imitates order behavior.
     *
     * @param string $incrementId
     * @param string $state
     * @return void
     */
    private function withOrder($incrementId, $state)
    {
        $this->orderFactory->method('create')
            ->willReturn($this->order);

        $this->order->method('loadByIncrementId')
            ->with($incrementId)
            ->willReturnSelf();

        $this->order->method('getIncrementId')
            ->willReturn($incrementId);

        $this->order->method('getState')
            ->willReturn($state);

        $this->order->method('getPayment')
            ->willReturn($this->payment);
    }

    /**
     * Imitates layout behavior.
     *
     * @return void
     */
    private function withLayout()
    {
        $this->view->method('getLayout')
            ->willReturn($this->layout);

        $this->layout->method('getBlock')
            ->willReturn($this->block);
    }

    /**
     * Imitates checkout session behavior.
     *
     * @param int $orderId
     * @param bool $restoreQuote
     */
    private function withCheckoutSession($orderId, $restoreQuote)
    {
        $this->checkoutSession->method('getLastRealOrderId')
            ->willReturn($orderId);

        $this->checkoutSession->method('getLastRealOrder')
            ->willReturn($this->order);

        $this->checkoutSession->method('restoreQuote')
            ->willReturn($restoreQuote);
    }

    /**
     * Imitates processes to set block error content.
     *
     * @param bool $gotoSection
     * @param string $errMsg
     * @return void
     */
    private function withBlockContent($gotoSection, $errMsg)
    {
        $this->block->expects(self::at(0))
            ->method('setData')
            ->with('goto_section', self::equalTo($gotoSection))
            ->willReturnSelf();

        $this->block->expects(self::at(1))
            ->method('setData')
            ->with('error_msg', self::equalTo(__($errMsg)))
            ->willReturnSelf();
    }
}
