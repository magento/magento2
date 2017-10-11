<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Analytics\Test\TestCase;

use Magento\Mtf\TestCase\Injectable;
use Magento\Analytics\Test\Page\Adminhtml\ConfigAnalytics;

/**
 * Steps:
 * 1. Log in to backend.
 * 2. Navigate to analytics menu in system config
 * 3. Select one of the verticals and save config
 * 4. Perform assertions
 *
 * @ZephyrId MAGETWO-63898
 */
class SetIndustryTest extends Injectable
{
    /* tags */
    const MVP = 'no';
    /* end tags */

    /**
     * Set analytics vertical test.
     *
     * @param ConfigAnalytics $configAnalytics
     * @param string $industry
     * @return void
     */
    public function test(ConfigAnalytics $configAnalytics, $industry)
    {
        $configAnalytics->open();
        $configAnalytics->getAnalyticsForm()->setAnalyticsVertical($industry);
        $configAnalytics->getAnalyticsForm()->saveConfig();
    }
}
