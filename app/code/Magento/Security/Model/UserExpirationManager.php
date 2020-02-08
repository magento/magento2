<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Security\Model;

use Magento\Security\Model\ResourceModel\UserExpiration\Collection as ExpiredUsersCollection;

/**
 * Class to handle admin user expirations. Temporary admin users can be created with an expiration
 * date that, once past, will not allow them to login to the admin. A cron is run to periodically check for expired
 * users and, if found, will deactivate them.
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class UserExpirationManager
{

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    private $dateTime;

    /**
     * @var ConfigInterface
     */
    private $securityConfig;

    /**
     * @var ResourceModel\AdminSessionInfo\CollectionFactory
     */
    private $adminSessionInfoCollectionFactory;

    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    private $authSession;

    /**
     * @var ResourceModel\UserExpiration\CollectionFactory
     */
    private $userExpirationCollectionFactory;

    /**
     * @var \Magento\User\Model\ResourceModel\User\CollectionFactory
     */
    private $userCollectionFactory;

    /**
     * UserExpirationManager constructor.
     *
     * @param \Magento\Backend\Model\Auth\Session $authSession
     * @param ConfigInterface $securityConfig
     * @param ResourceModel\AdminSessionInfo\CollectionFactory $adminSessionInfoCollectionFactory
     * @param ResourceModel\UserExpiration\CollectionFactory $userExpirationCollectionFactory
     * @param \Magento\User\Model\ResourceModel\User\CollectionFactory $userCollectionFactory
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
     */
    public function __construct(
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Security\Model\ConfigInterface $securityConfig,
        \Magento\Security\Model\ResourceModel\AdminSessionInfo\CollectionFactory $adminSessionInfoCollectionFactory,
        \Magento\Security\Model\ResourceModel\UserExpiration\CollectionFactory $userExpirationCollectionFactory,
        \Magento\User\Model\ResourceModel\User\CollectionFactory $userCollectionFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
    ) {
        $this->dateTime = $dateTime;
        $this->securityConfig = $securityConfig;
        $this->adminSessionInfoCollectionFactory = $adminSessionInfoCollectionFactory;
        $this->authSession = $authSession;
        $this->userExpirationCollectionFactory = $userExpirationCollectionFactory;
        $this->userCollectionFactory = $userCollectionFactory;
    }

    /**
     * Deactivate expired user accounts and invalidate their sessions.
     */
    public function deactivateExpiredUsers(): void
    {
        /** @var ExpiredUsersCollection $expiredRecords */
        $expiredRecords = $this->userExpirationCollectionFactory->create()->addActiveExpiredUsersFilter();
        $this->processExpiredUsers($expiredRecords);
    }

    /**
     * Deactivate specific expired users.
     *
     * @param array $userIds
     */
    public function deactivateExpiredUsersById(array $userIds): void
    {
        $expiredRecords = $this->userExpirationCollectionFactory->create()
            ->addActiveExpiredUsersFilter()
            ->addUserIdsFilter($userIds);
        $this->processExpiredUsers($expiredRecords);
    }

    /**
     * Deactivate expired user accounts and invalidate their sessions.
     *
     * @param ExpiredUsersCollection $expiredRecords
     */
    private function processExpiredUsers(ExpiredUsersCollection $expiredRecords): void
    {
        if ($expiredRecords->getSize() > 0) {
            // get all active sessions for the users and set them to logged out
            /** @var \Magento\Security\Model\ResourceModel\AdminSessionInfo\Collection $currentSessions */
            $currentSessions = $this->adminSessionInfoCollectionFactory->create()
                ->addFieldToFilter('user_id', ['in' => $expiredRecords->getAllIds()])
                ->filterExpiredSessions($this->securityConfig->getAdminSessionLifetime());
            /** @var \Magento\Security\Model\AdminSessionInfo $currentSession */
            $currentSessions->setDataToAll('status', \Magento\Security\Model\AdminSessionInfo::LOGGED_OUT)
                ->save();
        }

        // delete expired records
        $expiredRecordIds = $expiredRecords->getAllIds();

        // set user is_active to 0
        $users = $this->userCollectionFactory->create()
            ->addFieldToFilter('main_table.user_id', ['in' => $expiredRecordIds]);
        $users->setDataToAll('is_active', 0)->save();
        $expiredRecords->walk('delete');
    }

    /**
     * Check if the given user is expired.
     *
     * @param string $userId
     * @return bool
     */
    public function isUserExpired(string $userId): bool
    {
        $isExpired = false;
        /** @var \Magento\Security\Model\UserExpirationInterface $expiredRecord */
        $expiredRecord = $this->userExpirationCollectionFactory->create()
            ->addExpiredRecordsForUserFilter($userId)
            ->getFirstItem();
        if ($expiredRecord && $expiredRecord->getId()) {
            $expiresAt = $this->dateTime->timestamp($expiredRecord->getExpiresAt());
            $isExpired = $expiresAt < $this->dateTime->gmtTimestamp();
        }

        return $isExpired;
    }
}
