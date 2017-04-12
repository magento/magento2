<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\TestCase;

use Magento\Mtf\TestCase\Scenario;

/**
 * Preconditions:
 * 1. Enable payment method one of "Check/Money Order/Bank Transfer/Cash on Delivery/Purchase Order".
 * 2. Enable shipping method one of "Flat Rate/Free Shipping".
 * 3. Create order.
 * 4. Create Invoice.
 *
 * Steps:
 * 1. Go to Sales > Orders > find out placed order and open.
 * 2. Click 'Credit Memo' button.
 * 3. Fill data from dataset.
 * 4. On order's page click 'Refund offline' button.
 * 5. Perform all assertions.
 *
 * @group Order_Management
 * @ZephyrId MAGETWO-29116
 */
class CreateCreditMemoEntityTest extends Scenario
{
    /* tags */
    const MVP = 'yes';
    /* end tags */

    /**
     * Runs test for credit memo creation for order placed with offline payment method.
     *
     * @return void
     */
    public function test()
    {
        $this->executeScenario();
    }
}
