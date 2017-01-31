<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Analytics\Test\TestCase;

use Magento\Mtf\TestCase\Injectable;
use Magento\Analytics\Test\Page\Adminhtml\ConfigAnalytics;

/**
 * Steps:
 * 1. Log in to backend.
 * 2. Navigate to analytics menu in system config
 * 3. Select one of the verticals and save config.
 * 4. Assert setting is saved
 *
 * @ZephyrId MAGETWO-63898
 */
class SetVerticalTest extends Injectable
{
    /* tags */
    const MVP = 'no';
    /* end tags */

    /**
     * Set analytics vertical test.
     *
     * @param ConfigAnalytics $configAnalytics
     * @return void
     */
    public function test(ConfigAnalytics $configAnalytics)
    {
        $configAnalytics->open();
        $configAnalytics->getAnalyticsForm()->setAnalyticsVertical();
        $configAnalytics->getAnalyticsForm()->saveConfig();
    }
}
