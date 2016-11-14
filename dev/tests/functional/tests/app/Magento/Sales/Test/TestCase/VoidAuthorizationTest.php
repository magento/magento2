<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\TestCase;

use Magento\Mtf\TestCase\Scenario;

/**
 * Preconditions:
 * 1. Configure shipping method.
 * 2. Configure payment method.
 * 3. Create products.
 *
 * Steps:
 * 1. Go to Storefront.
 * 2. Add products to the cart.
 * 3. Click the 'Proceed to Checkout' button.
 * 4. Select checkout method according to dataset.
 * 5. Fill billing information and select the 'Ship to this address' option.
 * 6. Select shipping method.
 * 7. Select payment method.
 * 8. Place order.
 * 9. Open created order.
 * 10. Click 'Void' button.
 * 11. Perform assertions.
 *
 * @group Order_Management
 * @ZephyrId MAGETWO-39444
 */
class VoidAuthorizationTest extends Scenario
{
    /* tags */
    const MVP = 'yes';
    const TEST_TYPE = '3rd_party_test';
    /* end tags */

    /**
     * Void order authorization.
     *
     * @return void
     */
    public function test()
    {
        $this->executeScenario();
    }
}
