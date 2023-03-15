<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Security\Model\ResourceModel;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context as DbContext;
use Magento\Framework\Stdlib\DateTime;

/**
 * Password reset request event mysql resource model
 */
class PasswordResetRequestEvent extends AbstractDb
{
    /**
     * @param DbContext $context
     * @param DateTime $dateTime
     * @param null $connectionName
     */
    public function __construct(
        DbContext $context,
        protected readonly DateTime $dateTime,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('password_reset_request_event', 'id');
    }

    /**
     * Delete records which has been created earlier than specified timestamp
     *
     * @param int $timestamp
     * @return $this
     * @throws LocalizedException
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
