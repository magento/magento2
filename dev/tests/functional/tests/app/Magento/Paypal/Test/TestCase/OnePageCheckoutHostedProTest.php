<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Paypal\Test\TestCase;

use Magento\Mtf\TestCase\Scenario;

/**
 * Preconditions:
 * 1. Configure shipping method.
 * 2. Configure payment method.
 * 3. Create products.
 * 4. Create sales rule according to dataset.
 *
 * Steps:
 * 1. Go to Storefront.
 * 2. Add products to the cart.
 * 3. Click the 'Go to Checkout' button.
 * 4. Fill shipping information.
 * 5. Select shipping method.
 * 6. Click 'Next' button.
 * 7. Select Hosted Pro method.
 * 8. Click 'Continue' button.
 * 9. Specify credit card data in Paypal iframe.
 * 10. Click 'Pay Now' button.
 * 11. Perform assertions.
 *
 * @group Paypal
 * @ZephyrId MAGETWO-12971
 */
class OnePageCheckoutHostedProTest extends Scenario
{
    /* tags */
    const MVP = 'yes';
    const TEST_TYPE = '3rd_party_test';
    /* end tags */

    /**
     * Place order using PayPal Payments Pro Hosted Solution.
     *
     * @return void
     */
    public function test()
    {
        $this->executeScenario();
    }
}
