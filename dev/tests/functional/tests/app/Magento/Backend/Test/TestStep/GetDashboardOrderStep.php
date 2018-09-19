<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Test\TestStep;

use Magento\Mtf\TestStep\TestStepInterface;
use Magento\Backend\Test\Page\Adminhtml\Dashboard;

/**
 * Get order information from admin dashboard.
 */
class GetDashboardOrderStep implements TestStepInterface
{
    /**
     * Dashboard page.
     *
     * @var Dashboard
     */
    private $dashboard;

    /**
     * Needed information from dashboard.
     *
     * @var array
     */
    private $argumentsList;

    /**
     * @param Dashboard $dashboard
     * @param array $argumentsList
     */
    public function __construct(
        Dashboard $dashboard,
        array $argumentsList = []
    ) {
        $this->dashboard = $dashboard;
        $this->argumentsList = $argumentsList;
    }

    /**
     * Return order information from admin dashboard.
     *
     * @return array
     */
    public function run()
    {
        $dashboardOrder = [];
        if (isset($this->argumentsList)) {
            $this->dashboard->open();
            $dashboardOrder = $this->dashboard->getMainBlock()->getDashboardOrder($this->argumentsList);
        }
        return ['dashboardOrder' => $dashboardOrder];
    }
}
