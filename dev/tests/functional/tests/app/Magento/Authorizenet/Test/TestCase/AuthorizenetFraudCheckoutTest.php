<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Authorizenet\Test\TestCase;

use Magento\Mtf\TestCase\Scenario;

/**
 * Preconditions:
 * 1. Configure payment method.
 * 2. Create products.
 *
 * Steps:
 * 1. Log in Storefront.
 * 2. Add products to the Shopping Cart.
 * 3. Click the 'Proceed to Checkout' button.
 * 4. Fill shipping information.
 * 5. Select shipping method.
 * 6. Select payment method.
 * 7. Click 'Place Order' button.
 * 8. Log in to Authorize.Net sandbox.
 * 9. Accept transaction.
 * 10. Log in to Magento Admin.
 * 11. Open order.
 * 12. Perform assertions.
 *
 * @group Checkout
 * @ZephyrId MAGETWO-38379
 */
class AuthorizenetFraudCheckoutTest extends Scenario
{
    /* tags */
    const TEST_TYPE = '3rd_party_test';
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
