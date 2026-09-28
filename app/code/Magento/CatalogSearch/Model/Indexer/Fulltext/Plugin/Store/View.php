<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\CatalogSearch\Model\Indexer\Fulltext\Plugin\Store;

use Magento\CatalogSearch\Model\Indexer\Fulltext as FulltextIndexer;
use Magento\CatalogSearch\Model\Indexer\Fulltext\Plugin\AbstractPlugin as AbstractIndexerPlugin;
use Magento\Framework\Model\AbstractModel;
use Magento\Store\Model\ResourceModel\Store as StoreResourceModel;

/**
 * Plugin for Magento\Store\Model\ResourceModel\Store
 */
class View extends AbstractIndexerPlugin
{
    /**
     * Invalidate indexer on store view save
     *
     * @param StoreResourceModel $subject
     * @param StoreResourceModel $result
     * @param AbstractModel $store
     * @return StoreResourceModel
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterSave(StoreResourceModel $subject, StoreResourceModel $result, AbstractModel $store)
    {
        if ($store->isObjectNew()) {
            $this->indexerRegistry->get(FulltextIndexer::INDEXER_ID)->invalidate();
        }

        return $result;
    }

    /**
     * Invalidate indexer on store view delete
     *
     * @param StoreResourceModel $subject
     * @param StoreResourceModel $result
     * @return StoreResourceModel
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterDelete(StoreResourceModel $subject, StoreResourceModel $result)
    {
        $this->indexerRegistry->get(FulltextIndexer::INDEXER_ID)->invalidate();

        return $result;
    }
}
