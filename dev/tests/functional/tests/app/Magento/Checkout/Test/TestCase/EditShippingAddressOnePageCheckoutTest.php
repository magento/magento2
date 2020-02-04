<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Test\TestCase;

use Magento\Mtf\TestCase\Scenario;

/**
 * Preconditions:
 *
 * 1. Simple product is created.
 * 2. Customer with default billing/shipping address is created.
 *
 * Steps:
 * 1. Go to Frontend as Customer.
 * 2. Add product in cart and proceed to checkout.
 * 3. Click *New Address* button on 1st checkout step.
 * 4. Fill in required fields and click *Save address* button.
 * 5. Select Shipping Rate.
 * 6. Click *Edit* button for the new address.
 * 7. Remove values from required fields and click *Cancel* button.
 * 8. Go to *Next*.
 * 9. Select payment solution.
 * 10. Refresh page.
 * 11. Click Place order.
 * 12. Perform all assertions.
 *
 * @group Checkout
 * @ZephyrId MAGETWO-67837
 */
class EditShippingAddressOnePageCheckoutTest extends Scenario
{
    /**
     * Edit Shipping Address on Checkout Page.
     *
     * @return void
     */
    public function test()
    {
        $this->executeScenario();
    }
}
