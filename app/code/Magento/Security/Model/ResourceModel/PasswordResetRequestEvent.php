<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Security\Model\ResourceModel;

/**
 * Password reset request event mysql resource model
 */
class PasswordResetRequestEvent extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
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
        $this->_init(\Magento\Security\Setup\InstallSchema::PASSWORD_RESET_REQUEST_EVENT_DB_TABLE_NAME, 'id');
    }

    /**
     * Delete records which has been created earlier than specified timestamp
     *
     * @param int $timestamp
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteRecordsOlderThen($timestamp)
    {
        $this->getConnection()->delete(
            $this->getMainTable(),
            ['created_at < ?' => $this->dateTime->formatDate($timestamp)]
        );

        return $this;
    }
}
