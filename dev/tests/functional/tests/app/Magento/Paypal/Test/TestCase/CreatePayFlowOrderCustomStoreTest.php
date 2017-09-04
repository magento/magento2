<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Test\TestCase;

use Magento\Mtf\TestCase\Scenario;
use Magento\Mtf\TestStep\TestStepFactory;

/**
 * Preconditions:
 * 1. Create simple product.
 * 2. Configure PayPal Payflow Pro.
 * 3. Configure Flat Rate.
 * 4. Set Base Currency US Dollar
 * 5. Create custom store view for Main Website.
 * 6. Set default display currency Euro for custom store view.
 *
 * Steps:
 * 1. Go to storefront.
 * 2. Switch to the additional Store View.
 * 3. Add simple product to the cart.
 * 4. Proceed to checkout.
 * 5. Specify shiping address data (US Customer 1).
 * 6. Specify shipping method (Fixed Flat Rate).
 * 7. Select payment method Credit Card (PayFlow Pro).
 * 8. Fill required fields for credit card.
 * 9. Click Submit order button.
 * 10. Make assertions.
 *
 * @group PayPal
 * @ZephyrId MAGETWO-61668
 */
class CreatePayFlowOrderCustomStoreTest extends Scenario
{
    /* tags */
    const MVP = 'yes';
    const TEST_TYPE = '3rd_party_test';
    const SEVERITY = 'S2';
    /* end tags */

    /**
     * Runs sales order on backend.
     *
     * @return void
     */
    public function test()
    {
        $this->executeScenario();
    }
}
