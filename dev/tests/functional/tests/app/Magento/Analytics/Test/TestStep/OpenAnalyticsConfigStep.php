<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Test\TestStep;

use Magento\Backend\Test\Page\Adminhtml\Dashboard;
use Magento\Backend\Test\Page\Adminhtml\SystemConfigEdit;
use Magento\Mtf\TestStep\TestStepInterface;

/**
 * Steps:
 * 1. Log in to backend.
 * 2. Navigate to Stores->Configuration->General->Analytics->General menu.
 */
class OpenAnalyticsConfigStep implements TestStepInterface
{
    /**
     * Dashboard page.
     *
     * @var Dashboard
     */
    private $dashboard;

    /**
     * System Config page.
     *
     * @var SystemConfigEdit
     */
    private $systemConfigPage;

    /**
     * @param Dashboard $dashboard
     * @param SystemConfigEdit $systemConfigPage
     */
    public function __construct(Dashboard $dashboard, SystemConfigEdit $systemConfigPage)
    {
        $this->dashboard = $dashboard;
        $this->systemConfigPage = $systemConfigPage;
    }

    /**
     * Navigate to Stores->Configuration->General->Analytics->General menu.
     *
     * @return void
     */
    public function run()
    {
        $this->dashboard->open();
        $this->dashboard->getMenuBlock()->navigate('Stores > Configuration');
        $this->systemConfigPage->getForm()->getGroup('analytics', 'general');
    }
}
