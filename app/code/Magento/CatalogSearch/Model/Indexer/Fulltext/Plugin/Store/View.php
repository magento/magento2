<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Model\Indexer\Fulltext\Plugin\Store;

use Magento\CatalogSearch\Model\Indexer\Fulltext\Plugin\AbstractPlugin as AbstractIndexerPlugin;
use Magento\Store\Model\ResourceModel\Store as StoreResourceModel;
use Magento\Framework\Model\AbstractModel;
use Magento\CatalogSearch\Model\Indexer\Fulltext as FulltextIndexer;

/**
 * Plugin for Magento\Store\Model\ResourceModel\Store
 */
class View extends AbstractIndexerPlugin
{
    /**
     * @var bool
     */
    private $needInvalidation;

    /**
     * Check if indexer requires invalidation after store view save
     *
     * @param StoreResourceModel $subject
     * @param AbstractModel $store
     * @return void
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeSave(StoreResourceModel $subject, AbstractModel $store)
    {
        $this->needInvalidation = $store->isObjectNew();
    }

    /**
     * Invalidate indexer on store view save
     *
     * @param StoreResourceModel $subject
     * @param StoreResourceModel $result
     * @return StoreResourceModel
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterSave(StoreResourceModel $subject, StoreResourceModel $result)
    {
        if ($this->needInvalidation) {
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
