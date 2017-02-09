<?php
/**
 * Copyright © 2013-2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Test\TestStep;

use Magento\Analytics\Test\Page\Adminhtml\ConfigAnalytics;
use Magento\Mtf\TestStep\TestStepInterface;

/**
 * Navigate to menu Stores > Configuration > General > Analytics > General
 */
class ConfigAnalyticsStep implements TestStepInterface
{
    /**
     * Analytics Config settings page.
     *
     * @var configAnalytics
     */
    private $configAnalytics;

    /**
     * @param ConfigAnalytics $configAnalytics
     */
    public function __construct(
        ConfigAnalytics $configAnalytics
    ) {
        $this->configAnalytics = $configAnalytics;
    }

    /**
     * Open Config Analytics settings menu.
     *
     * @return void
     */
    public function run()
    {
        $this->configAnalytics->open();
    }
}
