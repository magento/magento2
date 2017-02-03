<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Braintree\Test\Unit\Controller\PayPal;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Class ReviewTest
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ReviewTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Braintree\Model\CheckoutFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $checkoutFactoryMock;

    /**
     * @var \Magento\Checkout\Model\Session|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $checkoutSessionMock;

    /**
     * @var \Magento\Framework\App\ActionFlag|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $actionFlagMock;

    /**
     * @var \Magento\Braintree\Model\Config\PayPal|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $braintreePayPalConfigMock;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestMock;

    /**
     * @var \Magento\Framework\Controller\ResultFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultFactoryMock;

    /**
     * @var \Magento\Braintree\Model\Checkout|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $checkoutMock;

    /**
     * @var \Magento\Framework\Message\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $messageManagerMock;

    /**
     * @var \Magento\Braintree\Model\PaymentMethod\PayPal|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $paymentMethodInstanceMock;

    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @var \Magento\Braintree\Controller\PayPal\Review
     */
    protected $controller;

    /**
     * test setup
     */
    public function setUp()
    {
        $this->checkoutSessionMock = $this->getMockBuilder('\Magento\Checkout\Model\Session')
            ->disableOriginalConstructor()
            ->getMock();

        $this->braintreePayPalConfigMock = $this->getMockBuilder('\Magento\Braintree\Model\Config\PayPal')
            ->disableOriginalConstructor()
            ->getMock();

        $this->checkoutFactoryMock = $this->getMockBuilder('\Magento\Braintree\Model\CheckoutFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->messageManagerMock = $this->getMock('\Magento\Framework\Message\ManagerInterface');

        $jsonHelperMock = $this->getMockBuilder('\Magento\Framework\Json\Helper\Data')
            ->disableOriginalConstructor()
            ->getMock();
        $jsonHelperMock->expects($this->any())
            ->method('jsonDecode')
            ->willReturnCallback(function ($arg) {
                return json_decode($arg, true);
            });

        $this->checkoutMock = $this->getMockBuilder('\Magento\Braintree\Model\Checkout')
            ->disableOriginalConstructor()
            ->getMock();

        $this->requestMock = $this->getMock('\Magento\Framework\App\RequestInterface');

        $this->actionFlagMock = $this->getMockBuilder('\Magento\Framework\App\ActionFlag')
            ->disableOriginalConstructor()
            ->setMethods(['set'])
            ->getMock();

        $this->resultFactoryMock = $this->getMockBuilder('\Magento\Framework\Controller\ResultFactory')
            ->disableOriginalConstructor()
            ->getMock();

        $this->paymentMethodInstanceMock = $this->getMockBuilder('\Magento\Braintree\Model\PaymentMethod\PayPal')
            ->disableOriginalConstructor()
            ->getMock();

        $contextMock = $this->getMockBuilder('\Magento\Framework\App\Action\Context')
            ->disableOriginalConstructor()
            ->getMock();
        $contextMock->expects($this->any())
            ->method('getResultFactory')
            ->willReturn($this->resultFactoryMock);
        $contextMock->expects($this->any())
            ->method('getActionFlag')
            ->willReturn($this->actionFlagMock);
        $contextMock->expects($this->any())
            ->method('getRequest')
            ->willReturn($this->requestMock);
        $contextMock->expects($this->any())
            ->method('getMessageManager')
            ->willReturn($this->messageManagerMock);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->controller = $this->objectManagerHelper->getObject(
            '\Magento\Braintree\Controller\PayPal\Review',
            [
                'context' => $contextMock,
                'checkoutSession' => $this->checkoutSessionMock,
                'braintreePayPalConfig' => $this->braintreePayPalConfigMock,
                'checkoutFactory' => $this->checkoutFactoryMock,
                'jsonHelper' => $jsonHelperMock,
            ]
        );
    }

    protected function setupCart($withPaymentMethodInstance = true)
    {
        $quoteMock = $this->getMockBuilder('\Magento\Quote\Model\Quote')
            ->disableOriginalConstructor()
            ->setMethods(['hasItems', 'getHasError', 'getPayment'])
            ->getMock();
        $quoteMock->expects($this->any())
            ->method('hasItems')
            ->willReturn(true);
        $quoteMock->expects($this->any())
            ->method('getHasError')
            ->willReturn(false);

        $this->checkoutSessionMock->expects($this->any())
            ->method('getQuote')
            ->willReturn($quoteMock);

        $this->checkoutFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->checkoutMock);

        if ($withPaymentMethodInstance) {
            $paymentMock = $this->getMockBuilder('\Magento\Quote\Model\Quote\Payment')
                ->disableOriginalConstructor()
                ->setMethods(['getMethodInstance'])
                ->getMock();

            $paymentMock->expects($this->any())
                ->method('getMethodInstance')
                ->willReturn($this->paymentMethodInstanceMock);

            $quoteMock->expects($this->any())
                ->method('getPayment')
                ->willReturn($paymentMock);
        }

        return $quoteMock;
    }

    protected function setupReviewPage($quote)
    {
        $shippingMethodBlockMock = $this->getMockBuilder('\Magento\Paypal\Block\Express\Review')
            ->disableOriginalConstructor()
            ->getMock();

        $reviewBlockMock = $this->getMockBuilder('\Magento\Braintree\Block\Checkout\Review')
            ->disableOriginalConstructor()
            ->getMock();
        $reviewBlockMock->expects($this->once())
            ->method('setQuote')
            ->with($quote);
        $shippingMethodBlockMock->expects($this->once())
            ->method('setQuote')
            ->with($quote);
        $reviewBlockMock->expects($this->once())
            ->method('getChildBlock')
            ->with('shipping_method')
            ->willReturn($shippingMethodBlockMock);

        $layoutMock = $this->getMockBuilder('\Magento\Framework\View\Layout')
            ->disableOriginalConstructor()
            ->getMock();
        $layoutMock->expects($this->once())
            ->method('getBlock')
            ->with('braintree.paypal.review')
            ->willReturn($reviewBlockMock);
        $resultPageMock = $this->getMockBuilder('\Magento\Framework\View\Result\Page')
            ->disableOriginalConstructor()
            ->getMock();
        $resultPageMock->expects($this->once())
            ->method('getLayout')
            ->willReturn($layoutMock);

        $this->resultFactoryMock->expects($this->once())
            ->method('create')
            ->with(\Magento\Framework\Controller\ResultFactory::TYPE_PAGE)
            ->willReturn($resultPageMock);
        return $resultPageMock;
    }

    public function testExecute()
    {
        $paymentMethodNonce = 'nonce';
        $email = 'abc@example.com';
        $billingAddress = ['someAddress'];
        $details = [
            'email' => $email,
            'billingAddress' => $billingAddress,
        ];
        $detailsEncoded = json_encode($details);

        $this->requestMock->expects($this->at(0))
            ->method('getParam')
            ->with('payment_method_nonce')
            ->willReturn($paymentMethodNonce);
        $this->requestMock->expects($this->at(1))
            ->method('getParam')
            ->with('details')
            ->willReturn($detailsEncoded);

        $quoteMock = $this->setupCart();
        $this->checkoutMock->expects($this->once())
            ->method('initializeQuoteForReview')
            ->with($paymentMethodNonce, ['email' => $email]);
        $resultPageMock = $this->setupReviewPage($quoteMock);

        $this->assertEquals($resultPageMock, $this->controller->execute());
    }

    public function testExecuteWithBillingAddress()
    {
        $paymentMethodNonce = 'nonce';
        $email = 'abc@example.com';
        $billingAddress = ['someAddress'];
        $details = [
            'email' => $email,
            'billingAddress' => $billingAddress,
        ];
        $detailsEncoded = json_encode($details);

        $this->braintreePayPalConfigMock->expects($this->once())
            ->method('isBillingAddressEnabled')
            ->willReturn(true);
        $this->requestMock->expects($this->at(0))
            ->method('getParam')
            ->with('payment_method_nonce')
            ->willReturn($paymentMethodNonce);
        $this->requestMock->expects($this->at(1))
            ->method('getParam')
            ->with('details')
            ->willReturn($detailsEncoded);

        $quoteMock = $this->setupCart();
        $this->checkoutMock->expects($this->once())
            ->method('initializeQuoteForReview')
            ->with($paymentMethodNonce, $details);
        $resultPageMock = $this->setupReviewPage($quoteMock);

        $this->assertEquals($resultPageMock, $this->controller->execute());
    }

    public function testExecuteRefresh()
    {
        $quoteMock = $this->setupCart();
        $this->paymentMethodInstanceMock->expects($this->once())
            ->method('getCode')
            ->willReturn(\Magento\Braintree\Model\PaymentMethod\PayPal::METHOD_CODE);
        $resultPageMock = $this->setupReviewPage($quoteMock);

        $this->assertEquals($resultPageMock, $this->controller->execute());
    }

    public function testExecuteNoPayment()
    {
        $quoteMock = $this->setupCart(false);
        $paymentMock = $this->getMockBuilder('\Magento\Quote\Model\Quote\Payment')
            ->disableOriginalConstructor()
            ->setMethods(['getMethodInstance'])
            ->getMock();
        $paymentMock->expects($this->once())
            ->method('getMethodInstance')
            ->willReturn(null);
        $quoteMock->expects($this->once())
            ->method('getPayment')
            ->willReturn($paymentMock);

        $this->messageManagerMock->expects($this->once())
            ->method('addErrorMessage')
            ->with(new \Magento\Framework\Phrase('Incorrect payment method.'));

        $resultRedirect = $this->getMockBuilder('\Magento\Framework\Controller\Result\Redirect')
            ->disableOriginalConstructor()
            ->getMock();
        $resultRedirect->expects($this->once())
            ->method('setPath')
            ->with('checkout/cart')
            ->willReturnSelf();
        $this->resultFactoryMock->expects($this->once())
            ->method('create')
            ->with(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT)
            ->willReturn($resultRedirect);
        $this->assertEquals($resultRedirect, $this->controller->execute());
    }

    public function testExecuteIncorrectPaymentMathod()
    {
        $this->setupCart();
        $this->paymentMethodInstanceMock->expects($this->once())
            ->method('getCode')
            ->willReturn('incorrect_method');
        $this->messageManagerMock->expects($this->once())
            ->method('addErrorMessage')
            ->with(new \Magento\Framework\Phrase('Incorrect payment method.'));

        $resultRedirect = $this->getMockBuilder('\Magento\Framework\Controller\Result\Redirect')
            ->disableOriginalConstructor()
            ->getMock();
        $resultRedirect->expects($this->once())
            ->method('setPath')
            ->with('checkout/cart')
            ->willReturnSelf();
        $this->resultFactoryMock->expects($this->once())
            ->method('create')
            ->with(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT)
            ->willReturn($resultRedirect);
        $this->assertEquals($resultRedirect, $this->controller->execute());
    }

    public function testExecuteValidationFailure()
    {
        $paymentMethodNonce = 'nonce';
        $email = 'abc@example.com';
        $billingAddress = ['someAddress'];
        $details = [
            'email' => $email,
            'billingAddress' => $billingAddress,
        ];
        $detailsEncoded = json_encode($details);

        $this->braintreePayPalConfigMock->expects($this->once())
            ->method('isBillingAddressEnabled')
            ->willReturn(true);
        $this->requestMock->expects($this->at(0))
            ->method('getParam')
            ->with('payment_method_nonce')
            ->willReturn($paymentMethodNonce);
        $this->requestMock->expects($this->at(1))
            ->method('getParam')
            ->with('details')
            ->willReturn($detailsEncoded);

        $this->setupCart();
        $errorMessage = new \Magento\Framework\Phrase('Selected payment type is not allowed for billing country.');
        $exception = new \Magento\Framework\Exception\LocalizedException($errorMessage);

        $this->paymentMethodInstanceMock->expects($this->once())
            ->method('validate')
            ->willThrowException($exception);
        $this->messageManagerMock->expects($this->once())
            ->method('addExceptionMessage')
            ->with($exception, $errorMessage);

        $resultRedirect = $this->getMockBuilder('\Magento\Framework\Controller\Result\Redirect')
            ->disableOriginalConstructor()
            ->getMock();
        $resultRedirect->expects($this->once())
            ->method('setPath')
            ->with('checkout/cart')
            ->willReturnSelf();
        $this->resultFactoryMock->expects($this->once())
            ->method('create')
            ->with(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT)
            ->willReturn($resultRedirect);
        $this->assertEquals($resultRedirect, $this->controller->execute());
    }

    public function testExecuteException()
    {
        $paymentMethodNonce = 'nonce';
        $email = 'abc@example.com';
        $billingAddress = ['someAddress'];
        $details = [
            'email' => $email,
            'billingAddress' => $billingAddress,
        ];
        $detailsEncoded = json_encode($details);

        $this->requestMock->expects($this->at(0))
            ->method('getParam')
            ->with('payment_method_nonce')
            ->willReturn($paymentMethodNonce);
        $this->requestMock->expects($this->at(1))
            ->method('getParam')
            ->with('details')
            ->willReturn($detailsEncoded);

        $this->setupCart();

        $errorMsg = new \Magento\Framework\Phrase('error');
        $exception = new \Magento\Framework\Exception\LocalizedException($errorMsg);
        $this->messageManagerMock->expects($this->once())
            ->method('addExceptionMessage')
            ->with($exception, $errorMsg);

        $this->checkoutMock->expects($this->once())
            ->method('initializeQuoteForReview')
            ->with($paymentMethodNonce, ['email' => $email])
            ->willThrowException($exception);

        $resultRedirect = $this->getMockBuilder('\Magento\Framework\Controller\Result\Redirect')
            ->disableOriginalConstructor()
            ->getMock();
        $resultRedirect->expects($this->once())
            ->method('setPath')
            ->with('checkout/cart')
            ->willReturnSelf();
        $this->resultFactoryMock->expects($this->once())
            ->method('create')
            ->with(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT)
            ->willReturn($resultRedirect);
        $this->assertEquals($resultRedirect, $this->controller->execute());
    }
}
