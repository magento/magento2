<?php
/**
 * Copyright © 2013-2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Test\TestStep;

use Magento\Backend\Test\Page\Adminhtml\Dashboard;
use Magento\Mtf\TestStep\TestStepInterface;

/**
 * Navigate to Dashboard.
 */
class OpenDashboardStep implements TestStepInterface
{
    /**
     * Dashboard page.
     *
     * @var Dashboard
     */
    private $dashboard;

    /**
     * @param Dashboard $dashboard
     */
    public function __construct(Dashboard $dashboard)
    {
        $this->dashboard = $dashboard;
    }

    /**
     * Run step flow.
     *
     * @return void
     */
    public function run()
    {
        $this->dashboard->open();
    }
}
