<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LoginAsCustomerLog\Model\ResourceModel\Log;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\LoginAsCustomerLog\Model\Log;
use Magento\LoginAsCustomerLog\Model\ResourceModel\Log as LogResource;

/**
 * Login as customer log entities collection.
 */
class Collection extends AbstractCollection
{
    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init(Log::class, LogResource::class);
    }
}
