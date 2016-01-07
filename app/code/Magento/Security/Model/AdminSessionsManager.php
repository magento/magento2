<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Security\Model;

use \Magento\Security\Model\ResourceModel\AdminSessionInfo\CollectionFactory;

/**
 * Admin Sessions Manager Model
 */
class AdminSessionsManager
{
    /**
     * Admin Session lifetime (sec)
     */
    const ADMIN_SESSION_LIFETIME = 86400;

    /**
     * @var \Magento\Security\Helper\SecurityConfig
     */
    protected $securityConfig;

    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $authSession;

    /**
     * @var AdminSessionInfoFactory
     */
    protected $adminSessionInfoFactory;

    /**
     * @var ResourceModel\AdminSessionInfo
     */
    protected $adminSessionInfoResource;

    /**
     * @var \Magento\Security\Model\ResourceModel\AdminSessionInfo\CollectionFactory
     */
    protected $adminSessionInfoCollectionFactory;

    /**
     * @var \Magento\Security\Model\AdminSessionInfo
     */
    protected $currentSession;

    /**
     * @param \Magento\Security\Helper\SecurityConfig $securityConfig
     * @param \Magento\Backend\Model\Auth\Session $authSession
     * @param AdminSessionInfoFactory $adminSessionInfoFactory
     * @param ResourceModel\AdminSessionInfo $adminSessionInfoResource
     * @param CollectionFactory $adminSessionInfoCollectionFactory
     */
    public function __construct(
        \Magento\Security\Helper\SecurityConfig $securityConfig,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Security\Model\AdminSessionInfoFactory $adminSessionInfoFactory,
        \Magento\Security\Model\ResourceModel\AdminSessionInfo $adminSessionInfoResource,
        \Magento\Security\Model\ResourceModel\AdminSessionInfo\CollectionFactory $adminSessionInfoCollectionFactory
    ) {
        $this->securityConfig = $securityConfig;
        $this->authSession = $authSession;
        $this->adminSessionInfoFactory = $adminSessionInfoFactory;
        $this->adminSessionInfoResource = $adminSessionInfoResource;
        $this->adminSessionInfoCollectionFactory = $adminSessionInfoCollectionFactory;
    }

    /**
     * Handle all others active sessions according Sharing Account Setting
     *
     * @return $this
     */
    public function processLogin()
    {
        $this->createNewSession();

        $olderThen = $this->securityConfig->getCurrentTimestamp() - $this->securityConfig->getAdminSessionLifetime();
        if (!$this->securityConfig->isAdminAccountSharingEnabled()) {
            $result = $this->adminSessionInfoResource->updateStatusByUserId(
                AdminSessionInfo::LOGGED_OUT_BY_LOGIN,
                $this->getCurrentSession()->getUserId(),
                [AdminSessionInfo::LOGGED_IN],
                [$this->getCurrentSession()->getSessionId()],
                $olderThen
            );
            if ($result) {
                $this->currentSession->setIsOtherSessionsTerminated(true);
            }
        }

        return $this;
    }

    /**
     * Handle Prolong process
     *
     * @return $this
     */
    public function processProlong()
    {
        $this->getCurrentSession()->setData(
            'updated_at',
            $this->authSession->getUpdatedAt()
        );
        $this->getCurrentSession()->save();

        return $this;
    }

    /**
     * Handle logout process
     *
     * @return $this
     */
    public function processLogout()
    {
        $this->getCurrentSession()->setData(
            'status',
            AdminSessionInfo::LOGGED_OUT
        );
        $this->getCurrentSession()->save();

        return $this;
    }

    /**
     * Get current session record
     *
     * @return AdminSessionInfo
     */
    public function getCurrentSession()
    {
        if (!$this->currentSession) {
            $this->currentSession = $this->adminSessionInfoFactory->create();
            $this->currentSession->load($this->authSession->getSessionId(), 'session_id');
        }

        return $this->currentSession;
    }

    /**
     * Get message with explanation of logout reason
     *
     * @return string
     */
    public function getLogoutReasonMessage()
    {
        return $this->getLogoutReasonMessageByStatus(
            $this->getCurrentSession()->getStatus()
        );
    }

    /**
     * @param int $statusCode
     * @return string
     */
    public function getLogoutReasonMessageByStatus($statusCode)
    {
        switch ((int)$statusCode) {
            case AdminSessionInfo::LOGGED_IN:
                $reasonMessage = '';
                break;
            case AdminSessionInfo::LOGGED_OUT_BY_LOGIN:
                $reasonMessage = _(
                    'Someone logged into this account from another device or browser.'
                    .' Your current session is terminated.'
                );
                break;
            case AdminSessionInfo::LOGGED_OUT_MANUALLY:
                $reasonMessage = _(
                    'Your current session is terminated by another user of this account.'
                );
                break;
            default:
                $reasonMessage = _('Your current session has been expired.');
                break;
        }

        return $reasonMessage;
    }

    /**
     * Create new record
     *
     * @return $this
     */
    protected function createNewSession()
    {
        $this->currentSession = $this->adminSessionInfoFactory->create();
        $this->currentSession->setData(
            [
                'session_id' => $this->authSession->getSessionId(),
                'user_id' => $this->authSession->getUser()->getId(),
                'ip' => $this->securityConfig->getRemoteIp(),
                'status' => AdminSessionInfo::LOGGED_IN
            ]
        );
        $this->currentSession->save();

        return $this;
    }

    /**
     * Clean expired Admin Sessions
     *
     * @return $this
     */
    public function cleanExpiredSessions()
    {
        $this->adminSessionInfoResource->deleteSessionsOlderThen(
            $this->securityConfig->getCurrentTimestamp() - self::ADMIN_SESSION_LIFETIME
        );

        return $this;
    }

    /**
     * Get sessions for current user
     *
     * @return \Magento\Security\Model\ResourceModel\AdminSessionInfo\Collection
     */
    public function getSessionsForCurrentUser()
    {
        return $this->adminSessionInfoCollectionFactory->create()
            ->filterByUser($this->authSession->getUser()->getId(), \Magento\Security\Model\AdminSessionInfo::LOGGED_IN)
            ->filterExpiredSessions($this->securityConfig->getAdminSessionLifetime())
            ->loadData();
    }

    /**
     * Logout another user sessions
     *
     * @return $this
     */
    public function logoutAnotherUserSessions()
    {
        $collection = $this->adminSessionInfoCollectionFactory->create()
            ->filterByUser(
                $this->authSession->getUser()->getId(),
                \Magento\Security\Model\AdminSessionInfo::LOGGED_IN,
                $this->authSession->getSessionId()
            )
            ->filterExpiredSessions($this->securityConfig->getAdminSessionLifetime())
            ->loadData();

        $collection->setDataToAll('status', \Magento\Security\Model\AdminSessionInfo::LOGGED_OUT_MANUALLY)
                ->save();

        return $this;
    }
}
