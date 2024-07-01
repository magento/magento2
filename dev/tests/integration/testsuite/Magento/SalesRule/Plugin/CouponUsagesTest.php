<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Plugin;

use Magento\Framework\DataObject;
use Magento\Framework\ObjectManagerInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteManagement;
use Magento\Quote\Model\SubmitQuoteValidator;
use Magento\Sales\Api\OrderManagementInterface;
use Magento\Sales\Model\Service\OrderService;
use Magento\SalesRule\Model\Coupon;
use Magento\SalesRule\Model\ResourceModel\Coupon\Usage;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\MessageQueue\EnvironmentPreconditionException;
use Magento\TestFramework\MessageQueue\PreconditionFailedException;
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
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->usage = $this->objectManager->get(Usage::class);
        $this->couponUsage = $this->objectManager->create(DataObject::class);
        $this->quoteManagement = $this->objectManager->get(QuoteManagement::class);
        $this->orderService = $this->objectManager->get(OrderService::class);

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
     * Test increasing coupon usages after after order placing and decreasing after order cancellation.
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
     * @dataProvider quoteSubmitFailureDataProvider
     */
    public function testQuoteSubmitFailure(array $mockObjects)
    {
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
    public function quoteSubmitFailureDataProvider(): array
    {
        /** @var OrderManagementInterface|MockObject $orderManagement */
        $orderManagement = $this->createMock(OrderManagementInterface::class);
        $orderManagement->expects($this->once())
            ->method('place')
            ->willThrowException(new \Exception());

        /** @var OrderManagementInterface|MockObject $orderManagement */
        $submitQuoteValidator = $this->createMock(SubmitQuoteValidator::class);
        $submitQuoteValidator->expects($this->once())
            ->method('validateQuote')
            ->willThrowException(new \Exception());

        return [
            'order placing failure' => [
                ['orderManagement' => $orderManagement]
            ],
            'quote validation failure' => [
                ['submitQuoteValidator' => $submitQuoteValidator]
            ],
        ];
    }
}
