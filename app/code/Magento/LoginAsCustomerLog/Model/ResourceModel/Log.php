<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LoginAsCustomerLog\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\LoginAsCustomerLog\Api\Data\LogInterface;

/**
 * Todo: add description.
 */
class Log extends AbstractDb
{
    const TABLE_NAME_LOG = 'magento_login_as_customer_log';

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init(self::TABLE_NAME_LOG, LogInterface::LOG_ID);
    }
}
