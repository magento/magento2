<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Test\TestCase;

use Magento\Mtf\TestCase\Scenario;

/**
 * Preconditions:
 * 1. Create two products.
 * 2. Create customer with two addresses.
 *
 * Steps:
 * 1. Go to Frontend.
 * 2. Add products to the cart.
 * 3. Go to cart.
 * 4. Click 'Check Out with Multiple Addresses'.
 * 5. Choose different address for each product.
 * 6. Press 'Go To Shipping Information.'
 * 7. Press 'Change' button on both addresses.
 * 8. Perform assertion that all addresses contain full information.
 *
 * @group One_Page_Checkout_(CS)
 * @ZephyrId MAGETWO-64728
 */
class UpdateAddressOnCheckoutWithMultipleAddressesTest extends Scenario
{
    /**
     * Runs update address on checkout with multiple addresses test.
     *
     * @return void
     */
    public function test()
    {
        $this->executeScenario();
    }
}
