<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\TestCase;

use Magento\Mtf\TestCase\Scenario;

/**
 * Preconditions:
 * 1. 20(21) products created.
 * 2. Customer is created.
 * 3. Customer placed the order with 20(21) products.
 *
 * Steps:
 * 1. Login to Storefront as Customer.
 * 2. Go to My Account > My Orders page
 * 3. Click 'View Order' link on order from preconditions
 *
 * @group Order_Management
 * @ZephyrId MAGETWO-63457
 */
class FrontendOrderPagerTest extends Scenario
{
    /* tags */
    const MVP = 'no';
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
