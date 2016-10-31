<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Test\TestCase;

use Magento\Mtf\TestCase\Scenario;

/**
 * Preconditions:
 * 1. Create customer.
 * 2. Create product.
 *
 * Steps:
 * 1. Open Backend.
 * 2. Open Sales -> Orders.
 * 3. Click Create New Order.
 * 4. Select Customer created in preconditions.
 * 5. Add Product.
 * 6. Fill data according dataset.
 * 7. Click Update Product qty.
 * 8. Fill data according dataset.
 * 9. Click Get Shipping Method and rates.
 * 10. Fill data according dataset.
 * 11. Select payment method Credit Card (PayFlow Pro)
 * 12. Leave empty required fields for credit card 
 * 13. Click Submit order button
 *
 * @group PayPal
 * @ZephyrId MAGETWO-58934
 */
class CreatePayFlowOrderBackendNegativeTest extends Scenario
{
    /* tags */
    const MVP = 'yes';
    const TEST_TYPE = '3rd_party_test';
    const SEVERITY = 'S2';
    /* end tags */

    /**
     * Runs sales order on backend.
     *
     * @return void
     */
    public function test()
    {
        $this->executeScenario();
    }
}
