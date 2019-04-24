<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Braintree\Model;

use Braintree\Result\Successful;
use Braintree\Transaction;
use Magento\Braintree\Gateway\Command\GetPaymentNonceCommand;
use Magento\Braintree\Model\Adapter\BraintreeAdapter;
use Magento\Braintree\Model\Adapter\BraintreeAdapterFactory;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Multishipping\Model\Checkout\Type\Multishipping;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use \PHPUnit_Framework_MockObject_MockObject as MockObject;
use Magento\Payment\Gateway\Command\ResultInterface as CommandResultInterface;

/**
 * Tests Magento\Multishipping\Model\Checkout\Type\Multishipping with Braintree and BraintreePayPal payments.
 *
 * @magentoAppArea frontend
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class MultishippingTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var BraintreeAdapter|MockObject
     */
    private $adapter;

    /**
     * @var Multishipping
     */
    private $model;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();

        $orderSender = $this->getMockBuilder(OrderSender::class)
            ->disableOriginalConstructor()
            ->getMock();

        $adapterFactory = $this->getMockBuilder(BraintreeAdapterFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->adapter = $this->getMockBuilder(BraintreeAdapter::class)
            ->disableOriginalConstructor()
            ->getMock();
        $adapterFactory->method('create')
            ->willReturn($this->adapter);

        $this->objectManager->addSharedInstance($adapterFactory, BraintreeAdapterFactory::class);
        $this->objectManager->addSharedInstance($this->getPaymentNonceMock(), GetPaymentNonceCommand::class);

        $this->model = $this->objectManager->create(
            Multishipping::class,
            ['orderSender' => $orderSender]
        );
    }

    /**
     * Checks a case when multiple orders are created successfully using Braintree payment method.
     *
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/Braintree/Fixtures/quote_with_split_items_braintree.php
     * @magentoConfigFixture current_store payment/braintree/active 1
     * @return void
     */
    public function testCreateOrdersWithBraintree()
    {
        $this->adapter->method('sale')
            ->willReturn(
                $this->getTransactionStub()
            );
        $this->createOrders();
    }

    /**
     * Checks a case when multiple orders are created successfully using Braintree PayPal payment method.
     *
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/Braintree/Fixtures/quote_with_split_items_braintree_paypal.php
     * @magentoConfigFixture current_store payment/braintree_paypal/active 1
     * @return void
     */
    public function testCreateOrdersWithBraintreePaypal()
    {
        $this->adapter->method('sale')
            ->willReturn(
                $this->getTransactionPaypalStub()
            );
        $this->createOrders();
    }

    /**
     * Creates orders for multishipping checkout flow.
     *
     * @return void
     */
    private function createOrders()
    {
        $expectedPlacedOrdersNumber = 3;
        $quote = $this->getQuote('multishipping_quote_id_braintree');

        /** @var CheckoutSession $session */
        $session = $this->objectManager->get(CheckoutSession::class);
        $session->replaceQuote($quote);

        $this->model->createOrders();

        $orderList = $this->getOrderList((int)$quote->getId());
        self::assertCount(
            $expectedPlacedOrdersNumber,
            $orderList,
            'Total successfully placed orders number mismatch'
        );
    }

    /**
     * Creates stub for Braintree capture Transaction.
     *
     * @return Successful
     */
    private function getTransactionStub(): Successful
    {
        $transaction = $this->getMockBuilder(Transaction::class)
            ->disableOriginalConstructor()
            ->getMock();
        $transaction->status = 'submitted_for_settlement';
        $transaction->creditCard = [
            'last4' => '1111',
            'cardType' => 'Visa',
            'expirationMonth' => '12',
            'expirationYear' => '2021'
        ];

        $creditCardDetails = new \stdClass();
        $creditCardDetails->token = '4fdg';
        $creditCardDetails->expirationMonth = '12';
        $creditCardDetails->expirationYear = '2021';
        $creditCardDetails->cardType = 'Visa';
        $creditCardDetails->last4 = '1111';
        $creditCardDetails->expirationDate = '12/2021';
        $transaction->creditCardDetails = $creditCardDetails;

        $response = new Successful();
        $response->success = true;
        $response->transaction = $transaction;

        return $response;
    }

    /**
     * Creates stub for BraintreePaypal capture Transaction.
     *
     * @return Successful
     */
    private function getTransactionPaypalStub(): Successful
    {
        $transaction = $this->getMockBuilder(Transaction::class)
            ->disableOriginalConstructor()
            ->getMock();
        $transaction->status = 'submitted_for_settlement';
        $transaction->paypal = [
            'token' => 'fchxqx',
            'payerEmail' => 'payer@example.com',
            'paymentId' => 'PAY-33ac47a28e7f54791f6cda45',
        ];
        $paypalDetails = new \stdClass();
        $paypalDetails->token = 'fchxqx';
        $paypalDetails->payerEmail = 'payer@example.com';
        $paypalDetails->paymentId = '33ac47a28e7f54791f6cda45';
        $transaction->paypalDetails = $paypalDetails;

        $response = new Successful();
        $response->success = true;
        $response->transaction = $transaction;

        return $response;
    }

    /**
     * Retrieves quote by reserved order id.
     *
     * @param string $reservedOrderId
     * @return Quote
     */
    private function getQuote(string $reservedOrderId): Quote
    {
        /** @var SearchCriteriaBuilder $searchCriteriaBuilder */
        $searchCriteriaBuilder = $this->objectManager->get(SearchCriteriaBuilder::class);
        $searchCriteria = $searchCriteriaBuilder->addFilter('reserved_order_id', $reservedOrderId)
            ->create();

        /** @var CartRepositoryInterface $quoteRepository */
        $quoteRepository = $this->objectManager->get(CartRepositoryInterface::class);
        $items = $quoteRepository->getList($searchCriteria)->getItems();

        return array_pop($items);
    }

    /**
     * Get list of orders by quote id.
     *
     * @param int $quoteId
     * @return array
     */
    private function getOrderList(int $quoteId): array
    {
        /** @var SearchCriteriaBuilder $searchCriteriaBuilder */
        $searchCriteriaBuilder = $this->objectManager->get(SearchCriteriaBuilder::class);
        $searchCriteria = $searchCriteriaBuilder->addFilter('quote_id', $quoteId)
            ->create();

        /** @var OrderRepositoryInterface $orderRepository */
        $orderRepository = $this->objectManager->get(OrderRepositoryInterface::class);
        return $orderRepository->getList($searchCriteria)->getItems();
    }

    /**
     * Returns GetPaymentNonceCommand command mock.
     *
     * @return MockObject
     */
    private function getPaymentNonceMock(): MockObject
    {
        $commandResult = $this->createMock(CommandResultInterface::class);
        $commandResult->method('get')
            ->willReturn(['paymentMethodNonce' => 'testNonce']);
        $paymentNonce = $this->createMock(GetPaymentNonceCommand::class);
        $paymentNonce->method('execute')
            ->willReturn($commandResult);

        return $paymentNonce;
    }
}
