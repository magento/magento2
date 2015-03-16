<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\User\Test\TestStep;

use Magento\Backend\Test\Page\AdminAuthLogin;
use Magento\Mtf\TestStep\TestStepInterface;
use Magento\User\Test\Fixture\User;

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
     * @constructor
     * @param LogoutUserOnBackendStep $logoutUserOnBackendStep
     * @param AdminAuthLogin $adminAuth
     * @param User $user
     */
    public function __construct(
        LogoutUserOnBackendStep $logoutUserOnBackendStep,
        AdminAuthLogin $adminAuth,
        User $user
    ) {
        $this->logoutUserOnBackendStep = $logoutUserOnBackendStep;
        $this->adminAuth = $adminAuth;
        $this->user = $user;
    }

    /**
     * Run step flow.
     *
     * @return void
     */
    public function run()
    {
        $this->logoutUserOnBackendStep->run();

        $this->adminAuth->open();
        $this->adminAuth->getLoginBlock()->fill($this->user);
        $this->adminAuth->getLoginBlock()->submit();
        $this->adminAuth->getLoginBlock()->waitFormNotVisible();
    }
}
