<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Braintree;

use Braintree\Result\Error;
use Braintree\Result\Successful;
use Braintree\Transaction;
use Braintree\Transaction\CreditCardDetails;
use Magento\Braintree\Gateway\Http\Client\TransactionSale;
use Magento\Braintree\Model\Ui\ConfigProvider;
use Magento\Checkout\Api\PaymentInformationManagementInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\State;
use Magento\Framework\App\Area;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Sales\Api\Data\TransactionInterface;
use Magento\Sales\Api\TransactionRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PaymentInformationManagementTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var TransactionSale|MockObject
     */
    private $client;

    /**
     * @var PaymentInformationManagementInterface
     */
    private $management;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();

        $this->client = $this->getMockBuilder(TransactionSale::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->objectManager->addSharedInstance($this->client, TransactionSale::class);
        $this->management = $this->objectManager->get(PaymentInformationManagementInterface::class);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown()
    {
        $this->objectManager->removeSharedInstance(TransactionSale::class);
        parent::tearDown();
    }

    /**
     * Checks a case when payment method triggers an error during place order flow and
     * error messages from payment gateway should be mapped.
     * Error messages might be specific for different areas.
     *
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/Checkout/_files/quote_with_shipping_method.php
     * @magentoConfigFixture current_store payment/braintree/active 1
     * @dataProvider getErrorPerAreaDataProvider
     * @expectedException \Magento\Framework\Exception\CouldNotSaveException
     * @param string $area
     * @param array $testErrorCodes
     * @param string $expectedOutput
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function testSavePaymentInformationAndPlaceOrderWithErrors(
        string $area,
        array $testErrorCodes,
        string $expectedOutput
    ) {
        /** @var State $state */
        $state = $this->objectManager->get(State::class);
        $state->setAreaCode($area);

        $quote = $this->getQuote('test_order_1');
        $payment = $this->getPayment();

        $errors = ['errors' => []];

        foreach ($testErrorCodes as $testErrorCode) {
            array_push($errors['errors'], ['code' => $testErrorCode]);
        }

        $response = new Error(['errors' => $errors, 'transaction' => ['status' => 'declined']]);

        $this->client->method('placeRequest')
            ->willReturn(['object' => $response]);

        $this->expectExceptionMessage($expectedOutput);

        $this->management->savePaymentInformationAndPlaceOrder(
            $quote->getId(),
            $payment
        );
    }

    /**
     * Gets list of areas with specific error messages.
     *
     * @return array
     */
    public function getErrorPerAreaDataProvider()
    {
        $testErrorGlobal = ['code' => 81802, 'message' => 'Company is too long.'];
        $testErrorAdmin = ['code' => 91511, 'message' => 'Customer does not have any credit cards.'];
        $testErrorFake = ['code' => 'fake_code', 'message' => 'Error message should not be mapped.'];

        return [
            [
                Area::AREA_FRONTEND,
                [$testErrorAdmin['code'], $testErrorFake['code']],
                'Transaction has been declined. Please try again later.'
            ], [
                Area::AREA_FRONTEND,
                [$testErrorGlobal['code'], $testErrorAdmin['code'], $testErrorFake['code']],
                $testErrorGlobal['message']
            ], [
                Area::AREA_ADMINHTML,
                [$testErrorGlobal['code'], $testErrorAdmin['code'], $testErrorFake['code']],
                $testErrorGlobal['message'] . PHP_EOL . $testErrorAdmin['message']
            ],
        ];
    }

    /**
     * Checks a case when order should be placed with "Sale" payment action.
     *
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/Checkout/_files/quote_with_shipping_method.php
     * @magentoConfigFixture current_store payment/braintree/active 1
     * @magentoConfigFixture current_store payment/braintree/payment_action authorize_capture
     */
    public function testPlaceOrderWithSaleAction()
    {
        $response = $this->getSuccessfulResponse(Transaction::SUBMITTED_FOR_SETTLEMENT);
        $this->client->method('placeRequest')
            ->willReturn($response);

        $quote = $this->getQuote('test_order_1');
        $payment = $this->getPayment();

        $orderId = $this->management->savePaymentInformationAndPlaceOrder($quote->getId(), $payment);
        self::assertNotEmpty($orderId);

        $transactions = $this->getPaymentTransactionList((int) $orderId);
        self::assertEquals(1, sizeof($transactions), 'Only one transaction should be present.');

        /** @var TransactionInterface $transaction */
        $transaction = array_pop($transactions);
        self::assertEquals(
            'capture',
            $transaction->getTxnType(),
            'Order should contain only the "capture" transaction.'
        );
        self::assertFalse((bool) $transaction->getIsClosed(), 'Transaction should not be closed.');
    }

    /**
     * Checks a case when order should be placed with "Authorize" payment action.
     *
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/Checkout/_files/quote_with_shipping_method.php
     * @magentoConfigFixture current_store payment/braintree/active 1
     * @magentoConfigFixture current_store payment/braintree/payment_action authorize
     */
    public function testPlaceOrderWithAuthorizeAction()
    {
        $response = $this->getSuccessfulResponse(Transaction::AUTHORIZED);
        $this->client->method('placeRequest')
            ->willReturn($response);

        $quote = $this->getQuote('test_order_1');
        $payment = $this->getPayment();

        $orderId = $this->management->savePaymentInformationAndPlaceOrder($quote->getId(), $payment);
        self::assertNotEmpty($orderId);

        $transactions = $this->getPaymentTransactionList((int) $orderId);
        self::assertEquals(1, sizeof($transactions), 'Only one transaction should be present.');

        /** @var TransactionInterface $transaction */
        $transaction = array_pop($transactions);
        self::assertEquals(
            'authorization',
            $transaction->getTxnType(),
            'Order should contain only the "authorization" transaction.'
        );
        self::assertFalse((bool) $transaction->getIsClosed(), 'Transaction should not be closed.');
    }

    /**
     * Retrieves quote by provided order ID.
     *
     * @param string $reservedOrderId
     * @return CartInterface
     */
    private function getQuote(string $reservedOrderId): CartInterface
    {
        /** @var SearchCriteriaBuilder $searchCriteriaBuilder */
        $searchCriteriaBuilder = $this->objectManager->get(SearchCriteriaBuilder::class);
        $searchCriteria = $searchCriteriaBuilder->addFilter('reserved_order_id', $reservedOrderId)
            ->create();

        /** @var CartRepositoryInterface $quoteRepository */
        $quoteRepository = $this->objectManager->get(CartRepositoryInterface::class);
        $items = $quoteRepository->getList($searchCriteria)
            ->getItems();

        return array_pop($items);
    }

    /**
     * Creates Braintree payment method.
     *
     * @return PaymentInterface
     */
    private function getPayment(): PaymentInterface
    {
        /** @var PaymentInterface $payment */
        $payment = $this->objectManager->create(PaymentInterface::class);
        $payment->setMethod(ConfigProvider::CODE);

        return $payment;
    }

    /**
     * Get list of order transactions.
     *
     * @param int $orderId
     * @return TransactionInterface[]
     */
    private function getPaymentTransactionList(int $orderId): array
    {
        /** @var SearchCriteriaBuilder $searchCriteriaBuilder */
        $searchCriteriaBuilder = $this->objectManager->get(SearchCriteriaBuilder::class);
        $searchCriteria = $searchCriteriaBuilder->addFilter('order_id', $orderId)
            ->create();

        /** @var TransactionRepositoryInterface $transactionRepository */
        $transactionRepository = $this->objectManager->get(TransactionRepositoryInterface::class);
        return $transactionRepository->getList($searchCriteria)
            ->getItems();
    }

    /**
     * Returns successful Braintree response.
     *
     * @param string $transactionStatus
     * @return array
     */
    private function getSuccessfulResponse(string $transactionStatus): array
    {
        $successResponse = new Successful();
        $successResponse->success = true;
        $successResponse->transaction = $this->getBraintreeTransaction($transactionStatus);

        $response = [
            'object' => $successResponse,
        ];

        return $response;
    }

    /**
     * Returns Braintree transaction.
     *
     * @param string $transactionStatus
     * @return Transaction
     */
    private function getBraintreeTransaction(string $transactionStatus)
    {
        $cardData = [
            'token' => '73nrjn',
            'bin' => '411111',
            'cardType' => 'Visa',
            'expirationMonth' => '12',
            'expirationYear' => '2025',
            'last4' => '1111'
        ];

        $transactionData = [
            'id' => 'c0n6gvjb',
            'status' => $transactionStatus,
            'creditCard' => $cardData,
            'creditCardDetails' => new CreditCardDetails($cardData)
        ];

        $transaction = Transaction::factory($transactionData);

        return $transaction;
    }
}
