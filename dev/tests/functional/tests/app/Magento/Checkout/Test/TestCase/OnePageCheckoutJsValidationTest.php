<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
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
 * @ZephyrId MAGETWO-59697
 */
class OnePageCheckoutJsValidationTest extends Scenario
{
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
