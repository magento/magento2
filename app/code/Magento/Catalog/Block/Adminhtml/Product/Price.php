<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */

/**
 * Product price block
 *
 */
namespace Magento\Catalog\Block\Adminhtml\Product;

class Price extends \Magento\Catalog\Block\Product\Price
{
    /**
     * @param null|string|bool|int|\Magento\Store\Model\Store $storeId
     * @return bool|\Magento\Store\Model\Website
     */
    public function getWebsite($storeId)
    {
        return $this->_storeManager->getStore($storeId)->getWebsite();
    }
}
