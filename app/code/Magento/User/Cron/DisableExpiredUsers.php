<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\User\Cron;

use Magento\Security\Model\AdminSessionsManager;

/**
 * Disable expired users.
 */
class DisableExpiredUsers
{

    /**
     * @var \Magento\User\Model\ResourceModel\User\CollectionFactory
     */
    private $userCollectionFactory;
    /**
     * @var \Magento\Security\Model\ResourceModel\AdminSessionInfo\CollectionFactory
     */
    private $adminSessionCollectionFactory;
    /**
     * @var \Magento\Security\Model\ConfigInterface
     */
    private $securityConfig;

    /**
     * @param \Magento\User\Model\ResourceModel\User\CollectionFactory $userCollectionFactory
     * @param \Magento\Security\Model\ResourceModel\AdminSessionInfo\CollectionFactory $adminSessionCollectionFactory
     * @param \Magento\Security\Model\ConfigInterface $securityConfig
     */
    public function __construct(
        \Magento\User\Model\ResourceModel\User\CollectionFactory $userCollectionFactory,
        \Magento\Security\Model\ResourceModel\AdminSessionInfo\CollectionFactory $adminSessionCollectionFactory,
        \Magento\Security\Model\ConfigInterface $securityConfig
    ) {
        $this->userCollectionFactory = $userCollectionFactory;
        $this->adminSessionCollectionFactory = $adminSessionCollectionFactory;
        $this->securityConfig = $securityConfig;
    }

    /**
     * Disable all expired user accounts and invalidate their sessions.
     */
    public function execute()
    {
        /** @var \Magento\User\Model\ResourceModel\User\Collection $users */
        $users = $this->userCollectionFactory->create()
            ->addActiveExpiredUsersFilter();

        if ($users->getSize() > 0) {
            /** @var \Magento\Security\Model\ResourceModel\AdminSessionInfo\Collection $currentSessions */
            $currentSessions = $this->adminSessionCollectionFactory->create()
                ->addFieldToFilter('user_id', ['in' => $users->getAllIds()])
                ->addFieldToFilter('status', \Magento\Security\Model\AdminSessionInfo::LOGGED_IN)
                ->filterExpiredSessions($this->securityConfig->getAdminSessionLifetime());
            $currentSessions->setDataToAll('status', AdminSessionsManager::LOGOUT_REASON_USER_EXPIRED)
                ->save();
        }

        $users->setDataToAll('expires_at', null)
            ->setDataToAll('is_active', 0)
            ->save();
    }
}
