<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Shipping\Test\TestCase;

use Magento\Mtf\TestCase\Scenario;

/**
 * Preconditions:
 * 1. Enable payment method "Check/Money Order".
 *
 * Steps:
 * 1. Go to Storefront.
 * 2. Add products to the shopping cart.
 * 3. Fill shipping information on Checkout step.
 * 4. Select shipping method.
 * 5. Place order.
 * 6. Go to Sales > Orders.
 * 7. Select created order in the grid and open it.
 * 8. Click 'Ship' button.
 * 9. Fill data according to dataset.
 * 10. Click 'Submit Shipment' button.
 * 11. Select created shipment in the grid and open it.
 * 12. Add tracking number according to dataset.
 * 13. Select created order in the grid and open it.
 * 14. Click on 'Track Order' link.
 * 15. Perform all asserts.
 *
 * @group Shipping
 * @ZephyrId MAGETWO-65163, MAGETWO-58158
 */
class TrackingShipmentForPlacedOrderTest extends Scenario
{
    /* tags */
    const MVP = 'yes';
    const SEVERITY = 'S1';
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
