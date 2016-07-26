<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Model\Indexer\Fulltext\Plugin\Store;

use Magento\CatalogSearch\Model\Indexer\Fulltext;
use Magento\CatalogSearch\Model\Indexer\Fulltext\Plugin\AbstractPlugin;

class View extends AbstractPlugin
{
    /**
     * @var bool
     */
    private $needInvalidation;

    /**
     * Check if indexer requires invalidation after store view save
     *
     * @param \Magento\Store\Model\ResourceModel\Store $subject
     * @param \Magento\Framework\Model\AbstractModel $store
     * @return void
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeSave(
        \Magento\Store\Model\ResourceModel\Store $subject,
        \Magento\Framework\Model\AbstractModel $store
    ) {
        $this->needInvalidation = $store->isObjectNew();
    }

    /**
     * Invalidate indexer on store view save
     *
     * @param \Magento\Store\Model\ResourceModel\Store $subject
     * @param \Magento\Store\Model\ResourceModel\Store $result
     * @return \Magento\Store\Model\ResourceModel\Store
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterSave(
        \Magento\Store\Model\ResourceModel\Store $subject,
        \Magento\Store\Model\ResourceModel\Store $result
    ) {
        if ($this->needInvalidation) {
            $this->indexerRegistry->get(Fulltext::INDEXER_ID)->invalidate();
        }

        return $result;
    }

    /**
     * Invalidate indexer on store view delete
     *
     * @param \Magento\Store\Model\ResourceModel\Store $subject
     * @param \Magento\Store\Model\ResourceModel\Store $result
     *
     * @return \Magento\Store\Model\ResourceModel\Store
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterDelete(
        \Magento\Store\Model\ResourceModel\Store $subject,
        \Magento\Store\Model\ResourceModel\Store $result
    ) {
        $this->indexerRegistry->get(Fulltext::INDEXER_ID)->invalidate();
        return $result;
    }
}
