<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Test\TestCase;

use Magento\Mtf\TestCase\Scenario;

/**
 * Preconditions:
 * 1. Configure shipping method.
 * 2. Configure payment method.
 * 3. Create products.
 *
 * Steps:
 * 1. Go to Frontend.
 * 2. Add products to the cart.
 * 3. Click the 'Proceed to Checkout' button on mini shopping cart.
 * 4. Select checkout method according to dataset.
 * 5. Fill billing information and select the 'Ship to this address' option.
 * 6. Select shipping method.
 * 7. Select payment method (use reward points and store credit if available).
 * 8. Verify order total on review step.
 * 9. Click 'Place Order' button.
 * 10. Perform assertions.
 *
 * @group One_Page_Checkout
 * @ZephyrId MAGETWO-38400
 */
class OnePageCheckoutFromMiniShoppingCartDeclinedTest extends Scenario
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
