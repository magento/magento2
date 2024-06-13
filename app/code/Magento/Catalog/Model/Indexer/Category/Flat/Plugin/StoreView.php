<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Catalog\Model\Indexer\Category\Flat\Plugin;

class StoreView extends StoreGroup
{
    /**
     * Validate changes for invalidating indexer
     *
     * @param \Magento\Framework\Model\AbstractModel $store
     * @return bool
     */
    protected function validate(\Magento\Framework\Model\AbstractModel $store)
    {
        return $store->isObjectNew() || $store->dataHasChangedFor('group_id');
    }
}
