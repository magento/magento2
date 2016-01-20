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
 * 3. In 'Estimate Shipping and Tax' section specify destination using values from Test Data.
 * 4. Click the 'Go to Checkout' button.
 * 5. Fill shipping information.
 * 6. Select shipping method.
 * 7. Select payment method (use reward points and store credit if available).
 * 8. Verify order total on review step.
 * 9. Click 'Place Order' button.
 * 10. Go to admin panel.
 * 11. Open 'Reports' -> 'Braintree Settlement'.
 * 12. Find transaction for latest order.
 * 13. Perform assertions.
 *
 * @group Braintree_(CS)
 * @ZephyrId MAGETWO-48162
 */
class BraintreeSettlementReportTest extends Scenario
{
    /* tags */
    const MVP = 'yes';
    const DOMAIN = 'CS';
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
