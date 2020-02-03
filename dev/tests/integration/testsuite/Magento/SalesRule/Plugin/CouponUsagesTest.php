<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SalesRule\Plugin;

use Magento\Framework\DataObject;
use Magento\Framework\ObjectManagerInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Service\OrderService;
use Magento\SalesRule\Model\Coupon;
use Magento\SalesRule\Model\ResourceModel\Coupon\Usage;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Test increasing coupon usages after after order placing and decreasing after order cancellation.
 *
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 */
class CouponUsagesTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var Coupon
     */
    private $coupon;

    /**
     * @var Usage
     */
    private $usage;

    /**
     * @var DataObject
     */
    private $couponUsage;

    /**
     * @var Order
     */
    private $order;

    /**
     * @var OrderService
     */
    private $orderService;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->coupon = $this->objectManager->get(Coupon::class);
        $this->usage = $this->objectManager->get(Usage::class);
        $this->couponUsage = $this->objectManager->get(DataObject::class);
        $this->order = $this->objectManager->get(Order::class);
        $this->orderService = $this->objectManager->get(OrderService::class);
    }

    /**
     * Test increasing coupon usages after after order placing and decreasing after order cancellation.
     *
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/SalesRule/_files/coupons_limited_order.php
     */
    public function testOrderCancellation()
    {
        $customerId = 1;
        $couponCode = 'one_usage';
        $orderId = '100000001';

        $this->coupon->loadByCode($couponCode);
        $this->order->loadByIncrementId($orderId);

        // Make sure coupon usages value is incremented then order is placed.
        $this->orderService->place($this->order);
        $this->usage->loadByCustomerCoupon($this->couponUsage, $customerId, $this->coupon->getId());
        $this->coupon->loadByCode($couponCode);

        self::assertEquals(
            1,
            $this->coupon->getTimesUsed()
        );
        self::assertEquals(
            1,
            $this->couponUsage->getTimesUsed()
        );

        // Make sure order coupon usages value is decremented then order is cancelled.
        $this->orderService->cancel($this->order->getId());
        $this->usage->loadByCustomerCoupon($this->couponUsage, $customerId, $this->coupon->getId());
        $this->coupon->loadByCode($couponCode);

        self::assertEquals(
            0,
            $this->coupon->getTimesUsed()
        );
        self::assertEquals(
            0,
            $this->couponUsage->getTimesUsed()
        );
    }
}
