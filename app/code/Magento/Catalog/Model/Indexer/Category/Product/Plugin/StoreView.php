<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Indexer\Category\Product\Plugin;

/**
 * Class \Magento\Catalog\Model\Indexer\Category\Product\Plugin\StoreView
 *
 * @since 2.0.0
 */
class StoreView extends StoreGroup
{
    /**
     * Validate changes for invalidating indexer
     *
     * @param \Magento\Framework\Model\AbstractModel $store
     * @return bool
     * @since 2.0.0
     */
    protected function validate(\Magento\Framework\Model\AbstractModel $store)
    {
        return $store->isObjectNew() || $store->dataHasChangedFor('group_id');
    }
}
