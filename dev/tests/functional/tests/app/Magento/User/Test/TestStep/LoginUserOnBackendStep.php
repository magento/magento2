<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\User\Test\TestStep;

use Magento\Backend\Test\Page\AdminAuthLogin;
use Magento\Mtf\Client\BrowserInterface;
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
     * Browser.
     *
     * @var BrowserInterface
     */
    private $browser;

    /**
     * Array of error messages on admin login form.
     *
     * @var array
     */
    private $errorMessages = [
        'Invalid Form Key. Please refresh the page.',
        'Your current session has been expired.',
    ];

    /**
     * @constructor
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
        BrowserInterface $browser
    ) {
        $this->logoutUserOnBackendStep = $logoutUserOnBackendStep;
        $this->adminAuth = $adminAuth;
        $this->user = $user;
        $this->dashboard = $dashboard;
        $this->browser = $browser;
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

        try {
            $this->login();
        } catch (\PHPUnit_Extensions_Selenium2TestCase_WebDriverException $e) {
            if (strpos($e->getMessage(), 'Timed out after') !== false) {
                $messages = $this->adminAuth->getMessagesBlock();
                if (in_array($messages->getErrorMessage(), $this->errorMessages, true)) {
                    $this->browser->refresh();
                    $this->login();
                }
            }
        }

        $this->dashboard->getSystemMessageDialog()->closePopup();
    }

    private function login()
    {
        $this->adminAuth->getLoginBlock()->fill($this->user);
        $this->adminAuth->getLoginBlock()->submit();
        $this->adminAuth->getLoginBlock()->waitFormNotVisible();
    }
}
