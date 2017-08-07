<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Model\Indexer\Fulltext\Plugin\Store;

use Magento\CatalogSearch\Model\Indexer\Fulltext\Plugin\AbstractPlugin as AbstractIndexerPlugin;
use Magento\Store\Model\ResourceModel\Group as StoreGroupResourceModel;
use Magento\Framework\Model\AbstractModel;
use Magento\CatalogSearch\Model\Indexer\Fulltext as FulltextIndexer;

/**
 * Plugin for Magento\Store\Model\ResourceModel\Group
 */
class Group extends AbstractIndexerPlugin
{
    /**
     * @var bool
     * @since 2.2.0
     */
    private $needInvalidation;

    /**
     * Check if indexer requires invalidation after store group save
     *
     * @param StoreGroupResourceModel $subject
     * @param AbstractModel $group
     * @return void
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.2.0
     */
    public function beforeSave(StoreGroupResourceModel $subject, AbstractModel $group)
    {
        $this->needInvalidation = !$group->isObjectNew() && $group->dataHasChangedFor('website_id');
    }

    /**
     * Invalidate indexer on store group save
     *
     * @param StoreGroupResourceModel $subject
     * @param StoreGroupResourceModel $result
     * @return StoreGroupResourceModel
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.2.0
     */
    public function afterSave(StoreGroupResourceModel $subject, StoreGroupResourceModel $result)
    {
        if ($this->needInvalidation) {
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
