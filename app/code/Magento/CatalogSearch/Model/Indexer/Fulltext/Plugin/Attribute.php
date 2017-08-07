<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Model\Indexer\Fulltext\Plugin;

use Magento\CatalogSearch\Model\Indexer\Fulltext;

/**
 * Class \Magento\CatalogSearch\Model\Indexer\Fulltext\Plugin\Attribute
 *
 */
class Attribute extends AbstractPlugin
{
    /**
     * @var \Magento\Framework\Search\Request\Config
     */
    private $config;

    /**
     * @var boolean
     * @since 2.2.0
     */
    private $deleteNeedInvalidation;

    /**
     * @var boolean
     * @since 2.2.0
     */
    private $saveNeedInvalidation;

    /**
     * @var boolean
     * @since 2.2.0
     */
    private $saveIsNew;

    /**
     * @param \Magento\Framework\Indexer\IndexerRegistry $indexerRegistry
     * @param \Magento\Framework\Search\Request\Config $config
     */
    public function __construct(
        \Magento\Framework\Indexer\IndexerRegistry $indexerRegistry,
        \Magento\Framework\Search\Request\Config $config
    ) {
        parent::__construct($indexerRegistry);
        $this->config = $config;
    }

    /**
     * Check if indexer invalidation is needed on attribute save (searchable flag change)
     *
     * @param \Magento\Catalog\Model\ResourceModel\Attribute $subject
     * @param \Magento\Framework\Model\AbstractModel $attribute
     *
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.2.0
     */
    public function beforeSave(
        \Magento\Catalog\Model\ResourceModel\Attribute $subject,
        \Magento\Framework\Model\AbstractModel $attribute
    ) {
        $this->saveIsNew = $attribute->isObjectNew();
        $this->saveNeedInvalidation = (
                $attribute->dataHasChangedFor('is_searchable')
                || $attribute->dataHasChangedFor('is_filterable')
                || $attribute->dataHasChangedFor('is_visible_in_advanced_search')
            ) && ! $this->saveIsNew;
    }

    /**
     * Invalidate indexer on attribute save (searchable flag change)
     *
     * @param \Magento\Catalog\Model\ResourceModel\Attribute $subject
     * @param \Magento\Catalog\Model\ResourceModel\Attribute $result
     *
     * @return \Magento\Catalog\Model\ResourceModel\Attribute
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.2.0
     */
    public function afterSave(
        \Magento\Catalog\Model\ResourceModel\Attribute $subject,
        \Magento\Catalog\Model\ResourceModel\Attribute $result
    ) {
        if ($this->saveNeedInvalidation) {
            $this->indexerRegistry->get(Fulltext::INDEXER_ID)->invalidate();
        }
        if ($this->saveIsNew || $this->saveNeedInvalidation) {
            $this->config->reset();
        }

        return $result;
    }

    /**
     * Check if indexer invalidation is needed on searchable attribute delete
     *
     * @param \Magento\Catalog\Model\ResourceModel\Attribute $subject
     * @param \Magento\Framework\Model\AbstractModel $attribute
     *
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.2.0
     */
    public function beforeDelete(
        \Magento\Catalog\Model\ResourceModel\Attribute $subject,
        \Magento\Framework\Model\AbstractModel $attribute
    ) {
        $this->deleteNeedInvalidation = !$attribute->isObjectNew() && $attribute->getIsSearchable();
    }

    /**
     * Invalidate indexer on searchable attribute delete
     *
     * @param \Magento\Catalog\Model\ResourceModel\Attribute $subject
     * @param \Magento\Catalog\Model\ResourceModel\Attribute $result
     *
     * @return \Magento\Catalog\Model\ResourceModel\Attribute
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.2.0
     */
    public function afterDelete(
        \Magento\Catalog\Model\ResourceModel\Attribute $subject,
        \Magento\Catalog\Model\ResourceModel\Attribute $result
    ) {
        if ($this->deleteNeedInvalidation) {
            $this->indexerRegistry->get(Fulltext::INDEXER_ID)->invalidate();
        }
        return $result;
    }
}
