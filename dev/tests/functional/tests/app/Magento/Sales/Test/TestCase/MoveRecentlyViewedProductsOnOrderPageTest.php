<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\TestCase;

use Magento\Mtf\TestCase\Scenario;

/**
 * Preconditions:
 * 1. Create customer.
 * 2. Create product.
 * 3. Open product on frontend.
 *
 * Steps:
 * 1. Login in to Backend.
 * 2. Open Customers > All Customers.
 * 3. Search and open customer from preconditions.
 * 4. Click Create Order.
 * 5. Check product in Recently Viewed Products section.
 * 6. Click Update Changes.
 * 7. Click Configure.
 * 8. Fill data from dataset.
 * 9. Click OK.
 * 10. Click Update Items and Qty's button.
 * 11. Perform all assertions.
 *
 * @group Order_Management_(CS)
 * @ZephyrId MAGETWO-29723
 */
class MoveRecentlyViewedProductsOnOrderPageTest extends Scenario
{
    /* tags */
    const MVP = 'yes';
    const DOMAIN = 'CS';
    const TO_MAINTAIN = 'yes';
    /* end tags */

    /**
     * Runs Move Recently Viewed Products On Order Page.
     *
     * @return void
     */
    public function test()
    {
        $this->executeScenario();
    }
}
