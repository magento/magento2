<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Security\Model\ResourceModel\UserExpiration;

/**
 * Admin user expiration collection
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'user_id';

    /**
     * Initialize collection
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Magento\Security\Model\UserExpiration::class,
            \Magento\Security\Model\ResourceModel\UserExpiration::class
        );
    }

    /**
     * Filter for expired, active users.
     *
     * @return $this
     */
    public function addActiveExpiredUsersFilter(): Collection
    {
        $currentTime = new \DateTime();
        $currentTime->format('Y-m-d H:i:s');
        $this->getSelect()->joinLeft(
            ['user' => $this->getTable('admin_user')],
            'main_table.user_id = user.user_id',
            ['is_active']
        );
        $this->addFieldToFilter('expires_at', ['lt' => $currentTime])
            ->addFieldToFilter('user.is_active', 1);

        return $this;
    }

    /**
     * Filter collection by user id.
     *
     * @param int[] $userIds
     * @return Collection
     */
    public function addUserIdsFilter(array $userIds = []): Collection
    {
        return $this->addFieldToFilter('main_table.user_id', ['in' => $userIds]);
    }

    /**
     * Get any expired records for the given user.
     *
     * @param string $userId
     * @return Collection
     */
    public function addExpiredRecordsForUserFilter(string $userId): Collection
    {
        return $this->addActiveExpiredUsersFilter()
            ->addFieldToFilter('main_table.user_id', (int)$userId);
    }
}
