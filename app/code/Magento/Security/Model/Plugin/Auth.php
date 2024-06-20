<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Security\Model\Plugin;

use Magento\Backend\Model\Auth as BackendAuth;
use Magento\Framework\Message\ManagerInterface;
use Magento\Security\Model\AdminSessionsManager;

/**
 * Magento\Backend\Model\Auth decorator
 */
class Auth
{
    /**
     * @param AdminSessionsManager $sessionsManager
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        protected readonly AdminSessionsManager $sessionsManager,
        protected readonly ManagerInterface $messageManager
    ) {
    }

    /**
     * Add warning message if other sessions terminated
     *
     * @param BackendAuth $authModel
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterLogin(BackendAuth $authModel)
    {
        $this->sessionsManager->processLogin();
        if ($this->sessionsManager->getCurrentSession()->isOtherSessionsTerminated()) {
            $this->messageManager->addWarningMessage(__('All other open sessions for this account were terminated.'));
        }
    }

    /**
     * Handle logout process
     *
     * @param BackendAuth $authModel
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeLogout(BackendAuth $authModel)
    {
        $this->sessionsManager->processLogout();
    }
}
