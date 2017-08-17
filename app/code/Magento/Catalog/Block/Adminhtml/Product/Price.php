<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Product price block
 *
 */
namespace Magento\Catalog\Block\Adminhtml\Product;

/**
 * Class \Magento\Catalog\Block\Adminhtml\Product\Price
 *
 */
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
