<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Test\TestCase;

use Magento\Mtf\TestCase\Scenario;

/**
 * Steps:
 * 1. Open Backend.
 * 2. Go to admin setting page.
 * 3. Change the interface locale and save.
 * 4. Verify the save message.
 * 5. Revert back the interface locale.
 *
 * @ZephyrId MAGETWO-65024
 */
class SwitchAdminInterfaceLocaleTest extends Scenario
{
    /* tags */
    const MVP = 'no';
    const SEVERITY = 'S1';
    /* end tags */

    /**
     * Verify the save message after switching the interface locale.
     *
     * @return void
     */
    public function test()
    {
        /**
         * Skip test for 'Switch Admin Interface Locale' in production mode.
         */
        if ($_ENV['mage_mode'] === 'production') {
            $this->markTestSkipped(
                'Skip Switch Admin Interface Locale test when in production mode.'
            );
        }

        $this->executeScenario();
    }
}
