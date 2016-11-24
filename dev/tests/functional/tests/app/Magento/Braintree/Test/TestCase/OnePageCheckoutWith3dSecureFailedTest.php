<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Braintree\Test\TestCase;

use Magento\Mtf\TestCase\Scenario;

/**
 * Preconditions:
 * 1. Configure payment method.
 * 2. Create products.
 *
 * Steps:
 * 1. Log in Storefront.
 * 2. Add products to the Shopping Cart.
 * 5. Click the 'Proceed to Checkout' button.
 * 6. Fill shipping information.
 * 7. Select shipping method.
 * 8. Select payment method.
 * 9. Verify order total on review step.
 * 10. Click 'Place Order' button.
 * 11. Specify password in 3D Secure popup.
 * 12. Click 'Submit'.
 * 13. Perform assertions.
 *
 * @group Braintree
 * @ZephyrId MAGETWO-46477
 */
class OnePageCheckoutWith3dSecureFailedTest extends Scenario
{
    /* tags */
    const MVP = 'yes';
    const TEST_TYPE = '3rd_party_test';
    const SEVERITY = 'S1';
    /* end tags */

    /**
     * Verifies error message on Onepage Checkout if 3d secure validation is failed.
     *
     * @return void
     */
    public function test()
    {
        $this->executeScenario();
    }
}
