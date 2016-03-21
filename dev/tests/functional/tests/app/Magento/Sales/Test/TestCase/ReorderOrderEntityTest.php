<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\TestCase;

use Magento\Mtf\TestCase\Scenario;

/**
 * Preconditions:
 * 1. Create two products.
 * 2. Create a customer.
 * 3. Create order.
 *
 * Steps:
 * 1. Go to backend.
 * 2. Open Sales > Orders.
 * 3. Open the created order.
 * 4. Do 'Reorder' for placed order.
 * 5. Perform all assertions.
 *
 * @group Order_Management_(CS)
 * @ZephyrId MAGETWO-29007
 */
class ReorderOrderEntityTest extends Scenario
{
    /* tags */
    const MVP = 'yes';
    const DOMAIN = 'CS';
    /* end tags */

    /**
     * Reorder created order.
     *
     * @return void
     */
    public function test()
    {
        $this->executeScenario();
    }
}
