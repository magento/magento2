<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Braintree\Test\Unit\Controller\Paypal;

use Magento\Braintree\Controller\Paypal\PlaceOrder;
use Magento\Braintree\Gateway\Config\PayPal\Config;
use Magento\Braintree\Model\Paypal\Helper\OrderPlace;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Quote\Model\Quote;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;

/**
 * Class PlaceOrderTest
 *
 * @see \Magento\Braintree\Controller\Paypal\PlaceOrder
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PlaceOrderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var OrderPlace|MockObject
     */
    private $orderPlace;

    /**
     * @var Config|MockObject
     */
    private $config;

    /**
     * @var Session|MockObject
     */
    private $checkoutSession;

    /**
     * @var RequestInterface|MockObject
     */
    private $request;

    /**
     * @var ResultFactory|MockObject
     */
    private $resultFactory;

    /**
     * @var ManagerInterface|MockObject
     */
    private $messageManager;

    /**
     * @var PlaceOrder
     */
    private $placeOrder;

    /**
     * @var MockObject
     */
    private $logger;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        /** @var Context|MockObject $context */
        $context = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->request = $this->getMockBuilder(RequestInterface::class)
            ->setMethods(['getPostValue'])
            ->getMockForAbstractClass();
        $this->resultFactory = $this->getMockBuilder(ResultFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->checkoutSession = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->config = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->orderPlace = $this->getMockBuilder(OrderPlace::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->messageManager = $this->getMockBuilder(ManagerInterface::class)
            ->getMockForAbstractClass();

        $context->method('getRequest')
            ->willReturn($this->request);
        $context->method('getResultFactory')
            ->willReturn($this->resultFactory);
        $context->method('getMessageManager')
            ->willReturn($this->messageManager);

        $this->logger = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->placeOrder = new PlaceOrder(
            $context,
            $this->config,
            $this->checkoutSession,
            $this->orderPlace,
            $this->logger
        );
    }

    /**
     * Checks if an order is placed successfully.
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function testExecute()
    {
        $agreement = ['test-data'];

        $quoteMock = $this->getQuoteMock();
        $quoteMock->method('getItemsCount')
            ->willReturn(1);

        $resultMock = $this->getResultMock();
        $resultMock->method('setPath')
            ->with('checkout/onepage/success')
            ->willReturnSelf();

        $this->resultFactory->method('create')
            ->with(ResultFactory::TYPE_REDIRECT)
            ->willReturn($resultMock);

        $this->request->method('getPostValue')
            ->with('agreement', [])
            ->willReturn($agreement);

        $this->checkoutSession->method('getQuote')
            ->willReturn($quoteMock);

        $this->orderPlace->method('execute')
            ->with($quoteMock, [0]);

        $this->messageManager->expects(self::never())
            ->method('addExceptionMessage');

        self::assertEquals($this->placeOrder->execute(), $resultMock);
    }

    /**
     * Checks a negative scenario during place order action.
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function testExecuteException()
    {
        $agreement = ['test-data'];

        $quote = $this->getQuoteMock();
        $quote->method('getItemsCount')
            ->willReturn(0);
        $quote->method('getReservedOrderId')
            ->willReturn('000000111');

        $resultMock = $this->getResultMock();
        $resultMock->method('setPath')
            ->with('checkout/cart')
            ->willReturnSelf();

        $this->resultFactory->method('create')
            ->with(ResultFactory::TYPE_REDIRECT)
            ->willReturn($resultMock);

        $this->request->method('getPostValue')
            ->with('agreement', [])
            ->willReturn($agreement);

        $this->checkoutSession->method('getQuote')
            ->willReturn($quote);

        $this->orderPlace->expects(self::never())
            ->method('execute');

        $this->messageManager->method('addExceptionMessage')
            ->with(
                self::isInstanceOf('\InvalidArgumentException'),
                'The order #000000111 cannot be processed.'
            );

        self::assertEquals($this->placeOrder->execute(), $resultMock);
    }

    /**
     * Gets mock object for a result.
     *
     * @return ResultInterface|MockObject
     */
    private function getResultMock()
    {
        return $this->getMockBuilder(ResultInterface::class)
            ->setMethods(['setPath'])
            ->getMockForAbstractClass();
    }

    /**
     * Gets mock object for a quote.
     *
     * @return Quote|MockObject
     */
    private function getQuoteMock()
    {
        return $this->getMockBuilder(Quote::class)
            ->disableOriginalConstructor()
            ->getMock();
    }
}
