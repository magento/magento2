<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Security\Model\Plugin;

use Magento\Backend\Model\Auth\Session;
use Magento\Security\Model\AdminSessionsManager;

/**
 * Magento\Backend\Model\Auth\Session decorator
 */
class AuthSession
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var AdminSessionsManager
     */
    protected $sessionsManager;

    /**
     * AuthSession constructor.
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param AdminSessionsManager $sessionsManager
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        AdminSessionsManager $sessionsManager
    ) {
        $this->request = $request;
        $this->messageManager = $messageManager;
        $this->sessionsManager = $sessionsManager;
    }

    /**
     * Admin Session prolong functionality
     *
     * @param Session $session
     * @param \Closure $proceed
     * @return mixed
     */
    public function aroundProlong(Session $session, \Closure $proceed)
    {
        if (!$this->sessionsManager->getCurrentSession()->isActive() && !$this->isAjaxRequest()) {
            $session->destroy();
            $this->messageManager->addError(
                $this->sessionsManager->getLogoutReasonMessage()
            );
        } elseif (!$this->isSessionCheckRequest()) {
            $result = $proceed();
            $this->sessionsManager->processProlong();
            return $result;
        }
    }

    /**
     * @return bool
     */
    protected function isSessionCheckRequest()
    {
        return $this->request->getModuleName() == 'security' && $this->request->getActionName() == 'check';
    }

    /**
     * @return bool
     */
    protected function isAjaxRequest()
    {
        return (bool) $this->request->getParam('isAjax');
    }
}
