<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Braintree\Test\Unit\Controller\PayPal;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\Controller\ResultFactory;

class SaveShippingMethodTest extends \PHPUnit_Framework_TestCase
{
    const REVIEW_URL = 'http://localhost/braintree/paypal/review';

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
     * @var \Magento\Framework\App\Response|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $responseMock;

    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @var \Magento\Braintree\Controller\PayPal\SaveShippingMethod
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

        $this->checkoutFactoryMock = $this->getMockBuilder('\Magento\Braintree\Model\CheckoutFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->messageManager = $this->getMock('\Magento\Framework\Message\ManagerInterface');

        $this->checkoutMock = $this->getMockBuilder('\Magento\Braintree\Model\Checkout')
            ->disableOriginalConstructor()
            ->getMock();

        $this->requestMock = $this->getMock('\Magento\Framework\App\RequestInterface');

        $this->resultFactoryMock = $this->getMockBuilder('\Magento\Framework\Controller\ResultFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $this->responseMock = $this->getMockBuilder('\Magento\Framework\App\Response')
            ->disableOriginalConstructor()
            ->setMethods(['setBody'])
            ->getMock();

        $urlBuilderMock = $this->getMock('\Magento\Framework\UrlInterface');
        $urlBuilderMock->expects($this->any())
            ->method('getUrl')
            ->willReturn(self::REVIEW_URL);

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
        $contextMock->expects($this->any())
            ->method('getResponse')
            ->willReturn($this->responseMock);
        $contextMock->expects($this->any())
            ->method('getUrl')
            ->willReturn($urlBuilderMock);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->controller = $this->objectManagerHelper->getObject(
            '\Magento\Braintree\Controller\PayPal\SaveShippingMethod',
            [
                'context' => $contextMock,
                'checkoutSession' => $this->checkoutSessionMock,
                'checkoutFactory' => $this->checkoutFactoryMock,
            ]
        );
    }

    protected function setupCart()
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

        return $quoteMock;
    }

    public function testExecute()
    {
        $html = '<html></html>';

        $shippingMethod = 'flat_rate';
        $this->requestMock->expects($this->at(0))
            ->method('getParam')
            ->with('isAjax')
            ->willReturn(true);
        $this->requestMock->expects($this->at(1))
            ->method('getParam')
            ->with('shipping_method')
            ->willReturn($shippingMethod);

        $this->setupCart();
        $this->checkoutMock->expects($this->once())
            ->method('updateShippingMethod')
            ->with($shippingMethod);

        $blockMock = $this->getMockBuilder('\Magento\Paypal\Block\Express\Review\Details')
            ->disableOriginalConstructor()
            ->getMock();
        $blockMock->expects($this->once())
            ->method('toHtml')
            ->willReturn($html);
        $layoutMock = $this->getMockBuilder('\Magento\Framework\View\Layout')
            ->disableOriginalConstructor()
            ->getMock();
        $layoutMock->expects($this->once())
            ->method('getBlock')
            ->with('page.block')
            ->willReturn($blockMock);
        $responsePageMock = $this->getMockBuilder('\Magento\Framework\View\Result\Page')
            ->disableOriginalConstructor()
            ->getMock();
        $responsePageMock->expects($this->once())
            ->method('addHandle')
            ->with('paypal_express_review_details')
            ->willReturnSelf();
        $responsePageMock->expects($this->once())
            ->method('getLayout')
            ->willReturn($layoutMock);

        $this->responseMock->expects($this->once())
            ->method('setBody')
            ->with($html);
        $this->resultFactoryMock->expects($this->once())
            ->method('create')
            ->with(ResultFactory::TYPE_PAGE)
            ->willReturn($responsePageMock);

        $this->controller->execute();
    }

    public function testExecuteException()
    {
        $html = '<script>window.location.href = '
            . self::REVIEW_URL
            . ';</script>';

        $shippingMethod = 'flat_rate';
        $this->requestMock->expects($this->at(0))
            ->method('getParam')
            ->with('isAjax')
            ->willReturn(true);
        $this->requestMock->expects($this->at(1))
            ->method('getParam')
            ->with('shipping_method')
            ->willReturn($shippingMethod);

        $this->setupCart();

        $exceptionMsg = new \Magento\Framework\Phrase('error');
        $exception = new \Magento\Framework\Exception\LocalizedException(
            $exceptionMsg
        );
        $this->checkoutMock->expects($this->once())
            ->method('updateShippingMethod')
            ->with($shippingMethod)
            ->willThrowException($exception);

        $this->messageManager->expects($this->once())
            ->method('addExceptionMessage')
            ->with($exception, $exceptionMsg);

        $this->responseMock->expects($this->once())
            ->method('setBody')
            ->with($html);

        $this->controller->execute();
    }
}
