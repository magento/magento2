<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

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
use Magento\Sales\Api\PaymentFailuresInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;
use Magento\Sales\Model\OrderFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ReturnUrlTest extends TestCase
{
   
    public const LAST_REAL_ORDER_ID = '000000001';

    public const SILENT_POST_HASH = 'abcdfg';

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
     * @var PaymentFailuresInterface|MockObject
     */
    private $paymentFailures;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);

        $this->context = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->view = $this->getMockBuilder(ViewInterface::class)
            ->getMock();

        $this->request = $this->getMockBuilder(Http::class)->disableOriginalConstructor()
            ->addMethods(['getParam'])
            ->getMock();

        $this->layout = $this->getMockBuilder(LayoutInterface::class)
            ->getMock();

        $this->block = $this->getMockBuilder(Success::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->orderFactory = $this->getMockBuilder(OrderFactory::class)->disableOriginalConstructor()
            ->onlyMethods(['create'])
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

        $this->checkoutSession = $this->getMockBuilder(Session::class)->disableOriginalConstructor()
            ->onlyMethods(['getLastRealOrder', 'restoreQuote'])
            ->addMethods(['setLastRealOrderId'])
            ->getMock();

        $this->paymentFailures = $this->getMockBuilder(PaymentFailuresInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->context->method('getView')
            ->willReturn($this->view);
        $this->context->method('getRequest')
            ->willReturn($this->request);

        $this->returnUrl = $this->objectManager->getObject(
            ReturnUrl::class,
            [
                'context' => $this->context,
                'checkoutSession' => $this->checkoutSession,
                'orderFactory' => $this->orderFactory,
                'checkoutHelper' => $this->checkoutHelper,
                'paymentFailures' => $this->paymentFailures
            ]
        );
    }

    /**
     * Checks a test case when action processes order with allowed state.
     *
     * @param string $state
     *
     * @return void
     * @dataProvider allowedOrderStateDataProvider
     */
    public function testExecuteAllowedOrderState($state): void
    {
        $this->withLayout();
        $this->withOrder(self::LAST_REAL_ORDER_ID, $state);

        $this->request->method('getParam')
            ->willReturnMap(
                [
                    ['INVNUM', self::LAST_REAL_ORDER_ID],
                    ['USER2', self::SILENT_POST_HASH]
                ]
            );

        $this->checkoutSession->expects($this->once())
            ->method('setLastRealOrderId')
            ->with(self::LAST_REAL_ORDER_ID);

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
    public static function allowedOrderStateDataProvider(): array
    {
        return [
            [Order::STATE_PROCESSING],
            [Order::STATE_COMPLETE],
            [Order::STATE_PAYMENT_REVIEW]
        ];
    }

    /**
     * Checks a test case when silent post hash validation fails.
     *
     * @param string $requestHash
     * @param string $orderHash
     *
     * @return void
     * @dataProvider invalidHashVariations
     */
    public function testFailedHashValidation(string $requestHash, string $orderHash): void
    {
        $this->withLayout();
        $this->withOrder(self::LAST_REAL_ORDER_ID, Order::STATE_PROCESSING, $orderHash);

        $this->request->method('getParam')
            ->willReturnMap(
                [
                    ['INVNUM', self::LAST_REAL_ORDER_ID],
                    ['USER2', $requestHash]
                ]
            );

        $this->checkoutSession->expects($this->never())
            ->method('setLastRealOrderId')
            ->with(self::LAST_REAL_ORDER_ID);

        $this->returnUrl->execute();
    }

    /**
     * Gets list of allowed order states.
     *
     * @return array
     */
    public static function invalidHashVariations(): array
    {
        return [
            ['requestHash' => '', 'orderHash' => self::SILENT_POST_HASH],
            ['requestHash' => self::SILENT_POST_HASH, 'orderHash' => ''],
            ['requestHash' => 'abcd', 'orderHash' => 'dcba']
        ];
    }

    /**
     * Checks a test case when action processes order with not allowed state.
     *
     * @param string $state
     * @param bool $restoreQuote
     * @param string $expectedGotoSection
     *
     * @return void
     * @dataProvider notAllowedOrderStateDataProvider
     */
    public function testExecuteNotAllowedOrderState($state, $restoreQuote, $expectedGotoSection): void
    {
        $errMessage = 'Transaction has been canceled.';
        $this->withLayout();
        $this->withOrder(self::LAST_REAL_ORDER_ID, $state);
        $this->withCheckoutSession(self::LAST_REAL_ORDER_ID, $restoreQuote);

        $this->request->method('getParam')
            ->willReturnMap([
                ['RESPMSG', $errMessage],
                ['INVNUM', self::LAST_REAL_ORDER_ID],
                ['USER2', self::SILENT_POST_HASH]
            ]);

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
    public static function notAllowedOrderStateDataProvider(): array
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
            [Order::STATE_HOLDED, true, 'paymentMethod']
        ];
    }

    /**
     * Checks a test case when action is triggered for unsupported payment method.
     *
     * @return void
     */
    public function testCheckRejectByPaymentMethod(): void
    {
        $this->withLayout();
        $this->withOrder(self::LAST_REAL_ORDER_ID, Order::STATE_NEW);

        $this->checkoutSession->expects($this->once())
            ->method('setLastRealOrderId')
            ->with(self::LAST_REAL_ORDER_ID);
        $this->request->method('getParam')
            ->willReturnMap([
                ['INVNUM', self::LAST_REAL_ORDER_ID],
                ['USER2', self::SILENT_POST_HASH]
            ]);

        $this->withBlockContent(false, 'Requested payment method does not match with order.');

        $this->payment->expects(self::once())
            ->method('getMethod')
            ->willReturn('something_else');

        $this->returnUrl->execute();
    }

    /**
     * @param string $errorMsg
     * @param string $errorMsgEscaped
     *
     * @return void
     * @dataProvider checkXSSEscapedDataProvider
     */
    public function testCheckXSSEscaped($errorMsg, $errorMsgEscaped): void
    {
        $this->withLayout();
        $this->withOrder(self::LAST_REAL_ORDER_ID, Order::STATE_NEW);
        $this->withCheckoutSession(self::LAST_REAL_ORDER_ID, true);

        $this->request->method('getParam')
            ->willReturnMap([
                ['RESPMSG', $errorMsg],
                ['INVNUM', self::LAST_REAL_ORDER_ID],
                ['USER2', self::SILENT_POST_HASH]
            ]);

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
    public static function checkXSSEscapedDataProvider(): array
    {
        return [
            ['simple', 'simple'],
            ['<script>alert(1)</script>', 'alert(1)'],
            ['<div style="background-image:url(javascript:alert(1))">', '']
        ];
    }

    /**
     * Checks a case when Payflow Advanced methods uses inherited behavior
     *
     * @return void
     */
    public function testCheckAdvancedAcceptingByPaymentMethod(): void
    {
        $this->withLayout();
        $this->withOrder(self::LAST_REAL_ORDER_ID, Order::STATE_NEW);
        $this->withCheckoutSession(self::LAST_REAL_ORDER_ID, true);

        $this->request->method('getParam')
            ->willReturnMap(
                [
                    ['RESPMSG', 'message'],
                    ['INVNUM', self::LAST_REAL_ORDER_ID],
                    ['USER2', self::SILENT_POST_HASH]
                ]
            );

        $this->withBlockContent('paymentMethod', 'Your payment has been declined. Please try again.');

        $this->payment->method('getMethod')
            ->willReturn(Config::METHOD_PAYFLOWADVANCED);

        $returnUrl = $this->objectManager->getObject(
            PayflowadvancedReturnUrl::class,
            [
                'context' => $this->context,
                'checkoutSession' => $this->checkoutSession,
                'orderFactory' => $this->orderFactory,
                'checkoutHelper' => $this->checkoutHelper,
                'paymentFailures' => $this->paymentFailures
            ]
        );

        $returnUrl->execute();
    }

    /**
     * Imitates order behavior.
     *
     * @param string $incrementId
     * @param string $state
     * @param string $hash
     *
     * @return void
     */
    private function withOrder($incrementId, $state, $hash = self::SILENT_POST_HASH): void
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
        $this->payment->method('getAdditionalInformation')
            ->willReturn($hash);
    }

    /**
     * Imitates layout behavior.
     *
     * @return void
     */
    private function withLayout(): void
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
     *
     * @return void
     */
    private function withCheckoutSession($orderId, $restoreQuote): void
    {
        $this->checkoutSession->method('setLastRealOrderId')
            ->with($orderId);

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
     *
     * @return void
     */
    private function withBlockContent($gotoSection, $errMsg): void
    {
        $this->block
            ->method('setData')
            ->willReturnCallback(function ($arg1, $arg2) use ($gotoSection, $errMsg) {
                if ($arg1 == 'goto_section' && $arg2 == $gotoSection) {
                    return $this->block;
                } elseif ($arg1 == 'error_msg' && $arg2 == (__($errMsg))) {
                    return $this->block;
                }
            });
    }
}
