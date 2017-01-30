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
 * 2. Create product.
 * 3. Apply configuration for test.
 *
 * Steps:
 * 1. Go to Frontend.
 * 2. Fill checkout product data.
 * 3. Click "Checkout with PayPal" button.
 * 4. Login to PayPal.
 * 5. Process checkout via PayPal.
 * 6. Perform asserts.
 *
 * @group PayPal_(CS)
 * @ZephyrId MAGETWO-12415
 */
class ExpressCheckoutFromProductPageTest extends Scenario
{
    /* tags */
    const MVP = 'yes';
    const DOMAIN = 'CS';
    const TEST_TYPE = '3rd_party_test';
    /* end tags */

    /**
     * Runs Express Checkout from product page test.
     *
     * @return void
     */
    public function test()
    {
        $this->executeScenario();
    }
}
