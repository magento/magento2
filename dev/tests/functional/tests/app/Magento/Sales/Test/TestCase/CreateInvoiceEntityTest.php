<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\TestCase;

use Magento\Mtf\TestCase\Scenario;

/**
 * Preconditions:
 * 1. Enable payment method: "Check/Money Order/Bank Transfer/Cash on Delivery/Purchase Order/Zero Subtotal Checkout".
 * 2. Enable shipping method one of "Flat Rate/Free Shipping".
 * 3. Create order.
 *
 * Steps:
 * 1. Go to Sales > Orders.
 * 2. Select created order in the grid and open it.
 * 3. Click 'Invoice' button.
 * 4. Fill data according to dataset.
 * 5. Click 'Submit Invoice' button.
 * 6. Perform assertions.
 *
 * @group Order_Management
 * @ZephyrId MAGETWO-28209
 */
class CreateInvoiceEntityTest extends Scenario
{
    /* tags */
    const MVP = 'yes';
    /* end tags */

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
