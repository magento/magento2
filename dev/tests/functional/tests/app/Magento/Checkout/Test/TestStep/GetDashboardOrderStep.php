<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Test\TestStep;

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
    private $items;

    /**
     * @param Dashboard $dashboard
     * @param array $items
     */
    public function __construct(
        Dashboard $dashboard,
        array $items = []
    ) {
        $this->dashboard = $dashboard;
        $this->items = $items;
    }

    /**
     * Return order information from admin dashboard.
     *
     * @return array
     */
    public function run()
    {
        if (isset($this->items)) {
            $this->dashboard->open();
            $dashboardOrder = $this->dashboard->getMainBlock()->getDashboardOrder($this->items);
            return ['dashboardOrder' => $dashboardOrder];
        }
    }
}
