<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Security\Plugin;

use Magento\Backend\Controller\Adminhtml\Auth\Login;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;
use Magento\Framework\UrlInterface;
use Magento\Security\Model\AdminSessionsManager;
use Magento\Security\Model\SecurityCookie;

class BackendLoginControllerPlugin
{
    /**
     * @var MessageManagerInterface
     */
    private $messageManager;

    /**
     * @var AdminSessionsManager
     */
    private $sessionsManager;

    /**
     * @var SecurityCookie
     */
    private $securityCookie;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @param RequestInterface $request
     * @param UrlInterface $urlBuilder
     * @param MessageManagerInterface $messageManager
     * @param AdminSessionsManager $sessionsManager
     * @param SecurityCookie $securityCookie
     */
    public function __construct(
        RequestInterface $request,
        UrlInterface $urlBuilder,
        MessageManagerInterface $messageManager,
        AdminSessionsManager $sessionsManager,
        SecurityCookie $securityCookie
    ) {
        $this->messageManager = $messageManager;
        $this->sessionsManager = $sessionsManager;
        $this->securityCookie = $securityCookie;
        $this->request = $request;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * Before execute login
     *
     * @param Login $login
     * @return void
     */
    public function beforeExecute(Login $login)
    {
        $logoutReasonCode = $this->securityCookie->getLogoutReasonCookie();
        if ($this->isLoginForm($login) && $logoutReasonCode >= 0) {
            $this->messageManager->addErrorMessage(
                $this->sessionsManager->getLogoutReasonMessageByStatus($logoutReasonCode)
            );
            $this->securityCookie->deleteLogoutReasonCookie();
        }
    }

    /**
     * Check if the login form action is requested directly
     *
     * @param Login $login
     * @return bool
     */
    private function isLoginForm(Login $login)
    {
        return $this->request->getUri() == $this->urlBuilder->getUrl('*');
    }
}
