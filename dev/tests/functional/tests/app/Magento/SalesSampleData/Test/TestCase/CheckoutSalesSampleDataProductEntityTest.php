<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SalesSampleData\Test\TestCase;

use Magento\Mtf\TestCase\Scenario;

/**
 * Preconditions:
 * 1. Configure shipping method.
 * 2. Configure payment method.
 * 3. Set products from dataset.
 * 4. Create and setup customer.
 * 5. Create sales rule according to dataset.
 *
 * Steps:
 * 1. Go to Frontend.
 * 2. Add products to the cart.
 * 3. Apply discounts in shopping cart according to dataset.
 * 4. In 'Estimate Shipping and Tax' section specify destination using values from Test Data
 * 5. Click the 'Proceed to Checkout' button.
 * 6. Select checkout method according to dataset.
 * 7. Fill shipping information and select the 'Ship to this address' option.
 * 8. Select shipping method.
 * 9. Select payment method (use reward points and store credit if available).
 * 10. Verify order total on review step.
 * 11. Place order.
 * 12. Perform assertions.
 *
 * @group Sample_Data_(MX)
 * @ZephyrId MAGETWO-33559
 */
class CheckoutSalesSampleDataProductEntityTest extends Scenario
{
    /* tags */
    const MVP = 'yes';
    const DOMAIN = 'MX';
    const TEST_TYPE = 'acceptance_test';
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
