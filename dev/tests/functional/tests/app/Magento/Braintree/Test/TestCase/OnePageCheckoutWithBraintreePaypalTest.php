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
 * 8. Select payment method
 * 9. Verify order total on review step.
 * 10. Click 'Continue to PayPal' button.
 * 11. Click 'Proceed purchase' in popup.
 * 12. Perform assertions.
 *
 * @group Braintree_(CS)
 * @ZephyrId MAGETWO-47805
 * @ZephyrId MAGETWO-47810
 */
class OnePageCheckoutWithBraintreePaypalTest extends Scenario
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
