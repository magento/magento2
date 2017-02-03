<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Braintree\Test\Unit\Controller\PayPal;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class PlaceOrderTest extends \PHPUnit_Framework_TestCase
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
    protected $messageManager;

    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @var \Magento\Braintree\Controller\PayPal\PlaceOrder
     */
    protected $controller;

    /**
     * @var \Magento\Checkout\Api\AgreementsValidatorInterface |\PHPUnit_Framework_MockObject_MockObject
     */
    protected $validatorMock;

    /**
     * test setup
     */
    public function setUp()
    {
        $this->checkoutSessionMock = $this->getMockBuilder('\Magento\Checkout\Model\Session')
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'clearHelperData',
                    'getQuote',
                    'setLastQuoteId',
                    'setLastSuccessQuoteId',
                    'setLastOrderId',
                    'setLastRealOrderId',
                ]
            )
            ->getMock();

        $this->checkoutFactoryMock = $this->getMockBuilder('\Magento\Braintree\Model\CheckoutFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->messageManager = $this->getMock('\Magento\Framework\Message\ManagerInterface');

        $this->checkoutMock = $this->getMockBuilder('\Magento\Braintree\Model\Checkout')
            ->disableOriginalConstructor()
            ->getMock();

        $this->requestMock = $this->getMock('\Magento\Framework\App\Request\Http', [], [], '', '', false);

        $this->resultFactoryMock = $this->getMockBuilder('\Magento\Framework\Controller\ResultFactory')
            ->disableOriginalConstructor()
            ->getMock();

        $contextMock = $this->getMockBuilder('\Magento\Framework\App\Action\Context')
            ->disableOriginalConstructor()
            ->getMock();
        $contextMock->expects($this->any())
            ->method('getResultFactory')
            ->willReturn($this->resultFactoryMock);
        $contextMock->expects($this->any())
            ->method('getRequest')
            ->willReturn($this->requestMock);
        $contextMock->expects($this->any())
            ->method('getMessageManager')
            ->willReturn($this->messageManager);
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->validatorMock = $this->getMock('Magento\Checkout\Api\AgreementsValidatorInterface');
        $this->controller = $this->objectManagerHelper->getObject(
            '\Magento\Braintree\Controller\PayPal\PlaceOrder',
            [
                'context' => $contextMock,
                'checkoutSession' => $this->checkoutSessionMock,
                'checkoutFactory' => $this->checkoutFactoryMock,
                'agreementsValidator' => $this->validatorMock
            ]
        );
    }

    protected function setupCart()
    {
        $quoteMock = $this->getMockBuilder('\Magento\Quote\Model\Quote')
            ->disableOriginalConstructor()
            ->setMethods(['hasItems', 'getHasError', 'getPayment', 'getId'])
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

        $this->requestMock->expects($this->any())->method('getPost')->willReturn([]);
        return $quoteMock;
    }

    public function testExecute()
    {
        $quoteId = 123;
        $orderId = 'orderId';
        $orderIncrementId = 125;

        $quoteMock = $this->setupCart();
        $this->validatorMock->expects($this->once())->method('isValid')->willReturn(true);
        $this->checkoutMock->expects($this->once())
            ->method('place')
            ->with(null);

        $this->checkoutSessionMock->expects($this->once())
            ->method('clearHelperData');

        $quoteMock->expects($this->once())
            ->method('getId')
            ->willReturn($quoteId);

        $this->checkoutSessionMock->expects($this->once())
            ->method('setLastQuoteId')
            ->with($quoteId)
            ->willReturnSelf();
        $this->checkoutSessionMock->expects($this->once())
            ->method('setLastSuccessQuoteId')
            ->with($quoteId)
            ->willReturnSelf();

        $orderMock = $this->getMockBuilder('\Magento\Sales\Model\Order')
            ->disableOriginalConstructor()
            ->getMock();
        $orderMock->expects($this->once())
            ->method('getId')
            ->willReturn($orderId);
        $orderMock->expects($this->once())
            ->method('getIncrementId')
            ->willReturn($orderIncrementId);

        $this->checkoutMock->expects($this->once())
            ->method('getOrder')
            ->willReturn($orderMock);

        $this->checkoutSessionMock->expects($this->once())
            ->method('setLastOrderId')
            ->with($orderId)
            ->willReturnSelf();
        $this->checkoutSessionMock->expects($this->once())
            ->method('setLastRealOrderId')
            ->with($orderIncrementId)
            ->willReturnSelf();

        $resultRedirect = $this->getMockBuilder('\Magento\Framework\Controller\Result\Redirect')
            ->disableOriginalConstructor()
            ->getMock();
        $resultRedirect->expects($this->once())
            ->method('setPath')
            ->with('checkout/onepage/success')
            ->willReturnSelf();
        $this->resultFactoryMock->expects($this->once())
            ->method('create')
            ->with(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT)
            ->willReturn($resultRedirect);

        $this->assertEquals($resultRedirect, $this->controller->execute());
    }

    public function testExecuteException()
    {
        $this->setupCart();
        $this->validatorMock->expects($this->once())->method('isValid')->willReturn(true);
        $exceptionMsg = new \Magento\Framework\Phrase('error');
        $exception = new \Magento\Framework\Exception\LocalizedException($exceptionMsg);
        $this->checkoutMock->expects($this->once())
            ->method('place')
            ->with(null)
            ->willThrowException($exception);

        $this->messageManager->expects($this->once())
            ->method('addExceptionMessage')
            ->with($exception, $exceptionMsg);

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
