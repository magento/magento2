<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Braintree\Test\Unit\Controller\Paypal;

use Magento\Payment\Model\Method\Logger;
use Magento\Quote\Model\Quote;
use Magento\Framework\View\Layout;
use Magento\Checkout\Model\Session;
use Magento\Framework\View\Result\Page;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Braintree\Controller\Paypal\Review;
use Magento\Braintree\Gateway\Config\PayPal\Config;
use Magento\Braintree\Model\Paypal\Helper\QuoteUpdater;
use Magento\Braintree\Block\Paypal\Checkout\Review as CheckoutReview;

/**
 * Class ReviewTest
 *
 * @see \Magento\Braintree\Controller\Paypal\Review
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ReviewTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var QuoteUpdater|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteUpdaterMock;

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
     * @var ResultFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultFactoryMock;

    /**
     * @var ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $messageManagerMock;

    /**
     * @var Review
     */
    private $review;

    /**
     * @var Logger|\PHPUnit_Framework_MockObject_MockObject
     */
    private $loggerMock;

    protected function setUp()
    {
        /** @var Context|\PHPUnit_Framework_MockObject_MockObject $contextMock */
        $contextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->requestMock = $this->getMockBuilder(RequestInterface::class)
            ->setMethods(['getPostValue'])
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
        $this->quoteUpdaterMock = $this->getMockBuilder(QuoteUpdater::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->messageManagerMock = $this->getMockBuilder(ManagerInterface::class)
            ->getMockForAbstractClass();
        $this->loggerMock = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();

        $contextMock->expects(self::once())
            ->method('getRequest')
            ->willReturn($this->requestMock);
        $contextMock->expects(self::once())
            ->method('getResultFactory')
            ->willReturn($this->resultFactoryMock);
        $contextMock->expects(self::once())
            ->method('getMessageManager')
            ->willReturn($this->messageManagerMock);

        $this->review = new Review(
            $contextMock,
            $this->configMock,
            $this->checkoutSessionMock,
            $this->quoteUpdaterMock,
            $this->loggerMock
        );
    }

    public function testExecute()
    {
        $result = '{"nonce": ["test-value"], "details": ["test-value"]}';

        $resultPageMock = $this->getResultPageMock();
        $layoutMock = $this->getLayoutMock();
        $blockMock = $this->getBlockMock();
        $quoteMock = $this->getQuoteMock();
        $childBlockMock = $this->getChildBlockMock();

        $quoteMock->expects(self::once())
            ->method('getItemsCount')
            ->willReturn(1);

        $this->requestMock->expects(self::once())
            ->method('getPostValue')
            ->with('result', '{}')
            ->willReturn($result);

        $this->checkoutSessionMock->expects(self::once())
            ->method('getQuote')
            ->willReturn($quoteMock);

        $this->quoteUpdaterMock->expects(self::once())
            ->method('execute')
            ->with(['test-value'], ['test-value'], $quoteMock);

        $this->resultFactoryMock->expects(self::once())
            ->method('create')
            ->with(ResultFactory::TYPE_PAGE)
            ->willReturn($resultPageMock);

        $resultPageMock->expects(self::once())
            ->method('getLayout')
            ->willReturn($layoutMock);

        $layoutMock->expects(self::once())
            ->method('getBlock')
            ->with('braintree.paypal.review')
            ->willReturn($blockMock);

        $blockMock->expects(self::once())
            ->method('setQuote')
            ->with($quoteMock);
        $blockMock->expects(self::once())
            ->method('getChildBlock')
            ->with('shipping_method')
            ->willReturn($childBlockMock);

        $childBlockMock->expects(self::once())
            ->method('setData')
            ->with('quote', $quoteMock);

        self::assertEquals($this->review->execute(), $resultPageMock);
    }

    public function testExecuteException()
    {
        $result = '{}';
        $quoteMock = $this->getQuoteMock();
        $resultRedirectMock = $this->getResultRedirectMock();

        $quoteMock->expects(self::once())
            ->method('getItemsCount')
            ->willReturn(0);

        $this->requestMock->expects(self::once())
            ->method('getPostValue')
            ->with('result', '{}')
            ->willReturn($result);

        $this->checkoutSessionMock->expects(self::once())
            ->method('getQuote')
            ->willReturn($quoteMock);

        $this->quoteUpdaterMock->expects(self::never())
            ->method('execute');

        $this->messageManagerMock->expects(self::once())
            ->method('addExceptionMessage')
            ->with(
                self::isInstanceOf('\InvalidArgumentException'),
                'Checkout failed to initialize. Verify and try again.'
            );

        $this->resultFactoryMock->expects(self::once())
            ->method('create')
            ->with(ResultFactory::TYPE_REDIRECT)
            ->willReturn($resultRedirectMock);

        $resultRedirectMock->expects(self::once())
            ->method('setPath')
            ->with('checkout/cart')
            ->willReturnSelf();

        self::assertEquals($this->review->execute(), $resultRedirectMock);
    }

    public function testExecuteExceptionPaymentWithoutNonce()
    {
        $result = '{}';
        $quoteMock = $this->getQuoteMock();
        $resultRedirectMock = $this->getResultRedirectMock();

        $quoteMock->expects(self::once())
            ->method('getItemsCount')
            ->willReturn(1);

        $paymentMock = $this->getMockBuilder(\Magento\Quote\Model\Quote\Payment::class)
            ->disableOriginalConstructor()
            ->getMock();

        $quoteMock->expects(self::once())
            ->method('getPayment')
            ->willReturn($paymentMock);

        $this->requestMock->expects(self::once())
            ->method('getPostValue')
            ->with('result', '{}')
            ->willReturn($result);

        $this->checkoutSessionMock->expects(self::once())
            ->method('getQuote')
            ->willReturn($quoteMock);

        $this->messageManagerMock->expects(self::once())
            ->method('addExceptionMessage')
            ->with(
                self::isInstanceOf(\Magento\Framework\Exception\LocalizedException::class),
                'Checkout failed to initialize. Verify and try again.'
            );

        $this->resultFactoryMock->expects(self::once())
            ->method('create')
            ->with(ResultFactory::TYPE_REDIRECT)
            ->willReturn($resultRedirectMock);

        $resultRedirectMock->expects(self::once())
            ->method('setPath')
            ->with('checkout/cart')
            ->willReturnSelf();

        self::assertEquals($this->review->execute(), $resultRedirectMock);
    }

    /**
     * @return Redirect|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getResultRedirectMock()
    {
        return $this->getMockBuilder(Redirect::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return AbstractBlock|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getChildBlockMock()
    {
        return $this->getMockBuilder(AbstractBlock::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return CheckoutReview|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getBlockMock()
    {
        return $this->getMockBuilder(CheckoutReview::class)
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
     * @return Page|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getResultPageMock()
    {
        return $this->getMockBuilder(Page::class)
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
}
