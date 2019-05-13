<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
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
    public function getCustomerId()
    {
        return $this->getData('customer_id');
    }

    /**
     * Get Timestamp
     *
     * @return int
     */
    public function getTimestamp()
    {
        return $this->getData('time_stamp');
    }

    /**
     * Get Fromstore
     *
     * @return string
     */
    public function getFromStoreCode()
    {
        return $this->getData('___from_store');
    }
}
