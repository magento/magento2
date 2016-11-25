<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Test\TestCase;

use Magento\Mtf\TestCase\Scenario;

/**
 * Preconditions:
 * 1. Configure payment method.
 * 2. Create products.
 *
 * Steps:
 * 1. Log in Storefront.
 * 2. Add products to the Shopping Cart.
 * 3. Click the 'Proceed to Checkout' button.
 * 4. Fill shipping information.
 * 5. Select shipping method.
 * 6. Select payment method.
 * 7. Click 'Place Order' button.
 * 8. Perform assertions.
 *
 * @group Checkout
 * @ZephyrId MAGETWO-46469
 */
class OnePageCheckoutDeclinedTest extends Scenario
{
    /* tags */
    const MVP = 'yes';
    const TEST_TYPE = '3rd_party_test';
    const SEVERITY = 'S1';
    /* end tags */

    /**
     * Verifies error message on Onepage Checkout.
     *
     * @return void
     */
    public function test()
    {
        $this->executeScenario();
    }
}
