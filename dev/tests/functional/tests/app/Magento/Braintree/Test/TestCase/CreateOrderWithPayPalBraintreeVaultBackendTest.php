<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Test\TestCase;

use Magento\Mtf\TestCase\Scenario;

/**
 * Preconditions:
 * 1. Configure shipping method.
 * 2. Configure payment method.
 * 3. Create products.
 * 4. Create and setup customer.
 *
 * Steps:
 * 1. Log in Storefront.
 * 2. Add products to the Shopping Cart.
 * 3. In 'Estimate Shipping and Tax' section specify destination using values from Test Data.
 * 4. Click the 'Proceed to Checkout' button.
 * 5. Fill shipping information.
 * 6. Select shipping method.
 * 8. Select payment method
 * 9. Verify order total on review step.
 * 10. Click 'Continue to PayPal' button.
 * 11. Click 'Proceed purchase' in popup.
 * 12. Log in Admin panel.
 * 13. Open placed order.
 * 14. Click 'Reorder' button.
 * 15. Select stored Braintree PayPal token.
 * 16. Click 'Submit Order'.
 * 17. Perform assertions.
 *
 * @group Braintree
 * @ZephyrId MAGETWO-59259
 */
class CreateOrderWithPayPalBraintreeVaultBackendTest extends Scenario
{
    /* tags */
    const MVP = 'yes';

    const TEST_TYPE = '3rd_party_test';

    const SEVERITY = 'S0';
    /* end tags */

    /**
     * Runs test scenario
     * @return void
     */
    public function test()
    {
        $this->executeScenario();
    }
}
