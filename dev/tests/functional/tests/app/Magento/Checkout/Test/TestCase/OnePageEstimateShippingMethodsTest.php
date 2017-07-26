<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Test\TestCase;

use Magento\Mtf\TestCase\Scenario;

/**
 * Preconditions:
 * 1. Configure shipping method according dataset.
 * 2. Create product(-s)
 *
 * Steps:
 * 1. Go to Frontend.
 * 2. Add product(-s) to the cart.
 * 3. Proceed to Checkout.
 * 4. Fill shipping address according to dataset.
 * 5. Wait until shipping methods will appear.
 * 6. Perform assertions.
 *
 * @group One_Page_Checkout_(CS)
 * @ZephyrId MAGETWO-71002
 */
class OnePageEstimateShippingMethodsTest extends Scenario
{
    /**
     * Runs test.
     *
     * @return void
     */
    public function test()
    {
        $this->executeScenario();
    }
}
