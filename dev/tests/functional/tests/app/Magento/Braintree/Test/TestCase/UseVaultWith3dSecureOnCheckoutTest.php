<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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
 * 5. Configure taxes.
 *
 * Steps:
 * 1. Log in Storefront.
 * 2. Add products to the cart.
 * 3. Click the 'Proceed to Checkout' button.
 * 4. Select shipping method.
 * 5. Select payment method (use reward points and store credit if available).
 * 6. Enter credit card data and select *Save credit card* checkbox.
 * 7. Click Place Order button.
 * 8. Specify password in 3D Secure popup.
 * 9. Click 'Submit' to place order.
 * 10. Add products to the cart.
 * 11. Click the 'Proceed to Checkout' button.
 * 12. Select shipping method.
 * 13. Select saved credit card as a payment.
 * 14. Click Place Order button.
 * 15. Specify password in 3D Secure popup.
 * 16. Click 'Submit' to place order.
 * 17. Perform assertions.
 *
 * @group One_Page_Checkout
 * @ZephyrId MAGETWO-55310
 */
class UseVaultWith3dSecureOnCheckoutTest extends Scenario
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
