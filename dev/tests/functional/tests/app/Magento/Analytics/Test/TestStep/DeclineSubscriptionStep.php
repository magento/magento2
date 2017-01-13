<?php
/**
 * Copyright © 2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Test\TestStep;

use Magento\Backend\Test\Page\Adminhtml\Dashboard;
use Magento\Mtf\TestStep\TestStepInterface;

/**
 * Steps:
 * 1. Log in to backend.
 * 2. Click Cancel on subscription pop-up
 */
class DeclineSubscriptionStep implements TestStepInterface
{
    /**
     * Dashboard page.
     *
     * @var Dashboard
     */
    private $dashboard;

    public function __construct(Dashboard $dashboard)
    {
        $this->dashboard = $dashboard;
    }

    /**
     * Decline Subscription step
     */
    public function run()
    {
        $this->dashboard->open();
        $this->dashboard->getSubscriptionForm()->enableCheckbox();
        $this->dashboard->getModalBlock()->dismissWarning();
    }
}
