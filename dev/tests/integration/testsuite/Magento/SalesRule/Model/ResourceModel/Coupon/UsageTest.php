<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SalesRule\Model\ResourceModel\Coupon;

use Magento\Framework\DataObject;
use Magento\Framework\ObjectManagerInterface;
use Magento\SalesRule\Model\Coupon;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Tests accounting of coupon usages.
 *
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 */
class UsageTest extends \PHPUnit\Framework\TestCase
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
     * Tests incrementing and decrementing of coupon usages.
     *
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/SalesRule/_files/coupons_limited.php
     */
    public function testUpdateCustomerCouponTimesUsed()
    {
        $customerId = 1;
        $couponCode = 'one_usage';

        $this->coupon->loadByCode($couponCode);

        $testCases = [
            ['increment' => true, 'expected' => 1],
            ['increment' => false, 'expected' => 0],
            ['increment' => false, 'expected' => 0],
            ['increment' => true, 'expected' => 1],
            ['increment' => true, 'expected' => 2],
        ];

        foreach ($testCases as $testCase) {
            $this->usage->updateCustomerCouponTimesUsed($customerId, $this->coupon->getId(), $testCase['increment']);
            $this->usage->loadByCustomerCoupon($this->couponUsage, $customerId, $this->coupon->getId());

            self::assertEquals(
                $testCase['expected'],
                $this->couponUsage->getTimesUsed()
            );
        }
    }

    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->coupon = $this->objectManager->get(Coupon::class);
        $this->usage = $this->objectManager->get(Usage::class);
        $this->couponUsage = $this->objectManager->get(DataObject::class);
    }
}
