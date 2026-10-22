<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Paypal\Test\Unit\Controller\Express;

use Magento\Checkout\Api\AgreementsValidatorInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Customer\Model\Url as CustomerUrl;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ObjectManager as AppObjectManager;
use Magento\Framework\App\Response\Http as HttpResponse;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Session\Generic as PaypalSession;
use Magento\Framework\Url\Helper\Data as UrlHelper;
use Magento\Paypal\Controller\Express\PlaceOrder;
use Magento\Paypal\Model\Api\ProcessableException;
use Magento\Paypal\Model\Config;
use Magento\Paypal\Model\Express\Checkout\Factory as CheckoutFactory;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Sales\Api\PaymentFailuresInterface;
use Magento\Sales\Model\OrderFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PlaceOrderPaymentFailureTest extends TestCase
{
    /**
     * @var CheckoutSession|Stub
     */
    private $checkoutSession;

    /**
     * @var PaymentFailuresInterface|MockObject
     */
    private $paymentFailures;

    /**
     * @var ManagerInterface|MockObject
     */
    private $messageManager;

    /**
     * @var RedirectInterface|MockObject
     */
    private $redirect;

    /**
     * @var HttpResponse|Stub
     */
    private $response;

    /**
     * @var PlaceOrder
     */
    private $controller;

    protected function setUp(): void
    {
        $this->checkoutSession = $this->createStub(CheckoutSession::class);
        $this->paymentFailures = $this->createMock(PaymentFailuresInterface::class);
        $this->messageManager = $this->createMock(ManagerInterface::class);
        $this->redirect = $this->createMock(RedirectInterface::class);
        $this->response = $this->createStub(HttpResponse::class);

        $objectManager = $this->createStub(ObjectManagerInterface::class);
        $objectManager->method('create')
            ->willReturn($this->createStub(Config::class));
        $objectManager->method('get')
            ->willReturn($this->createStub(CartRepositoryInterface::class));
        AppObjectManager::setInstance($objectManager);

        $context = $this->createStub(Context::class);
        $context->method('getObjectManager')->willReturn($objectManager);
        $context->method('getMessageManager')->willReturn($this->messageManager);
        $context->method('getRedirect')->willReturn($this->redirect);
        $context->method('getResponse')->willReturn($this->response);

        $this->controller = new PlaceOrder(
            $context,
            $this->createStub(CustomerSession::class),
            $this->checkoutSession,
            $this->createStub(OrderFactory::class),
            $this->createStub(CheckoutFactory::class),
            $this->createStub(PaypalSession::class),
            $this->createStub(UrlHelper::class),
            $this->createStub(CustomerUrl::class),
            $this->createStub(AgreementsValidatorInterface::class),
            $this->paymentFailures
        );
    }

    public function testApiErrorWithoutSessionQuoteSkipsFailureNotification(): void
    {
        $this->checkoutSession->method('getQuoteId')
            ->willReturn(null);
        $this->paymentFailures->expects($this->never())
            ->method('handle');

        $this->processApiErrorAndExpectReviewRedirect();
    }

    public function testApiErrorWithMissingQuoteStillRedirectsToReview(): void
    {
        $this->checkoutSession->method('getQuoteId')
            ->willReturn(42);
        $this->paymentFailures->expects($this->once())
            ->method('handle')
            ->with(42, 'PayPal gateway has rejected request.')
            ->willThrowException(NoSuchEntityException::singleField('cartId', 42));

        $this->processApiErrorAndExpectReviewRedirect();
    }

    public function testApiErrorWithSessionQuoteSendsFailureNotification(): void
    {
        $this->checkoutSession->method('getQuoteId')
            ->willReturn(42);
        $this->paymentFailures->expects($this->once())
            ->method('handle')
            ->with(42, 'PayPal gateway has rejected request.');

        $this->processApiErrorAndExpectReviewRedirect();
    }

    private function processApiErrorAndExpectReviewRedirect(): void
    {
        $exception = new ProcessableException(
            __('PayPal gateway has rejected request.'),
            null,
            ProcessableException::API_TRANSACTION_HAS_BEEN_COMPLETED
        );
        $this->messageManager->expects($this->once())
            ->method('addErrorMessage')
            ->with($exception->getUserMessage());
        $this->redirect->expects($this->once())
            ->method('redirect')
            ->with($this->response, '*/*/review', []);

        $method = new \ReflectionMethod($this->controller, '_processPaypalApiError');
        $method->invoke($this->controller, $exception);
    }
}
