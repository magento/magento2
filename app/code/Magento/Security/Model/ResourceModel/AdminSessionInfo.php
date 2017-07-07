<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Security\Model\ResourceModel;

/**
 * Admin Session Info mysql resource
 *
 * @api
 */
class AdminSessionInfo extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    protected $dateTime;

    /**
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param null $connectionName
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
     */
    protected function _construct()
    {
        $this->_init(\Magento\Security\Setup\InstallSchema::ADMIN_SESSIONS_DB_TABLE_NAME, 'id');
    }

    /**
     * Delete records which updated earlier than specified timestamp
     *
     * @param int $timestamp
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
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
     */
    public function updateStatusByUserId(
        $status,
        $userId,
        array $withStatuses = [],
        array $excludedSessionIds = [],
        $updateOlderThen = null
    ) {
        $whereStatement = [
            'updated_at > ?' => $this->dateTime->formatDate($updateOlderThen),
            'user_id = ?' => (int) $userId,
        ];
        if (!empty($excludedSessionIds)) {
            $whereStatement['session_id NOT IN (?)'] = $excludedSessionIds;
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
