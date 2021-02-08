<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Braintree\Controller\Paypal;

use Braintree\Result\Successful;
use Braintree\Transaction;
use Magento\Braintree\Model\Adapter\BraintreeAdapter;
use Magento\Braintree\Model\Adapter\BraintreeAdapterFactory;
use Magento\Checkout\Model\Session;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\Message\MessageInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\TestFramework\TestCase\AbstractController;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * PlaceOrderTest
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PlaceOrderTest extends AbstractController
{
    /**
     * @var Session|MockObject
     */
    private $session;

    /**
     * @var BraintreeAdapter|MockObject
     */
    private $adapter;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->session = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->setMethods(['getQuote', 'setLastOrderStatus', 'unsLastBillingAgreementReferenceId', 'getQuoteId'])
            ->getMock();

        $adapterFactory = $this->getMockBuilder(BraintreeAdapterFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->adapter = $this->getMockBuilder(BraintreeAdapter::class)
            ->disableOriginalConstructor()
            ->getMock();
        $adapterFactory->method('create')
            ->willReturn($this->adapter);

        $this->_objectManager->addSharedInstance($this->session, Session::class);
        $this->_objectManager->addSharedInstance($adapterFactory, BraintreeAdapterFactory::class);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        $this->_objectManager->removeSharedInstance(Session::class);
        $this->_objectManager->removeSharedInstance(BraintreeAdapterFactory::class);
        parent::tearDown();
    }

    /**
     * Tests a negative scenario for a place order flow when exception throws after placing an order.
     *
     * @magentoAppArea frontend
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/Braintree/Fixtures/paypal_quote.php
     */
    public function testExecuteWithFailedOrder()
    {
        $reservedOrderId = 'test01';
        $quote = $this->getQuote($reservedOrderId);

        $this->session->method('getQuote')
            ->willReturn($quote);
        $this->session->method('getQuoteId')
            ->willReturn($quote->getId());

        $this->adapter->method('sale')
            ->willReturn($this->getTransactionStub('authorized'));
        $this->adapter->method('void')
            ->willReturn($this->getTransactionStub('voided'));

        // emulates an error after placing the order
        $this->session->method('setLastOrderStatus')
            ->willThrowException(new \Exception('Test Exception'));

        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->dispatch('braintree/paypal/placeOrder');

        self::assertRedirect(self::stringContains('checkout/cart'));
        self::assertSessionMessages(
            self::equalTo(['The order #' . $reservedOrderId . ' cannot be processed.']),
            MessageInterface::TYPE_ERROR
        );

        $order = $this->getOrder($reservedOrderId);
        self::assertEquals('canceled', $order->getState());
    }

    /**
     * Tests a negative scenario for a place order flow when exception throws before order creation.
     *
     * @magentoAppArea frontend
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/Braintree/Fixtures/paypal_quote.php
     */
    public function testExecuteWithFailedQuoteValidation()
    {
        $reservedOrderId = null;
        $quote = $this->getQuote('test01');
        $quote->setReservedOrderId($reservedOrderId);

        $this->session->method('getQuote')
            ->willReturn($quote);
        $this->session->method('getQuoteId')
            ->willReturn($quote->getId());

        $this->adapter->method('sale')
            ->willReturn($this->getTransactionStub('authorized'));
        $this->adapter->method('void')
            ->willReturn($this->getTransactionStub('voided'));

        // emulates an error after placing the order
        $this->session->method('setLastOrderStatus')
            ->willThrowException(new \Exception('Test Exception'));

        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->dispatch('braintree/paypal/placeOrder');

        self::assertRedirect(self::stringContains('checkout/cart'));
        self::assertSessionMessages(
            self::equalTo(['The order #' . $reservedOrderId . ' cannot be processed.']),
            MessageInterface::TYPE_ERROR
        );
    }

    /**
     * Gets quote by reserved order ID.
     *
     * @param string $reservedOrderId
     * @return CartInterface
     */
    private function getQuote(string $reservedOrderId): CartInterface
    {
        $searchCriteria = $this->_objectManager->get(SearchCriteriaBuilder::class)
            ->addFilter('reserved_order_id', $reservedOrderId)
            ->create();

        /** @var CartRepositoryInterface $quoteRepository */
        $quoteRepository = $this->_objectManager->get(CartRepositoryInterface::class);
        $items = $quoteRepository->getList($searchCriteria)
            ->getItems();

        return array_pop($items);
    }

    /**
     * Gets order by increment ID.
     *
     * @param string $incrementId
     * @return OrderInterface
     */
    private function getOrder(string $incrementId): OrderInterface
    {
        $searchCriteria = $this->_objectManager->get(SearchCriteriaBuilder::class)
            ->addFilter('increment_id', $incrementId)
            ->create();

        /** @var OrderRepositoryInterface $repository */
        $repository = $this->_objectManager->get(OrderRepositoryInterface::class);
        $items = $repository->getList($searchCriteria)
            ->getItems();

        return array_pop($items);
    }

    /**
     * Creates stub for Braintree Transaction.
     *
     * @param string $status
     * @return Successful
     */
    private function getTransactionStub(string $status): Successful
    {
        $transaction = $this->getMockBuilder(Transaction::class)
            ->disableOriginalConstructor()
            ->getMock();
        $transaction->status = $status;
        $transaction->paypal = [
            'paymentId' => 'pay-001',
            'payerEmail' => 'test@test.com'
        ];
        $response = new Successful();
        $response->success = true;
        $response->transaction = $transaction;

        return $response;
    }
}
