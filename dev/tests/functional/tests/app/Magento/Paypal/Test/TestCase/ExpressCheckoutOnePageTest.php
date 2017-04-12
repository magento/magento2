<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Paypal\Test\TestCase;

use Magento\Mtf\TestCase\Scenario;

/**
 * Preconditions:
 * 1. Create PayPal Customer.
 * 2. Create products.
 * 3. Apply configuration for test.
 *
 * Steps:
 * 1. Go to Frontend.
 * 2. Add products to the cart.
 * 3. Proceed to checkout with necessary checkout method.
 * 4. Select Billing Address.
 * 5. Select Shipping Address.
 * 6. Select shipping method.
 * 3. Select Express Checkout as payment method.
 * 4. Login to PayPal.
 * 5. Process checkout via PayPal.
 * 6. Perform asserts.
 *
 * @group PayPal
 * @ZephyrId MAGETWO-12413, MAGETWO-14359, MAGETWO-12996
 */
class ExpressCheckoutOnePageTest extends Scenario
{
    /* tags */
    const MVP = 'yes';
    const TEST_TYPE = '3rd_party_test';
    const TO_MAINTAIN = 'yes';
    const SEVERITY = 'S0';
    /* end tags */

    /**
     * Runs one page checkout test.
     *
     * @return void
     */
    public function test()
    {
        $this->executeScenario();
    }
}
