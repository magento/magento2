<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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
 * 1. Log in Storefront.
 * 2. Add products to the Shopping Cart.
 * 3. Click the 'Proceed to Checkout' button.
 * 4. Fill shipping information.
 * 5. Select shipping method.
 * 6. Select payment method.
 * 7. Specify credit card data.
 * 8. Click 'Place order' button.
 * 9. Search for created case.
 * 10. Open created case.
 * 11. Click "Flag case as good" button.
 * 12. Perform case info assertions.
 * 13. Log in to Admin.
 * 14. Proceed to order grid.
 * 15. Perform Signifyd guarantee status assertions.
 * 16. Proceed to order view.
 * 17. Perform order status and case info assertions.
 * 18. Click Cancel button.
 * 19. Perform remaining assertions.
 *
 * @group Signifyd
 * @ZephyrId MAGETWO-62120, MAGETWO-63221
 */
class CreateSignifydGuaranteeAndCancelOrderTest extends Scenario
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
