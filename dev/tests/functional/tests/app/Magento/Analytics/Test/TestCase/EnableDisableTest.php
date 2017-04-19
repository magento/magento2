<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Analytics\Test\TestCase;

use Magento\Analytics\Test\Page\Adminhtml\ConfigAnalytics;
use Magento\Mtf\TestCase\Injectable;

/**
 * Steps:
 * 1. Login as admin user in backend
 * 2. Navigate to menu Stores>Configuration>General>Advanced Reporting->General
 * 3. Set Option "Advanced Reporting Service"
 * 4. Click "Save Config"
 * 5. Perform assertions
 *
 * @ZephyrId MAGETWO-66465
 */
class EnableDisableTest extends Injectable
{
    /* tags */
    const MVP = 'no';
    const SEVERITY = 'S1';
    /* end tags */

    /**
     * @param ConfigAnalytics $configAnalytics
     * @param string $vertical
     * @param string $state
     * @return void
     */
    public function test(ConfigAnalytics $configAnalytics, $vertical, $state)
    {
        $configAnalytics->open();
        $configAnalytics->getAnalyticsForm()->analyticsToggle($state);
        $configAnalytics->getAnalyticsForm()->setAnalyticsVertical($vertical);
        $configAnalytics->getAnalyticsForm()->saveConfig();
    }
}
