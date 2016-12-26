<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Paypal\Test\TestCase;

use Magento\Mtf\TestCase\Scenario;

/**
 * Preconditions:
 * 1. Order is placed via WPPHS.
 *
 * Steps:
 * 1. Log in to Admin.
 * 2. Go to Sales > Orders page.
 * 3. Open order.
 * 4. Click 'Ship' button and submit shipment.
 * 5. Click 'Invoice' button.
 * 6. Select Amount=Capture Online.
 * 7. Click 'Submit Invoice' button.
 * 11. Perform assertions.
 *
 * @group Paypal
 * @ZephyrId MAGETWO-13016
 */
class CloseSalesWithHostedProTest extends Scenario
{
    /* tags */
    const MVP = 'yes';
    const TEST_TYPE = '3rd_party_test';
    /* end tags */

    /**
     * Complete order paid PayPal Payments Pro Hosted Solution.
     *
     * @return void
     */
    public function test()
    {
        $this->executeScenario();
    }
}
