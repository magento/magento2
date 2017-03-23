<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Catalog\Model\Product;

/**
 * Catalog Product Mass Action processing model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Action extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Product website factory
     *
     * @var \Magento\Catalog\Model\Product\WebsiteFactory
     */
    protected $_productWebsiteFactory;

    /** @var \Magento\Framework\Indexer\IndexerRegistry */
    protected $indexerRegistry;

    /**
     * @var \Magento\Eav\Model\Config
     */
    protected $_eavConfig;

    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Eav\Processor
     */
    protected $_productEavIndexerProcessor;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param WebsiteFactory $productWebsiteFactory
     * @param \Magento\Framework\Indexer\IndexerRegistry $indexerRegistry
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Catalog\Model\Indexer\Product\Eav\Processor $productEavIndexerProcessor
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Model\Product\WebsiteFactory $productWebsiteFactory,
        \Magento\Framework\Indexer\IndexerRegistry $indexerRegistry,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Catalog\Model\Indexer\Product\Eav\Processor $productEavIndexerProcessor,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_productWebsiteFactory = $productWebsiteFactory;
        $this->indexerRegistry = $indexerRegistry;
        $this->_eavConfig = $eavConfig;
        $this->_productEavIndexerProcessor = $productEavIndexerProcessor;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Magento\Catalog\Model\ResourceModel\Product\Action::class);
    }

    /**
     * Retrieve resource instance wrapper
     *
     * @return \Magento\Catalog\Model\ResourceModel\Product\Action
     */
    protected function _getResource()
    {
        return parent::_getResource();
    }

    /**
     * Update attribute values for entity list per store
     *
     * @param array $productIds
     * @param array $attrData
     * @param int $storeId
     * @return $this
     */
    public function updateAttributes($productIds, $attrData, $storeId)
    {
        $this->_eventManager->dispatch(
            'catalog_product_attribute_update_before',
            ['attributes_data' => &$attrData, 'product_ids' => &$productIds, 'store_id' => &$storeId]
        );

        $this->_getResource()->updateAttributes($productIds, $attrData, $storeId);
        $this->setData(
            ['product_ids' => array_unique($productIds), 'attributes_data' => $attrData, 'store_id' => $storeId]
        );

        if ($this->_hasIndexableAttributes($attrData)) {
            $this->_productEavIndexerProcessor->reindexList(array_unique($productIds));
        }

        $categoryIndexer = $this->indexerRegistry->get(\Magento\Catalog\Model\Indexer\Product\Category::INDEXER_ID);
        if (!$categoryIndexer->isScheduled()) {
            $categoryIndexer->reindexList(array_unique($productIds));
        }
        return $this;
    }

    /**
     * Attributes array has indexable attributes
     *
     * @param array $attributesData
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    protected function _hasIndexableAttributes($attributesData)
    {
        foreach ($attributesData as $code => $value) {
            if ($this->_attributeIsIndexable($code)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check is attribute indexable in EAV
     *
     * @param \Magento\Catalog\Model\ResourceModel\Eav\Attribute|string $attribute
     * @return bool
     */
    protected function _attributeIsIndexable($attribute)
    {
        if (!$attribute instanceof \Magento\Catalog\Model\ResourceModel\Eav\Attribute) {
            $attribute = $this->_eavConfig->getAttribute(\Magento\Catalog\Model\Product::ENTITY, $attribute);
        }

        return $attribute->isIndexable();
    }

    /**
     * Update websites for product action
     *
     * Allowed types:
     * - add
     * - remove
     *
     * @param array $productIds
     * @param array $websiteIds
     * @param string $type
     * @return void
     */
    public function updateWebsites($productIds, $websiteIds, $type)
    {
        if ($type == 'add') {
            $this->_productWebsiteFactory->create()->addProducts($websiteIds, $productIds);
        } elseif ($type == 'remove') {
            $this->_productWebsiteFactory->create()->removeProducts($websiteIds, $productIds);
        }

        $this->setData(
            ['product_ids' => array_unique($productIds), 'website_ids' => $websiteIds, 'action_type' => $type]
        );

        $categoryIndexer = $this->indexerRegistry->get(\Magento\Catalog\Model\Indexer\Product\Category::INDEXER_ID);
        if (!$categoryIndexer->isScheduled()) {
            $categoryIndexer->reindexList(array_unique($productIds));
        }
    }
}
