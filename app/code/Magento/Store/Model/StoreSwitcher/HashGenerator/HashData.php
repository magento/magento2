<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Store\Model\StoreSwitcher\HashGenerator;

use Magento\Framework\DataObject;

/**
 * HashData object for one time token
 */
class HashData extends DataObject
{
    /**
     * Get CustomerId
     *
     * @return int
     */
    public function getCustomerId(): int
    {
        return (int)$this->getData('customer_id');
    }

    /**
     * Get Timestamp
     *
     * @return int
     */
    public function getTimestamp(): int
    {
        return (int)$this->getData('time_stamp');
    }

    /**
     * Get Fromstore
     *
     * @return string
     */
    public function getFromStoreCode(): string
    {
        return (string)$this->getData('___from_store');
    }
}
