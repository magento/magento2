<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\TestCase;

use Magento\Mtf\TestCase\Scenario;

/**
 * Preconditions:
 * 1. Create customer.
 * 2. Create product.
 *
 * Steps:
 * 1. Log in Admin.
 * 2. Open Sales -> Orders.
 * 3. Click Create New Order.
 * 4. Select Customer created in preconditions.
 * 5. Add Product.
 * 6. Fill data according dataset.
 * 7. Click Update Product qty.
 * 8. Fill data according dataset.
 * 9. Click Get Shipping Method and rates.
 * 10. Fill data according dataset.
 * 11. Submit Order.
 * 12. Go to Sales > Orders.
 * 13. Select created order in the grid and open it.
 * 14. Click 'Invoice' button.
 * 15. Fill data according to dataset.
 * 16. Click 'Submit Invoice' button.
 * 17. Perform assertions.
 *
 * @group Order_Management
 * @ZephyrId MAGETWO-47010
 */
class CreateOnlineInvoiceEntityTest extends Scenario
{
    /* tags */
    const MVP = 'yes';
    const TEST_TYPE = '3rd_party_test';
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
