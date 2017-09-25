<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SalesRule\Test\TestCase;

use Magento\Mtf\TestCase\Scenario;

/**
 * Steps:
 * 1. Open Backend.
 * 2. Create a new cart price rule with start and end date.
 * 3. Go to admin settings Page.
 * 4. Change the interface locale and save.
 * 5. Go to Cart price Rules Page.
 * 6. Select and open the Cart price.
 * 7. Validate the date format.
 * 8. Revert back the interface locale.
 *
 * @group Shopping_Cart_Price_Rules_(CS)
 * @ZephyrId MAGETWO-65024
 */
 class SalesRuleDateAfterInterfaceLocaleSwitchTest extends Scenario
{
     /* tags */
     const MVP = 'no';
     const SEVERITY = 'S1';
     /* end tags */

    /**
     * Checks the date format after switching interface locale.
     *
     * @return void
     */
    public function test()
    {
        /**
         * Skip test for 'sales rule date after interface locale switch' in production mode.
         */
        if ($_ENV['mage_mode'] === 'production') {
            $this->markTestSkipped(
                'Skip sales rule date after interface locale switch test when in production mode.'
            );
        }

        $this->executeScenario();
    }
}
