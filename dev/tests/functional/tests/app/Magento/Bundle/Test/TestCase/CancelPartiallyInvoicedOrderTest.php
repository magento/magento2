<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Bundle\Test\TestCase;

use Magento\Mtf\TestCase\Scenario;

/**
 * Preconditions:
 * 1. Enable payment method: "Transfer/Cash on Delivery/Purchase Order/Zero Subtotal Checkout".
 * 2. Enable shipping method one of "Flat Rate.
 * 3. Create order.
 *
 * Steps:
 * 1. Login to backend.
 * 2. Sales > Orders.
 * 3. Open the created order.
 * 4. Create partial invoice
 * 4. Do cancel Order.
 * 5. Perform all assetions.
 *
 * @group Order_Management
 * @ZephyrId MAGETWO-67787
 */
class CancelPartiallyInvoicedOrderTest extends Scenario
{
    /**
     * Runs test for invoice creation for order placed with offline payment method.
     *
     * @return void
     */
    public function test()
    {
        $this->executeScenario();
    }
}
