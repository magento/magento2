<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Model\ResourceModel;

/**
 * Backend translate resource model
 * @api
 * @since 2.0.0
 */
class Translate extends \Magento\Translation\Model\ResourceModel\Translate
{
    /**
     * Get current store id
     * Use always default scope for store id
     *
     * @return int
     * @since 2.0.0
     */
    protected function _getStoreId()
    {
        return \Magento\Store\Model\Store::DEFAULT_STORE_ID;
    }
}
