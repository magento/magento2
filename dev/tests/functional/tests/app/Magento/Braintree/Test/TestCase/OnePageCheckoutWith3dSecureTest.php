<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
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
 * 5. Create sales rule according to dataset.
 *
 * Steps:
 * 1. Log in Storefront.
 * 2. Add products to the Shopping Cart.
 * 3. Apply discounts in Shopping Cart according to dataset.
 * 4. In 'Estimate Shipping and Tax' section specify destination using values from Test Data.
 * 5. Click the 'Proceed to Checkout' button.
 * 6. Fill shipping information.
 * 7. Select shipping method.
 * 8. Select payment method (use reward points and store credit if available).
 * 9. Verify order total on review step.
 * 10. Click 'Place Order' button.
 * 11. Specify password in 3D Secure popup.
 * 12. Click 'Submit' to place order.
 * 13. Perform assertions.
 *
 * @group Braintree
 * @ZephyrId MAGETWO-46479
 */
class OnePageCheckoutWith3dSecureTest extends Scenario
{
    /* tags */
    const MVP = 'yes';
    const TEST_TYPE = '3rd_party_test';
    const SEVERITY = 'S1';
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
