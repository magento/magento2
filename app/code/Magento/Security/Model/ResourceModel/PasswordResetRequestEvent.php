<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Security\Model\ResourceModel;

/**
 * Password reset request event mysql resource model
 * @since 2.1.0
 */
class PasswordResetRequestEvent extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime
     * @since 2.1.0
     */
    protected $dateTime;

    /**
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param null $connectionName
     * @since 2.1.0
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
     * @since 2.1.0
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
     * @since 2.1.0
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
