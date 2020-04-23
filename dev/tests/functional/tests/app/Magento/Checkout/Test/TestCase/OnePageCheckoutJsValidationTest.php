<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Test\TestCase;

use Magento\Mtf\TestCase\Scenario;

/**
 * Steps:
 * 1. Go to Frontend as guest.
 * 2. Add simple product to shopping cart
 * 3. Go to shopping cart page
 * 4. Proceed to checkout
 * 5. Perform assertions.
 *
 * @group One_Page_Checkout
 * @ZephyrId MC-18203
 */
class OnePageCheckoutJsValidationTest extends Scenario
{
    /* tags */
    const SEVERITY = 'S2';
    /* end tags */

    /**
     * Runs one page checkout js validation test.
     *
     * @return void
     */
    public function test()
    {
        $this->executeScenario();
    }
}
