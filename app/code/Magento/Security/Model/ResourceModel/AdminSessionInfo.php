<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Security\Model\ResourceModel;

/**
 * Admin Session Info mysql resource
 *
 * @api
 * @since 100.1.0
 */
class AdminSessionInfo extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime
     * @since 100.1.0
     */
    protected $dateTime;

    /**
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param null|string $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->dateTime = $dateTime;
    }

    /**
     * Initialize resource model
     *
     * @return void
     * @since 100.1.0
     */
    protected function _construct()
    {
        $this->_init('admin_user_session', 'id');
    }

    /**
     * Delete records which updated earlier than specified timestamp
     *
     * @param int $timestamp
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     * @since 100.1.0
     */
    public function deleteSessionsOlderThen($timestamp)
    {
        $this->getConnection()->delete(
            $this->getMainTable(),
            ['updated_at < ?' => $this->dateTime->formatDate($timestamp)]
        );

        return $this;
    }

    /**
     * Update status by user ID
     *
     * @param int $status
     * @param int $userId
     * @param array $withStatuses
     * @param array $excludedSessionIds
     * @param int|null $updateOlderThen
     * @return int The number of affected rows.
     * @throws \Magento\Framework\Exception\LocalizedException
     * @since 100.1.0
     */
    public function updateStatusByUserId(
        $status,
        $userId,
        array $withStatuses = [],
        array $excludedSessionIds = [],
        $updateOlderThen = null
    ) {
        $whereStatement = [
            'user_id = ?' => (int) $userId,
        ];
        if ($updateOlderThen) {
            $whereStatement['updated_at > ?'] = $this->dateTime->formatDate($updateOlderThen);
        }
        if (!empty($excludedSessionIds)) {
            $whereStatement['id NOT IN (?)'] = $excludedSessionIds;
        }
        if (!empty($withStatuses)) {
            $whereStatement['status IN (?)'] = $withStatuses;
        }

        return $this->getConnection()->update(
            $this->getMainTable(),
            ['status' => (int) $status],
            $whereStatement
        );
    }
}
