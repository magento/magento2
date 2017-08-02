<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model\Backend;

/**
 * Class \Magento\Customer\Model\Backend\Customer
 *
 * @since 2.0.0
 */
class Customer extends \Magento\Customer\Model\Customer
{
    /**
     * Get store id
     *
     * @return int
     * @since 2.0.0
     */
    public function getStoreId()
    {
        if ($this->getWebsiteId() * 1) {
            return $this->_getWebsiteStoreId();
        }
        return parent::getStoreId();
    }
}
