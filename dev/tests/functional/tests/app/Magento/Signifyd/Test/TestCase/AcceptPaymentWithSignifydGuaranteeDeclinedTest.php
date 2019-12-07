<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Test\TestCase;

use Magento\Mtf\TestCase\Scenario;

/**
 * * Preconditions:
 * 1. Configure shipping method.
 * 2. Configure payment method.
 * 3. Configure Signifyd fraud protection tool
 * 4. Create products.
 * 5. Create and setup customer.
 *
 * Steps:
 * 1. Log in to Signifyd account.
 * 2. Remove all existing webhooks by test team.
 * 3. Add new webhook set.
 * 4. Log in Storefront.
 * 5. Add products to the Shopping Cart.
 * 6. Click the 'Proceed to Checkout' button.
 * 7. Fill shipping information.
 * 8. Select shipping method.
 * 9. Select Hosted Pro method.
 * 10. Click 'Continue' button.
 * 11. Specify credit card data in Paypal iframe.
 * 12. Click 'Pay Now' button.
 * 13. Log in to Signifyd account and search for created case.
 * 14. Open created case.
 * 15. Click "Flag case as bad" button.
 * 16. Perform case info assertions.
 * 17. Log in to Admin.
 * 18. Proceed to order grid.
 * 19. Perform Signifyd guarantee status assertions.
 * 20. Proceed to order view.
 * 21. Perform order status and case info assertions.
 * 22. Click Accept Payment button.
 * 23. Perform remaining assertions.
 *
 * @group Signifyd
 * @ZephyrId MAGETWO-65333
 */
class AcceptPaymentWithSignifydGuaranteeDeclinedTest extends Scenario
{
    /* tags */
    const MVP = 'yes';
    const TEST_TYPE = '3rd_party_test_single_flow';
    const SEVERITY = 'S2';
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
