<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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
 * 3. Click "Checkout with PayPal" button.
 * 4. Login to PayPal.
 * 5. Process checkout via PayPal.
 * 6. Perform asserts.
 *
 * @group PayPal
 * @ZephyrId MAGETWO-12414
 */
class ExpressCheckoutFromShoppingCartTest extends Scenario
{
    /* tags */
    const MVP = 'yes';
    const TEST_TYPE = '3rd_party_test';
    const TO_MAINTAIN = 'yes';
    const SEVERITY = 'S0';
    /* end tags */

    /**
     * Runs Express Checkout from shopping cart test.
     *
     * @return void
     */
    public function test()
    {
        $this->executeScenario();
    }
}
