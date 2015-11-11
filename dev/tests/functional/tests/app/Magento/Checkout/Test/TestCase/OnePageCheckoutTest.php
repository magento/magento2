<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Test\TestCase;

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
 * 1. Go to Frontend.
 * 2. Add products to the cart.
 * 3. Apply discounts in shopping cart according to dataset.
 * 4. In 'Estimate Shipping and Tax' section specify destination using values from Test Data
 * 5. Click the 'Get a Quote' button
 * 6. In the section appeared select Shipping method, click the 'Update Total' button
 * 7. Click the 'Proceed to Checkout' button.
 * 8. Select checkout method according to dataset.
 * 9. Fill billing information and select the 'Ship to this address' option.
 * 10. Select shipping method.
 * 11. Select payment method (use reward points and store credit if available).
 * 12. Verify order total on review step.
 * 13. Place order.
 * 14. Perform assertions.
 *
 * @group One_Page_Checkout_(CS)
 * @ZephyrId MAGETWO-27485, MAGETWO-12412, MAGETWO-12429
 * @ZephyrId MAGETWO-12444, MAGETWO-12848, MAGETWO-12849, MAGETWO-12850
 */
class OnePageCheckoutTest extends Scenario
{
    /* tags */
    const MVP = 'yes';
    const DOMAIN = 'CS';
    const TEST_TYPE = 'acceptance_test, 3rd_party_test';
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
