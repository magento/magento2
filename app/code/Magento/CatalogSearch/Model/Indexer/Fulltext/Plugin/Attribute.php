<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Model\Indexer\Fulltext\Plugin;

use Magento\CatalogSearch\Model\Indexer\Fulltext;
use Magento\Framework\Model\AbstractModel;
use Magento\Catalog\Model\ResourceModel\Attribute as AttributeResourceModel;
use Magento\Framework\Search\Request\Config;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Catalog\Api\Data\EavAttributeInterface;

/**
 * Catalog search indexer plugin for catalog attribute.
 */
class Attribute extends AbstractPlugin
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var boolean
     */
    private $deleteNeedInvalidation;

    /**
     * @var boolean
     */
    private $saveNeedInvalidation;

    /**
     * @var boolean
     */
    private $saveIsNew;

    /**
     * @param IndexerRegistry $indexerRegistry
     * @param Config $config
     */
    public function __construct(
        IndexerRegistry $indexerRegistry,
        Config $config
    ) {
        parent::__construct($indexerRegistry);
        $this->config = $config;
    }

    /**
     * Check if indexer invalidation is needed on attribute save (searchable flag change)
     *
     * @param AttributeResourceModel $subject
     * @param AbstractModel $attribute
     *
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeSave(
        AttributeResourceModel $subject,
        AbstractModel $attribute
    ) {
        $this->saveIsNew = $attribute->isObjectNew();
        $this->saveNeedInvalidation = $this->shouldInvalidateSearchIndex($attribute);
    }

    /**
     * Invalidate indexer on attribute save (searchable flag change)
     *
     * @param AttributeResourceModel $subject
     * @param AttributeResourceModel $result
     *
     * @return AttributeResourceModel
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterSave(
        AttributeResourceModel $subject,
        AttributeResourceModel $result
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
     * @param AttributeResourceModel $subject
     * @param AbstractModel $attribute
     *
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeDelete(
        AttributeResourceModel $subject,
        AbstractModel $attribute
    ) {
        $this->deleteNeedInvalidation = !$attribute->isObjectNew() && $attribute->getIsSearchable();
    }

    /**
     * Invalidate indexer on searchable attribute delete
     *
     * @param AttributeResourceModel $subject
     * @param AttributeResourceModel $result
     *
     * @return AttributeResourceModel
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterDelete(
        AttributeResourceModel $subject,
        AttributeResourceModel $result
    ) {
        if ($this->deleteNeedInvalidation) {
            $this->indexerRegistry->get(Fulltext::INDEXER_ID)->invalidate();
        }
        return $result;
    }

    /**
     * Check if catalogsearch_fulltext index should be invalidated.
     *
     * @param AbstractModel $attribute
     * @return bool
     */
    private function shouldInvalidateSearchIndex(
        AbstractModel $attribute
    ):bool {
        $shouldInvalidate = false;
        $fields = [
            EavAttributeInterface::IS_SEARCHABLE,
            EavAttributeInterface::IS_FILTERABLE,
            EavAttributeInterface::IS_VISIBLE_IN_ADVANCED_SEARCH,
        ];
        foreach ($fields as $field) {
            if ($this->saveIsNew && $attribute->getData($field)
                || !$this->saveIsNew && $attribute->dataHasChangedFor($field)) {
                $shouldInvalidate = true;
                break;
            }
        }

        return $shouldInvalidate;
    }
}
