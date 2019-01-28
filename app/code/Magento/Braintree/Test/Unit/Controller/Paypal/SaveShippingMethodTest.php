<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Braintree\Test\Unit\Controller\Paypal;

use Magento\Quote\Model\Quote;
use Magento\Framework\View\Layout;
use Magento\Checkout\Model\Session;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Result\Page;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Braintree\Block\Paypal\Checkout\Review;
use Magento\Braintree\Gateway\Config\PayPal\Config;
use Magento\Braintree\Controller\Paypal\SaveShippingMethod;
use Magento\Braintree\Model\Paypal\Helper\ShippingMethodUpdater;

/**
 * Class SaveShippingMethodTest
 *
 * @see \Magento\Braintree\Controller\Paypal\SaveShippingMethod
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SaveShippingMethodTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ShippingMethodUpdater|\PHPUnit_Framework_MockObject_MockObject
     */
    private $shippingMethodUpdaterMock;

    /**
     * @var Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configMock;

    /**
     * @var Session|\PHPUnit_Framework_MockObject_MockObject
     */
    private $checkoutSessionMock;

    /**
     * @var RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requestMock;

    /**
     * @var ResponseInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $responseMock;

    /**
     * @var RedirectInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $redirectMock;

    /**
     * @var UrlInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $urlMock;

    /**
     * @var ResultFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultFactoryMock;

    /**
     * @var ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $messageManagerMock;

    /**
     * @var SaveShippingMethod
     */
    private $saveShippingMethod;

    protected function setUp()
    {
        /** @var Context|\PHPUnit_Framework_MockObject_MockObject $contextMock */
        $contextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->requestMock = $this->getMockBuilder(RequestInterface::class)
            ->getMockForAbstractClass();
        $this->redirectMock = $this->getMockBuilder(RedirectInterface::class)
            ->getMockForAbstractClass();
        $this->urlMock = $this->getMockBuilder(UrlInterface::class)
            ->getMockForAbstractClass();
        $this->responseMock = $this->getMockBuilder(ResponseInterface::class)
            ->setMethods(['setBody'])
            ->getMockForAbstractClass();
        $this->resultFactoryMock = $this->getMockBuilder(ResultFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->checkoutSessionMock = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->configMock = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->shippingMethodUpdaterMock = $this->getMockBuilder(ShippingMethodUpdater::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->messageManagerMock = $this->getMockBuilder(ManagerInterface::class)
            ->getMockForAbstractClass();

        $contextMock->expects($this->once())
            ->method('getRequest')
            ->willReturn($this->requestMock);
        $contextMock->expects($this->once())
            ->method('getRedirect')
            ->willReturn($this->redirectMock);
        $contextMock->expects($this->once())
            ->method('getResponse')
            ->willReturn($this->responseMock);
        $contextMock->expects($this->once())
            ->method('getUrl')
            ->willReturn($this->urlMock);
        $contextMock->expects($this->once())
            ->method('getResultFactory')
            ->willReturn($this->resultFactoryMock);
        $contextMock->expects($this->once())
            ->method('getMessageManager')
            ->willReturn($this->messageManagerMock);

        $this->saveShippingMethod = new SaveShippingMethod(
            $contextMock,
            $this->configMock,
            $this->checkoutSessionMock,
            $this->shippingMethodUpdaterMock
        );
    }

    public function testExecuteAjax()
    {
        $resultHtml = '<html>test</html>';
        $quoteMock = $this->getQuoteMock();
        $responsePageMock = $this->getResponsePageMock();
        $layoutMock = $this->getLayoutMock();
        $blockMock = $this->getBlockMock();

        $quoteMock->expects($this->once())
            ->method('getItemsCount')
            ->willReturn(1);

        $this->requestMock->expects($this->exactly(2))
            ->method('getParam')
            ->willReturnMap(
                [
                    ['isAjax', null, true],
                    ['shipping_method', null, 'test-shipping-method']
                ]
            );

        $this->checkoutSessionMock->expects($this->once())
            ->method('getQuote')
            ->willReturn($quoteMock);

        $this->shippingMethodUpdaterMock->expects($this->once())
            ->method('execute')
            ->with('test-shipping-method', $quoteMock);

        $this->resultFactoryMock->expects($this->once())
            ->method('create')
            ->with(ResultFactory::TYPE_PAGE)
            ->willReturn($responsePageMock);

        $responsePageMock->expects($this->once())
            ->method('addHandle')
            ->with('paypal_express_review_details')
            ->willReturnSelf();

        $responsePageMock->expects($this->once())
            ->method('getLayout')
            ->willReturn($layoutMock);

        $layoutMock->expects($this->once())
            ->method('getBlock')
            ->with('page.block')
            ->willReturn($blockMock);

        $blockMock->expects($this->once())
            ->method('toHtml')
            ->willReturn($resultHtml);

        $this->responseMock->expects($this->once())
            ->method('setBody')
            ->with($resultHtml);

        $this->urlMock->expects($this->never())
            ->method('getUrl');

        $this->saveShippingMethod->execute();
    }

    public function testExecuteAjaxException()
    {
        $redirectPath = 'path/to/redirect';
        $quoteMock = $this->getQuoteMock();

        $quoteMock->expects($this->once())
            ->method('getItemsCount')
            ->willReturn(0);

        $this->requestMock->expects($this->exactly(1))
            ->method('getParam')
            ->willReturnMap(
                [
                    ['isAjax', null, false]
                ]
            );

        $this->checkoutSessionMock->expects($this->once())
            ->method('getQuote')
            ->willReturn($quoteMock);

        $this->shippingMethodUpdaterMock->expects($this->never())
            ->method('execute');

        $this->messageManagerMock->expects($this->once())
            ->method('addExceptionMessage')
            ->with(
                $this->isInstanceOf('\InvalidArgumentException'),
                'Checkout failed to initialize. Verify and try again.'
            );

        $this->urlMock->expects($this->once())
            ->method('getUrl')
            ->with('*/*/review', ['_secure' => true])
            ->willReturn($redirectPath);

        $this->redirectMock->expects($this->once())
            ->method('redirect')
            ->with($this->responseMock, $redirectPath, []);

        $this->saveShippingMethod->execute();
    }

    public function testExecuteException()
    {
        $redirectPath = 'path/to/redirect';
        $quoteMock = $this->getQuoteMock();

        $quoteMock->expects($this->once())
            ->method('getItemsCount')
            ->willReturn(0);

        $this->requestMock->expects($this->exactly(1))
            ->method('getParam')
            ->willReturnMap(
                [
                    ['isAjax', null, true]
                ]
            );

        $this->checkoutSessionMock->expects($this->once())
            ->method('getQuote')
            ->willReturn($quoteMock);

        $this->shippingMethodUpdaterMock->expects($this->never())
            ->method('execute');

        $this->messageManagerMock->expects($this->once())
            ->method('addExceptionMessage')
            ->with(
                $this->isInstanceOf('\InvalidArgumentException'),
                'Checkout failed to initialize. Verify and try again.'
            );

        $this->urlMock->expects($this->once())
            ->method('getUrl')
            ->with('*/*/review', ['_secure' => true])
            ->willReturn($redirectPath);

        $this->responseMock->expects($this->once())
            ->method('setBody')
            ->with(sprintf('<script>window.location.href = "%s";</script>', $redirectPath));

        $this->saveShippingMethod->execute();
    }

    /**
     * @return Review|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getBlockMock()
    {
        return $this->getMockBuilder(Review::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return Layout|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getLayoutMock()
    {
        return $this->getMockBuilder(Layout::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return Quote|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getQuoteMock()
    {
        return $this->getMockBuilder(Quote::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return Page|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getResponsePageMock()
    {
        return $this->getMockBuilder(Page::class)
            ->disableOriginalConstructor()
            ->getMock();
    }
}
