<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\User\Test\TestStep;

use Magento\Backend\Test\Page\AdminAuthLogin;
use Magento\Mtf\TestStep\TestStepInterface;
use Magento\User\Test\Fixture\User;
use Magento\Backend\Test\Page\Adminhtml\Dashboard;

/**
 * Login user on backend.
 */
class LoginUserOnBackendStep implements TestStepInterface
{
    /**
     * Logout user on backend step.
     *
     * @var LogoutUserOnBackendStep
     */
    protected $logoutUserOnBackendStep;

    /**
     * Authorization backend page.
     *
     * @var AdminAuthLogin
     */
    protected $adminAuth;

    /**
     * Fixture User.
     *
     * @var User
     */
    protected $user;

    /**
     * Dashboard backend page.
     *
     * @var Dashboard
     */
    protected $dashboard;

    /**
     * @constructor
     * @param LogoutUserOnBackendStep $logoutUserOnBackendStep
     * @param AdminAuthLogin $adminAuth
     * @param User $user
     * @param Dashboard $dashboard
     */
    public function __construct(
        LogoutUserOnBackendStep $logoutUserOnBackendStep,
        AdminAuthLogin $adminAuth,
        User $user,
        Dashboard $dashboard
    ) {
        $this->logoutUserOnBackendStep = $logoutUserOnBackendStep;
        $this->adminAuth = $adminAuth;
        $this->user = $user;
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

        if (!$this->adminAuth->getLoginBlock()->isVisible()) {
            $this->logoutUserOnBackendStep->run();
        }

        $this->adminAuth->getLoginBlock()->fill($this->user);
        $this->adminAuth->getLoginBlock()->submit();
        $this->adminAuth->getLoginBlock()->waitFormNotVisible();

        $this->dashboard->getSystemMessageDialog()->closePopup();
    }
}
