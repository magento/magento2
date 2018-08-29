<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Test\TestCase;

use Magento\Mtf\TestCase\Scenario;

/**
 * Preconditions:
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
 * 9. Select payment method.
 * 10. Specify credit card data.
 * 11. Click 'Place order' button.
 * 12. Search for created case.
 * 13. Open created case.
 * 14. Click "Flag case as good" or "Flag case as bad" button.
 * 15. Perform case info assertions.
 * 16. Log in to Admin.
 * 17. Proceed to order grid.
 * 18. Perform Signifyd guarantee status assertions.
 * 19. Proceed to order view.
 * 20. Perform order status and case info assertions.
 * 21. Click Cancel button.
 * 22. Perform remaining assertions.
 *
 * @group Signifyd
 * @ZephyrId MAGETWO-62120, MAGETWO-63221, MAGETWO-64305, MAGETWO-65253
 */
class CreateSignifydGuaranteeAndCancelOrderTest extends Scenario
{
    /* tags */
    const MVP = 'yes';
    const TEST_TYPE = '3rd_party_test_single_flow';
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
