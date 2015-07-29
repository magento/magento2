<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\User\Test\TestStep;

use Magento\Backend\Test\Page\AdminAuthLogin;
use Magento\Backend\Test\Page\Adminhtml\Dashboard;
use Magento\Mtf\TestStep\TestStepInterface;

/**
 * Logout user on backend.
 */
class LogoutUserOnBackendStep implements TestStepInterface
{
    /**
     * Authorization backend page.
     *
     * @var AdminAuthLogin
     */
    protected $adminAuth;

    /**
     * Dashboard backend page.
     *
     * @var Dashboard
     */
    protected $dashboard;

    /**
     * @construct
     * @param AdminAuthLogin $adminAuth
     * @param Dashboard $dashboard
     */
    public function __construct(AdminAuthLogin $adminAuth, Dashboard $dashboard)
    {
        $this->adminAuth = $adminAuth;
        $this->dashboard = $dashboard;
    }

    /**
     * Run step flow.
     *
     * @return void
     */
    public function run()
    {
        $this->adminAuth->open();
        $this->dashboard->getSystemMessageDialog()->closePopup();
        $this->dashboard->getAdminPanelHeader()->logOut();
    }
}
