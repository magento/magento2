<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Catalog\Model\ResourceModel\Product;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\DB\Select;
use Magento\Store\Model\Store;

/**
 * Provide Select object for retrieve product id with minimal price.
 */
class LinkedProductSelectBuilderByBasePrice implements LinkedProductSelectBuilderInterface
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resource;

    /**
     * @var \Magento\Eav\Model\Config
     */
    private $eavConfig;

    /**
     * @var \Magento\Catalog\Helper\Data
     */
    private $catalogHelper;

    /**
     * @var \Magento\Framework\EntityManager\MetadataPool
     */
    private $metadataPool;

    /**
     * @var BaseSelectProcessorInterface
     */
    private $baseSelectProcessor;

    /**
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\ResourceConnection $resourceConnection
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Catalog\Helper\Data $catalogHelper
     * @param \Magento\Framework\EntityManager\MetadataPool $metadataPool
     * @param BaseSelectProcessorInterface $baseSelectProcessor
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Catalog\Helper\Data $catalogHelper,
        \Magento\Framework\EntityManager\MetadataPool $metadataPool,
        ?BaseSelectProcessorInterface $baseSelectProcessor = null
    ) {
        $this->storeManager = $storeManager;
        $this->resource = $resourceConnection;
        $this->eavConfig = $eavConfig;
        $this->catalogHelper = $catalogHelper;
        $this->metadataPool = $metadataPool;
        $this->baseSelectProcessor = (null !== $baseSelectProcessor)
            ? $baseSelectProcessor : ObjectManager::getInstance()->get(BaseSelectProcessorInterface::class);
    }

    /**
     * @inheritdoc
     */
    public function build(int $productId, int $storeId) : array
    {
        $linkField = $this->metadataPool->getMetadata(ProductInterface::class)->getLinkField();
        $priceAttribute = $this->eavConfig->getAttribute(Product::ENTITY, 'price');
        $productTable = $this->resource->getTableName('catalog_product_entity');

        $priceSelect = $this->resource->getConnection()->select()
            ->from(['parent' => $productTable], '')
            ->joinInner(
                ['link' => $this->resource->getTableName('catalog_product_relation')],
                "link.parent_id = parent.$linkField",
                []
            )->joinInner(
                [BaseSelectProcessorInterface::PRODUCT_TABLE_ALIAS => $productTable],
                sprintf('%s.entity_id = link.child_id', BaseSelectProcessorInterface::PRODUCT_TABLE_ALIAS),
                ['entity_id']
            )->joinInner(
                ['t' => $priceAttribute->getBackendTable()],
                sprintf('t.%s = %s.%1$s', $linkField, BaseSelectProcessorInterface::PRODUCT_TABLE_ALIAS),
                []
            )->where('parent.entity_id = ?', $productId)
            ->where('t.attribute_id = ?', $priceAttribute->getAttributeId())
            ->where('t.value IS NOT NULL')
            ->order('t.value ' . Select::SQL_ASC)
            ->order(BaseSelectProcessorInterface::PRODUCT_TABLE_ALIAS . '.' . $linkField . ' ' . Select::SQL_ASC)
            ->limit(1);
        $priceSelect = $this->baseSelectProcessor->process($priceSelect);

        if (!$this->catalogHelper->isPriceGlobal()) {
            $priceSelectStore = clone $priceSelect;
            $priceSelectStore->where('t.store_id = ?', $storeId);
            $selects[] = $priceSelectStore;
        }

        $priceSelect->where('t.store_id = ?', Store::DEFAULT_STORE_ID);
        $selects[] = $priceSelect;

        return $selects;
    }
}
