<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Security\Model;

use Magento\Security\Model\ResourceModel\UserExpiration\Collection as ExpiredUsersCollection;

/**
 * Class to handle expired admin users.
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
     *
     * @param array|null $userIds
     */
    public function deactivateExpiredUsers(?array $userIds = null): void
    {
        /** @var ExpiredUsersCollection $expiredRecords */
        $expiredRecords = $this->userExpirationCollectionFactory->create()->addActiveExpiredUsersFilter();
        if ($userIds != null) {
            $expiredRecords->addUserIdsFilter($userIds);
        }

        if ($expiredRecords->getSize() > 0) {
            // get all active sessions for the users and set them to logged out
            /** @var \Magento\Security\Model\ResourceModel\AdminSessionInfo\Collection $currentSessions */
            $currentSessions = $this->adminSessionInfoCollectionFactory->create()
                ->addFieldToFilter('user_id', ['in' => $expiredRecords->getAllIds()])
                ->filterExpiredSessions($this->securityConfig->getAdminSessionLifetime());
            $currentSessions->setDataToAll('status', \Magento\Security\Model\AdminSessionInfo::LOGGED_OUT)
                ->save();
        }

        // delete expired records
        $expiredRecordIds = $expiredRecords->getAllIds();
        $expiredRecords->walk('delete');

        // set user is_active to 0
        $users = $this->userCollectionFactory->create()
            ->addFieldToFilter('main_table.user_id', ['in' => $expiredRecordIds]);
        $users->setDataToAll('is_active', 0)->save();
    }

    /**
     * Check if the given user is expired.
     * // TODO: check users expired an hour ago (timezone stuff)
     * @param \Magento\User\Model\User $user
     * @return bool
     */
    public function userIsExpired(\Magento\User\Model\User $user): bool
    {
        $isExpired = false;
        $expiredRecord = $this->userExpirationCollectionFactory->create()
            ->addExpiredRecordsForUserFilter($user->getId())
            ->getFirstItem();
        if ($expiredRecord && $expiredRecord->getId()) {
            $expiresAt = $this->dateTime->timestamp($expiredRecord->getExpiresAt());
            $isExpired = $expiresAt < $this->dateTime->gmtTimestamp();
        }

        return $isExpired;
    }
}
