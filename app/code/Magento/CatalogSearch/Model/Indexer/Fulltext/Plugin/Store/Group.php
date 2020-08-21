<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Model\Indexer\Fulltext\Plugin\Store;

use Magento\CatalogSearch\Model\Indexer\Fulltext as FulltextIndexer;
use Magento\CatalogSearch\Model\Indexer\Fulltext\Plugin\AbstractPlugin as AbstractIndexerPlugin;
use Magento\Framework\Model\AbstractModel;
use Magento\Store\Model\ResourceModel\Group as StoreGroupResourceModel;

/**
 * Plugin for Magento\Store\Model\ResourceModel\Group
 */
class Group extends AbstractIndexerPlugin
{
    /**
     * Invalidate indexer on store group save
     *
     * @param StoreGroupResourceModel $subject
     * @param StoreGroupResourceModel $result
     * @param AbstractModel $group
     * @return StoreGroupResourceModel
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterSave(StoreGroupResourceModel $subject, StoreGroupResourceModel $result, AbstractModel $group)
    {
        if (!$group->isObjectNew() && $group->dataHasChangedFor('website_id')) {
            $this->indexerRegistry->get(FulltextIndexer::INDEXER_ID)->invalidate();
        }

        return $result;
    }

    /**
     * Invalidate indexer on store group delete
     *
     * @param StoreGroupResourceModel $subject
     * @param StoreGroupResourceModel $result
     * @return StoreGroupResourceModel
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterDelete(StoreGroupResourceModel $subject, StoreGroupResourceModel $result)
    {
        $this->indexerRegistry->get(FulltextIndexer::INDEXER_ID)->invalidate();

        return $result;
    }
}
