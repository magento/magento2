<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Customer\Model\Backend;

class Customer extends \Magento\Customer\Model\Customer
{
    /**
     * Get store id
     *
     * @return int
     */
    public function getStoreId()
    {
        if ($this->getWebsiteId() * 1) {
            return $this->_getWebsiteStoreId();
        }
        return parent::getStoreId();
    }
}
