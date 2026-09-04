<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
namespace Magento\SalesRule\Plugin;

use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Checkout\Test\Fixture\SetBillingAddress;
use Magento\Checkout\Test\Fixture\SetDeliveryMethod;
use Magento\Checkout\Test\Fixture\SetPaymentMethod;
use Magento\Checkout\Test\Fixture\SetShippingAddress;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Framework\DataObject;
use Magento\Framework\DB\Transaction;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\MessageQueue\ConsumerFactory;
use Magento\Framework\ObjectManagerInterface;
use Magento\Quote\Api\CouponManagementInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteManagement;
use Magento\Quote\Model\SubmitQuoteValidator;
use Magento\Quote\Test\Fixture\AddProductToCart;
use Magento\Quote\Test\Fixture\CustomerCart;
use Magento\Sales\Api\InvoiceManagementInterface;
use Magento\Sales\Api\OrderManagementInterface;
use Magento\Sales\Model\Service\OrderService;
use Magento\SalesRule\Model\Coupon;
use Magento\SalesRule\Model\ResourceModel\Coupon\Usage;
use Magento\SalesRule\Test\Fixture\Rule as SalesRuleFixture;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\MessageQueue\EnvironmentPreconditionException;
use Magento\TestFramework\MessageQueue\PreconditionFailedException;
use PHPUnit\Framework\Attributes\DataProvider;
use Magento\TestFramework\MessageQueue\PublisherConsumerController;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test increasing coupon usages after order placing and decreasing after order cancellation.
 *
 * @magentoAppArea frontend
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CouponUsagesTest extends TestCase
{
    /**
     * @var PublisherConsumerController
     */
    private $publisherConsumerController;

    /**
     * @var array
     */
    private $consumers = ['sales.rule.update.coupon.usage'];

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var Usage
     */
    private $usage;

    /**
     * @var DataObject
     */
    private $couponUsage;

    /**
     * @var QuoteManagement
     */
    private $quoteManagement;

    /**
     * @var OrderService
     */
    private $orderService;

    /**
     * @var DataFixtureStorage
     */
    private $fixtures;

    /**
     * @var CouponManagementInterface
     */
    private $couponManagement;

    /**
     * @var OrderManagementInterface
     */
    private $orderManagement;

    /**
     * @var ConsumerFactory
     */
    private $consumerFactory;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->usage = $this->objectManager->get(Usage::class);
        $this->couponUsage = $this->objectManager->create(DataObject::class);
        $this->quoteManagement = $this->objectManager->get(QuoteManagement::class);
        $this->orderService = $this->objectManager->get(OrderService::class);
        $this->fixtures = $this->objectManager->get(DataFixtureStorageManager::class)->getStorage();
        $this->couponManagement = $this->objectManager->get(CouponManagementInterface::class);
        $this->orderManagement = $this->objectManager->get(OrderManagementInterface::class);
        $this->consumerFactory = $this->objectManager->get(ConsumerFactory::class);

        $this->publisherConsumerController = Bootstrap::getObjectManager()->create(
            PublisherConsumerController::class,
            [
                'consumers' => $this->consumers,
                'logFilePath' => TESTS_TEMP_DIR . "/MessageQueueTestLog.txt",
                'maxMessages' => 100,
                'appInitParams' => Bootstrap::getInstance()->getAppInitParams()
            ]
        );
        try {
            $this->publisherConsumerController->startConsumers();
        } catch (EnvironmentPreconditionException $e) {
            $this->markTestSkipped($e->getMessage());
        } catch (PreconditionFailedException $e) {
            $this->fail(
                $e->getMessage()
            );
        }
        parent::setUp();
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        $this->publisherConsumerController->stopConsumers();
        parent::tearDown();
    }

    /**
     * Test increasing coupon usages after order placing and decreasing after order cancellation.
     *
     * @magentoDataFixture Magento/SalesRule/_files/coupons_limited_order.php
     * @magentoDbIsolation disabled
     */
    public function testSubmitQuoteAndCancelOrder()
    {
        $customerId = 1;
        $couponCode = 'one_usage';
        $reservedOrderId = 'test01';

        /** @var Coupon $coupon */
        $coupon = $this->objectManager->create(Coupon::class);
        $coupon->loadByCode($couponCode);
        /** @var Quote $quote */
        $quote = $this->objectManager->create(Quote::class);
        $quote->load($reservedOrderId, 'reserved_order_id');

        // Make sure coupon usages value is incremented then order is placed.
        $order = $this->quoteManagement->submit($quote);
        sleep(30); // timeout to processing Magento queue
        $this->usage->loadByCustomerCoupon($this->couponUsage, $customerId, $coupon->getId());
        $coupon->loadByCode($couponCode);

        self::assertEquals(
            1,
            $coupon->getTimesUsed()
        );
        self::assertEquals(
            1,
            $this->couponUsage->getTimesUsed()
        );

        // Make sure order coupon usages value is decremented then order is cancelled.
        $this->orderService->cancel($order->getId());
        $this->usage->loadByCustomerCoupon($this->couponUsage, $customerId, $coupon->getId());
        $coupon->loadByCode($couponCode);

        self::assertEquals(
            0,
            $coupon->getTimesUsed()
        );
        self::assertEquals(
            0,
            $this->couponUsage->getTimesUsed()
        );
    }

    /**
     * Test to decrement coupon usages after exception on order placing
     *
     * @param array $mockObjects
     * @magentoDataFixture Magento/SalesRule/_files/coupons_limited_order.php
     * @magentoDbIsolation disabled
     */
    #[DataProvider('quoteSubmitFailureDataProvider')]
    public function testQuoteSubmitFailure(array $mockObjects)
    {
        if (!empty($mockObjects['orderManagement'])) {
            $mockObjects['orderManagement'] = $mockObjects['orderManagement']($this);
        } elseif (!empty($mockObjects['submitQuoteValidator'])) {
            $mockObjects['submitQuoteValidator'] = $mockObjects['submitQuoteValidator']($this);
        }

        $customerId = 1;
        $couponCode = 'one_usage';
        $reservedOrderId = 'test01';

        /** @var Coupon $coupon */
        $coupon = $this->objectManager->get(Coupon::class);
        $coupon->loadByCode($couponCode);
        /** @var Quote $quote */
        $quote = $this->objectManager->get(Quote::class);
        $quote->load($reservedOrderId, 'reserved_order_id');

        /** @var QuoteManagement $quoteManagement */
        $quoteManagement = $this->objectManager->create(
            QuoteManagement::class,
            $mockObjects
        );

        try {
            $quoteManagement->submit($quote);
        } catch (\Exception $exception) {
            sleep(30); // timeout to processing queue
            $this->usage->loadByCustomerCoupon($this->couponUsage, $customerId, $coupon->getId());
            $coupon->loadByCode($couponCode);
            self::assertEquals(
                0,
                $coupon->getTimesUsed()
            );
            self::assertEquals(
                0,
                $this->couponUsage->getTimesUsed()
            );
        }
    }

    /**
     * @return array
     */
    public static function quoteSubmitFailureDataProvider(): array
    {
        $orderManagement = static function (self $testCase) {
            $mock = $testCase->createMock(OrderManagementInterface::class);
            $mock->expects($testCase->once())
                ->method('place')
                ->willThrowException(new \Exception());
            return $mock;
        };

        $submitQuoteValidator = static function (self $testCase) {
            $mock = $testCase->createMock(SubmitQuoteValidator::class);
            $mock->expects($testCase->once())
                ->method('validateQuote')
                ->willThrowException(new \Exception());
            return $mock;
        };

        return [
            'order placing failure' => [
                ['orderManagement' => $orderManagement]
            ],
            'quote validation failure' => [
                ['submitQuoteValidator' => $submitQuoteValidator]
            ],
        ];
    }

    /**
     * Test that coupon usage is NOT decremented when order is partially invoiced and then cancelled
     *
     * @magentoDbIsolation disabled
     * @throws LocalizedException
     */
    #[
        DataFixture(ProductFixture::class, ['price' => 10, 'sku' => 'simple-product-1'], 'product1'),
        DataFixture(ProductFixture::class, ['price' => 20, 'sku' => 'simple-product-2'], 'product2'),
        DataFixture(
            SalesRuleFixture::class,
            [
                'coupon_code' => 'test_once_usage',
                'simple_action' => 'by_percent',
                'discount_amount' => 10,
                'uses_per_coupon' => 1,
                'uses_per_customer' => 1
            ],
            'salesrule'
        ),
        DataFixture(Customer::class, as: 'customer'),
        DataFixture(CustomerCart::class, ['customer_id' => '$customer.id$'], 'cart'),
        DataFixture(AddProductToCart::class, ['cart_id' => '$cart.id$', 'product_id' => '$product1.id$', 'qty' => 2]),
        DataFixture(AddProductToCart::class, ['cart_id' => '$cart.id$', 'product_id' => '$product2.id$', 'qty' => 1]),
        DataFixture(SetBillingAddress::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetShippingAddress::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetDeliveryMethod::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetPaymentMethod::class, ['cart_id' => '$cart.id$']),
    ]
    public function testCancelOrderAfterPartialInvoice()
    {
        $couponCode = 'test_once_usage';
        $cart = $this->fixtures->get('cart');
        $customer = $this->fixtures->get('customer');
        $customerId = $customer->getId();
        $this->couponManagement->set($cart->getId(), $couponCode);

        /** @var Coupon $coupon */
        $coupon = $this->objectManager->create(Coupon::class);
        $coupon->loadByCode($couponCode);

        $orderId = $this->quoteManagement->placeOrder($cart->getId());
        $orderRepository = $this->objectManager->get(\Magento\Sales\Api\OrderRepositoryInterface::class);
        $order = $orderRepository->get($orderId);
        sleep(30);
        $this->usage->loadByCustomerCoupon($this->couponUsage, $customerId, $coupon->getId());
        $coupon->loadByCode($couponCode);
        self::assertEquals(1, $coupon->getTimesUsed());
        self::assertEquals(1, $this->couponUsage->getTimesUsed());

        /** @var InvoiceManagementInterface $invoiceService */
        $invoiceService = $this->objectManager->get(InvoiceManagementInterface::class);
        $orderItems = $order->getAllItems();
        $firstItem = reset($orderItems);
        $invoiceItems = [$firstItem->getId() => $firstItem->getQtyOrdered()];
        $invoice = $invoiceService->prepareInvoice($order, $invoiceItems);
        $invoice->register();
        /** @var Transaction $transactionSave */
        $transactionSave = $this->objectManager->create(Transaction::class);
        $transactionSave->addObject($invoice)
            ->addObject($order)
            ->save();
        self::assertGreaterThan(
            0,
            abs($invoice->getDiscountAmount()),
            'Invoice should have discount amount applied'
        );
        $order = $orderRepository->get($orderId);
        self::assertGreaterThan(
            0,
            abs($order->getDiscountInvoiced()),
            'Order should have invoiced discount amount'
        );

        $this->orderManagement->cancel($orderId);
        sleep(30);
        $this->usage->loadByCustomerCoupon($this->couponUsage, $customerId, $coupon->getId());
        $coupon->loadByCode($couponCode);
        self::assertEquals(1, $coupon->getTimesUsed());
        self::assertEquals(1, $this->couponUsage->getTimesUsed());
    }
}
