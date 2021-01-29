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
use Magento\Sales\Api\OrderManagementInterface;
use Magento\Sales\Model\Service\OrderService;
use Magento\SalesRule\Model\Coupon;
use Magento\SalesRule\Model\ResourceModel\Coupon\Usage;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test increasing coupon usages after order placing and decreasing after order cancellation.
 *
 * @magentoAppArea frontend
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 */
class CouponUsagesTest extends TestCase
{
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
        $this->couponUsage = $this->objectManager->get(DataObject::class);
        $this->quoteManagement = $this->objectManager->get(QuoteManagement::class);
        $this->orderService = $this->objectManager->get(OrderService::class);
    }

    /**
     * Test increasing coupon usages after after order placing and decreasing after order cancellation.
     *
     * @magentoDataFixture Magento/SalesRule/_files/coupons_limited_order.php
     */
    public function testSubmitQuoteAndCancelOrder()
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

        // Make sure coupon usages value is incremented then order is placed.
        $order = $this->quoteManagement->submit($quote);
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
     * @magentoDataFixture Magento/SalesRule/_files/coupons_limited_order.php
     */
    public function testSubmitQuoteWithError()
    {
        $customerId = 1;
        $couponCode = 'one_usage';
        $reservedOrderId = 'test01';
        $exceptionMessage = 'Some test exception';

        /** @var Coupon $coupon */
        $coupon = $this->objectManager->get(Coupon::class);
        $coupon->loadByCode($couponCode);
        /** @var Quote $quote */
        $quote = $this->objectManager->get(Quote::class);
        $quote->load($reservedOrderId, 'reserved_order_id');

        /** @var OrderManagementInterface|MockObject $orderManagement */
        $orderManagement = $this->getMockForAbstractClass(OrderManagementInterface::class);
        $orderManagement->expects($this->once())
            ->method('place')
            ->willThrowException(new \Exception($exceptionMessage));

        /** @var QuoteManagement $quoteManagement */
        $quoteManagement = $this->objectManager->create(
            QuoteManagement::class,
            ['orderManagement' => $orderManagement]
        );

        try {
            $quoteManagement->submit($quote);
        } catch (\Exception $exception) {
            $this->assertEquals($exceptionMessage, $exception->getMessage());

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
}
