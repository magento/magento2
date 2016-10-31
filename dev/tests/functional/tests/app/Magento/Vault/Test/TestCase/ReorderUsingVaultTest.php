<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Vault\Test\TestCase;

use Magento\Mtf\TestCase\Scenario;

/**
 * Preconditions:
 * 1. Configure shipping method.
 * 2. Configure payment method.
 * 3. Create products.
 * 4. Create and setup customer.
 * 5. Configure taxes.
 *
 * Steps:
 * 1. Log in Storefront.
 * 2. Add products to the cart.
 * 3. Click the 'Proceed to Checkout' button.
 * 4. Select shipping method.
 * 5. Select payment method (use reward points and store credit if available).
 * 6. Enter credit card data and select *Save credit card* checkbox.
 * 7. Click Place Order button.
 * 8. Reorder placed order.
 * 9. Select stored cards as payment method.
 * 10. Select any available payment token.
 * 11. Place order.
 *
 * @group Order_Management
 * @ZephyrId MAGETWO-54870, MAGETWO-54872
 */
class ReorderUsingVaultTest extends Scenario
{
    /* tags */
    const MVP = 'yes';
    const TEST_TYPE = 'extended_acceptance_test, 3rd_party_test';
    const SEVERITY = 'S1';

    /* end tags */

    /**
     * Reorders placed order on backend using vault.
     *
     * @return void
     */
    public function test()
    {
        $this->executeScenario();
    }
}
