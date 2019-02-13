<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\User\Test\TestStep;

use Magento\Backend\Test\Page\AdminAuthLogin;
use Magento\Backend\Test\Page\Adminhtml\Dashboard;
use Magento\Mtf\Client\BrowserInterface;
use Magento\User\Test\Fixture\User;

/**
 * Login user on backend with access error.
 */
class LoginUserOnBackendWithErrorStep extends LoginUserOnBackendStep
{
    /**
     * @var CloseErrorAlertStep
     */
    private $closeErrorAlertStep;

    /**
     * @param LogoutUserOnBackendStep $logoutUserOnBackendStep
     * @param AdminAuthLogin $adminAuth
     * @param User $user
     * @param Dashboard $dashboard
     * @param BrowserInterface $browser
     */
    public function __construct(
        LogoutUserOnBackendStep $logoutUserOnBackendStep,
        AdminAuthLogin $adminAuth,
        User $user,
        Dashboard $dashboard,
        BrowserInterface $browser,
        CloseErrorAlertStep $closeErrorAlertStep
    ) {
        parent::__construct($logoutUserOnBackendStep, $adminAuth, $user, $dashboard, $browser);
        $this->closeErrorAlertStep = $closeErrorAlertStep;
    }

    /**
     * Run step flow.
     *
     * @return void
     */
    public function run()
    {
        parent::run();
        $this->closeErrorAlertStep->run();
    }
}
