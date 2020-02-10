<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\User\Test\TestStep;

use Magento\Backend\Test\Page\AdminAuthLogin;
use Magento\Backend\Test\Page\Adminhtml\Dashboard;

/**
 * Logout user on backend with access error.
 */
class LogoutUserOnBackendWithErrorStep extends LogoutUserOnBackendStep
{
    /**
     * @var CloseErrorAlertStep
     */
    private $closeErrorAlertStep;

    public function __construct(
        AdminAuthLogin $adminAuth,
        Dashboard $dashboard,
        CloseErrorAlertStep $closeErrorAlertStep
    ) {
        parent::__construct($adminAuth, $dashboard);
        $this->closeErrorAlertStep = $closeErrorAlertStep;
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        $this->adminAuth->open();
        $this->closeErrorAlertStep->run();
        $this->dashboard->getAdminPanelHeader()->logOut();
    }
}
