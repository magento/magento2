<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Customer\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class ConfirmationLog extends AbstractDb
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('customer_confirmation_log', 'id');
    }
}
