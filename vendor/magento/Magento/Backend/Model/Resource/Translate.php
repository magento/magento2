<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Backend\Model\Resource;

/**
 * Backend translate resource model
 */
class Translate extends \Magento\Translation\Model\Resource\Translate
{
    /**
     * Get current store id
     * Use always default scope for store id
     *
     * @return int
     */
    protected function _getStoreId()
    {
        return \Magento\Store\Model\Store::DEFAULT_STORE_ID;
    }
}
