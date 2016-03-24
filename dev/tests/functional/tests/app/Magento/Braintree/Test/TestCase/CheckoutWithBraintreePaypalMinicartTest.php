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
 * 1.  Log in Storefront.
 * 2.  Add products to the Shopping Cart.
 * 3.  Open the Mini shopping cart and click "Checkout with PayPal"
 * 4.  Click 'Proceed purchase' in popup.
 * 5.  Select a shipping method.
 * 6.  Click 'Place Order' button.
 * 8.  Select payment method
 * 12. Perform assertions.
 *
 * @group Braintree_(CS)
 * @ZephyrId MAGETWO-39359
 */
class CheckoutWithBraintreePaypalMinicartTest extends Scenario
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
