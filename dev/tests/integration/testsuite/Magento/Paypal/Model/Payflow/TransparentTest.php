<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Paypal\Model\Payflow;

use Laminas\Http\Response;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Checkout\Api\PaymentInformationManagementInterface;
use Magento\Checkout\Test\Fixture\SetBillingAddress as SetBillingAddressFixture;
use Magento\Checkout\Test\Fixture\SetDeliveryMethod as SetDeliveryMethodFixture;
use Magento\Checkout\Test\Fixture\SetGuestEmail as SetGuestEmailFixture;
use Magento\Checkout\Test\Fixture\SetPaymentMethod as SetPaymentMethodFixture;
use Magento\Checkout\Test\Fixture\SetShippingAddress as SetShippingAddressFixture;
use Magento\Customer\Test\Fixture\Customer as CustomerFixture;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\HTTP\LaminasClient;
use Magento\Framework\HTTP\LaminasClientFactory;
use Magento\Payment\Model\MethodInterface;
use Magento\Paypal\Model\Config;
use Magento\Paypal\Model\Payflow\Service\Gateway;
use Magento\Paypal\Model\Payflowpro;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Test\Fixture\AddProductToCart as AddProductToCartFixture;
use Magento\Quote\Test\Fixture\CustomerCart as CustomerCartFixture;
use Magento\Sales\Api\Data\TransactionInterface;
use Magento\Sales\Api\OrderManagementInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\TransactionRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Test\Fixture\Store as StoreFixture;
use Magento\TestFramework\Fixture\Config as ConfigFixture;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use Magento\Vault\Model\Method\Vault;
use Magento\Vault\Test\Fixture\PaymentToken as PaymentTokenFixture;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class TransparentTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var PaymentInformationManagementInterface
     */
    private $management;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->management = $this->objectManager->get(PaymentInformationManagementInterface::class);
    }

    /**
     * Checks a case when order should be placed in "Suspected Fraud" status based on account verification.
     *
     * @magentoDataFixture Magento/Checkout/_files/quote_with_shipping_method.php
     * @magentoConfigFixture current_store payment/payflowpro/active 1
     * @magentoConfigFixture current_store payment/payflowpro/payment_action Authorization
     * @magentoConfigFixture current_store payment/payflowpro/fmf 1
     */
    public function testPlaceOrderSuspectedFraud(): void
    {
        $quote = $this->getQuote('test_order_1');
        $this->addFraudPayment($quote);
        $payment = $quote->getPayment();
        $pnref = $payment->getAdditionalInformation(Payflowpro::PNREF);

        $orderId = (int)$this->management->savePaymentInformationAndPlaceOrder($quote->getId(), $payment);
        self::assertNotEmpty($orderId);

        /** @var OrderRepositoryInterface $orderManagement */
        $orderManagement = $this->objectManager->get(OrderRepositoryInterface::class);
        $order = $orderManagement->get($orderId);

        self::assertEquals(Order::STATUS_FRAUD, $order->getStatus());
        self::assertEquals(Order::STATE_PAYMENT_REVIEW, $order->getState());

        $transactions = $this->getPaymentTransactionList((int) $orderId);
        self::assertCount(1, $transactions, 'Only one transaction should be present.');

        /** @var TransactionInterface $transaction */
        $transaction = array_pop($transactions);
        self::assertEquals(
            $pnref,
            $transaction->getTxnId(),
            'Authorization transaction id should be equal to PNREF.'
        );

        self::assertStringContainsString(
            'Order is suspended as an account verification transaction is suspected to be fraudulent.',
            $this->getOrderComment($orderId)
        );
    }

    #[
        DbIsolation(false),
        DataFixture(StoreFixture::class, ['code' => 'test_vault_store'], as: 'store2'),
        ConfigFixture('payment/payflowpro/active', '1', ScopeInterface::SCOPE_STORE, 'default'),
        ConfigFixture('payment/payflowpro/cctypes', '1', ScopeInterface::SCOPE_STORE, 'default'),
        ConfigFixture('payment/payflowpro/payment_action', 'Authorization', ScopeInterface::SCOPE_STORE, 'default'),
        ConfigFixture('payment/payflowpro/allowspecific', '1', ScopeInterface::SCOPE_STORE, 'default'),
        ConfigFixture('payment/payflowpro/specificcountry', 'US', ScopeInterface::SCOPE_STORE, 'default'),
        ConfigFixture('payment/payflowpro_cc_vault/active', '1', ScopeInterface::SCOPE_STORE, 'default'),
        // second store config
        ConfigFixture('payment/payflowpro/active', '1', ScopeInterface::SCOPE_STORE, 'test_vault_store'),
        ConfigFixture('payment/payflowpro/cctypes', '1', ScopeInterface::SCOPE_STORE, 'test_vault_store'),
        ConfigFixture('payment/payflowpro/payment_action', 'Sale', ScopeInterface::SCOPE_STORE, 'test_vault_store'),
        ConfigFixture('payment/payflowpro/allowspecific', '1', ScopeInterface::SCOPE_STORE, 'test_vault_store'),
        ConfigFixture('payment/payflowpro/specificcountry', 'UK', ScopeInterface::SCOPE_STORE, 'test_vault_store'),
        ConfigFixture('payment/payflowpro_cc_vault/active', '1', ScopeInterface::SCOPE_STORE, 'test_vault_store'),

        DataFixture(CustomerFixture::class, as: 'customer'),
        DataFixture(CustomerCartFixture::class, ['customer_id' => '$customer.id$'], as: 'cart'),
        DataFixture(ProductFixture::class, as:'product'),
        DataFixture(AddProductToCartFixture::class, ['cart_id' => '$cart.id$', 'product_id' => '$product.id$']),
        DataFixture(SetBillingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetShippingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetGuestEmailFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetDeliveryMethodFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(
            PaymentTokenFixture::class,
            ['customer_id' => '$customer.id$', 'payment_method_code' => 'payflowpro'],
            as: 'token'
        ),
        DataFixture(
            SetPaymentMethodFixture::class,
            [
                'cart_id' => '$cart.id$',
                'method' => [
                    'method' => 'payflowpro_cc_vault',
                    'additional_data' => [
                        'public_hash' => '$token.public_hash$',
                        'customer_id' => '$customer.id$',
                    ]
                ]
            ]
        ),
    ]
    public function testStoreConfiguration(): void
    {
        $fixtures = DataFixtureStorageManager::getStorage();
        $cartRepository = $this->objectManager->get(CartRepositoryInterface::class);
        $cart = $fixtures->get('cart');
        $store = $fixtures->get('store2');
        $cart = $cartRepository->get($cart->getId());
        $cart->setStoreId($store->getId());
        $this->assertNotEmpty($cart->getPayment()->getMethod());
        $this->assertEquals('payflowpro_cc_vault', $cart->getPayment()->getMethod());
        $paymentMethodInstance = $cart->getPayment()->getMethodInstance();
        $this->assertInstanceOf(Vault::class, $paymentMethodInstance);
        $this->assertTrue(
            $paymentMethodInstance->canUseForCountry('UK')
        );
        $this->assertEquals(
            MethodInterface::ACTION_AUTHORIZE_CAPTURE,
            $paymentMethodInstance->getConfigPaymentAction()
        );
        $this->mockPaymentGateway($cart);
        $cartManagement = $this->objectManager->create(CartManagementInterface::class);
        $orderId = $cartManagement->placeOrder($cart->getId());
        $transactions = $this->getPaymentTransactionList((int) $orderId);
        $this->assertCount(1, $transactions);
        $transaction = array_pop($transactions);
        $this->assertEquals(TransactionInterface::TYPE_CAPTURE, $transaction->getTxnType());
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
     * Sets payment with fraud to quote.
     *
     * @return void
     */
    private function addFraudPayment(CartInterface $quote): void
    {
        $payment = $quote->getPayment();
        $payment->setMethod(Config::METHOD_PAYFLOWPRO);
        $payment->setAdditionalInformation(Payflowpro::PNREF, 'A90A0D1B361D');
        $payment->setAdditionalInformation('result_code', Payflowpro::RESPONSE_CODE_FRAUDSERVICE_FILTER);
        $payment->setCcType('VI');
        $payment->setCcLast4('1111');
        $payment->setCcExpMonth('3');
        $payment->setCcExpYear('2025');

        /** @var CartRepositoryInterface $quoteRepository */
        $quoteRepository = $this->objectManager->get(CartRepositoryInterface::class);
        $quoteRepository->save($quote);
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
     * Returns order comment.
     *
     * @param int $orderId
     * @return string
     */
    private function getOrderComment(int $orderId): string
    {
        /** @var OrderManagementInterface $orderManagement */
        $orderManagement = $this->objectManager->get(OrderManagementInterface::class);
        $comments = $orderManagement->getCommentsList($orderId)->getItems();
        $comment = reset($comments);

        return $comment ? $comment->getComment() : '';
    }

    private function mockPaymentGateway(CartInterface $cart): Gateway
    {
        $responseArray = [
            'RESULT' => '0',
            'pnref' => 'A30A3E5DDE34',
            'respmsg' => 'Approved',
            'authcode' => '421PNI',
            'avsaddr' => 'N',
            'avszip' => 'N',
            'txid' => '011144228158206',
            'hostcode' => 'A',
            'procavs' => 'N',
            'visacardlevel' => '12',
            'transtime' => date('Y-m-d H:i:s'),
            'firstname' => $cart->getBillingAddress()->getFirstname(),
            'lastname' => $cart->getBillingAddress()->getLastname(),
            'amt' => $cart->getShippingAddress()->getGrandTotal(),
            'acct' => '1234',
            'expdate' => date('Y-m-d H:i:s', strtotime('+1 hour')),
            'cardtype' => '0',
            'iavs' => 'N',
            'result_code' => '0',
        ];
        $clientFactory = $this->createMock(LaminasClientFactory::class);
        $client = $this->createMock(LaminasClient::class);
        $clientResponse = $this->createMock(Response::class);
        $clientFactory->method('create')->willReturn($client);
        $client->method('send')->willReturn($clientResponse);
        $clientResponse->method('getBody')->willReturn(http_build_query($responseArray));
        $gatewayMock = $this->objectManager->get(Gateway::class);
        $reflection = new \ReflectionClass($gatewayMock);
        $property = $reflection->getProperty('httpClientFactory');
        $property->setValue($gatewayMock, $clientFactory);
        
        return $gatewayMock;
    }
}
