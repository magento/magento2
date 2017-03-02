<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Shipping\Test\TestCase;

use Magento\Mtf\TestCase\Scenario;

/**
 * Preconditions:
 * 1. Configure shipping method.
 * 2. Create products.
 * 3. Create and setup customer.
 *
 * Steps:
 * 1. Go to Frontend.
 * 2. Add products to the cart.
 * 3. Click the 'Proceed to Checkout' button.
 * 4. Fill shipping information.
 * 5. Perform assertions.
 *
 * @group Shipping
 * @ZephyrId MAGETWO-56124
 */
class CityBasedShippingRateTest extends Scenario
{
    /* tags */
    const MVP = 'yes';
    const TEST_TYPE = '3rd_party_test';
    const SEVERITY = 'S1';
    /* end tags */

    /**
     * Runs City Based Shipping Rate test.
     *
     * @return void
     */
    public function test()
    {
        $this->executeScenario();
    }
}
