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
 *
 * Steps:
 * 1. Go to Storefront as registered customer.
 * 2. Add products to the cart.
 * 3. Click the *Proceed to Checkout* button.
 * 4. Select shipping method.
 * 5. Select payment method (use reward points and store credit if available).
 * 6. Select *Save for later use* checkbox.
 * 7. Click *Continue to PayPal* button.
 * 8. Click *Proceed with Sandbox Purchase* button.
 * 9. Click Place Order button.
 * 8. Add products to the cart.
 * 9. Click the *Proceed to Checkout* button.
 * 10. Select shipping method.
 * 11. Select PayPal payer account as a payment.
 * 12. Click Place Order button.
 * 13. Go to *My Account > Stored Payment Methods* section.
 * 14. Click *Delete* link next to stored PayPal payer account.
 * 15. Click *Delete* button on appeared pop up.
 * 16. Perform assertions. *
 *
 * @group Braintree
 * @ZephyrId MAGETWO-54838, MAGETWO-54843, MAGETWO-54844"
 */
class SaveUseDeleteVaultForPaypalBraintreeTest extends Scenario
{
    /* tags */
    const MVP = 'yes';
    const TEST_TYPE = '3rd_party_test';
    const SEVERITY = 'S0';
    /* end tags */

    /**
     * Saves vault for PayPal Braintree on checkout, uses it during checkout, deletes it from My Account.
     *
     * @return void
     */
    public function test()
    {
        $this->executeScenario();
    }
}
