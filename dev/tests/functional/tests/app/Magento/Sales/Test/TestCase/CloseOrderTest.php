<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\TestCase;

use Magento\Mtf\TestCase\Scenario;

/**
 * Preconditions:
 * 1. Order is placed.
 *
 * Steps:
 * 1. Log in to Admin.
 * 2. Go to Sales > Orders page.
 * 3. Open order.
 * 4. Click 'Ship' button and submit shipment.
 * 5. Click 'Invoice' button.
 * 6. Select Amount=Capture Online.
 * 7. Click 'Submit Invoice' button.
 * 8. Perform assertions.
 *
 * @group Order_Management
 * @ZephyrId MAGETWO-13015, MAGETWO-13020
 */
class CloseOrderTest extends Scenario
{
    /* tags */
    const MVP = 'yes';
    const TEST_TYPE = '3rd_party_test';
    const SEVERITY = 'S0';
    /* end tags */

    /**
     * Close order.
     *
     * @return void
     */
    public function test()
    {
        $this->executeScenario();
    }
}
