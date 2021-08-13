<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesRule\Model\ResourceModel\Report;

use Magento\Sales\Model\Order;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Test for salesrule report model
 */
class RuleTest extends TestCase
{
    /**
     * @magentoDataFixture Magento/SalesRule/_files/order_with_coupon.php
     */
    public function testGetUniqRulesNamesList()
    {
        $ruleName = uniqid('cart_price_rule_');
        $orderIncrementId = '100000001';
        $objectManager = Bootstrap::getObjectManager();
        /** @var Order $order */
        $order = $objectManager->create(Order::class);
        $order->loadByIncrementId($orderIncrementId)
            ->setCouponRuleName($ruleName)
            ->save();
        /** @var Rule $reportResource */
        $reportResource = $objectManager->create(Rule::class);
        $reportResource->aggregate();
        $this->assertContains($ruleName, $reportResource->getUniqRulesNamesList());
    }
}
