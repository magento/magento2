<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Model\ResourceModel\Stock;

use Magento\CatalogInventory\Model\Stock;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\Framework\App\ObjectManager;

/**
 * CatalogInventory Stock Status per website Resource Model
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Status extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Store model manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     * @deprecated
     */
    protected $_storeManager;

    /**
     * Website model factory
     *
     * @var \Magento\Store\Model\WebsiteFactory
     */
    protected $_websiteFactory;

    /**
     * @var \Magento\Eav\Model\Config
     */
    protected $eavConfig;

    /**
     * @var StockConfigurationInterface
     */
    private $stockConfiguration;

    /**
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Store\Model\WebsiteFactory $websiteFactory
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param string $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Store\Model\WebsiteFactory $websiteFactory,
        \Magento\Eav\Model\Config $eavConfig,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);

        $this->_storeManager = $storeManager;
        $this->_websiteFactory = $websiteFactory;
        $this->eavConfig = $eavConfig;
    }

    /**
     * Resource model initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('cataloginventory_stock_status', 'product_id');
    }

    /**
     * Save Product Status per website
     *
     * @param int $productId
     * @param int $status
     * @param float|int $qty
     * @param int|null $websiteId
     * @param int $stockId
     * @return $this
     */
    public function saveProductStatus(
        $productId,
        $status,
        $qty,
        $websiteId,
        $stockId = Stock::DEFAULT_STOCK_ID
    ) {
        $connection = $this->getConnection();
        $select = $connection->select()->from($this->getMainTable())
            ->where('product_id = :product_id')
            ->where('website_id = :website_id')
            ->where('stock_id = :stock_id');
        $bind = [':product_id' => $productId, ':website_id' => $websiteId, ':stock_id' => $stockId];
        $row = $connection->fetchRow($select, $bind);
        if ($row) {
            $bind = ['qty' => $qty, 'stock_status' => $status];
            $where = [
                $connection->quoteInto('product_id=?', (int)$row['product_id']),
                $connection->quoteInto('website_id=?', (int)$row['website_id']),
            ];
            $connection->update($this->getMainTable(), $bind, $where);
        } else {
            $bind = [
                'product_id' => $productId,
                'website_id' => $websiteId,
                'stock_id' => $stockId,
                'qty' => $qty,
                'stock_status' => $status,
            ];
            $connection->insert($this->getMainTable(), $bind);
        }

        return $this;
    }

    /**
     * Retrieve product status
     * Return array as key product id, value - stock status
     *
     * @param int[] $productIds
     * @param int $websiteId
     * @param int $stockId
     * @return array
     */
    public function getProductsStockStatuses($productIds, $websiteId, $stockId = Stock::DEFAULT_STOCK_ID)
    {
        if (!is_array($productIds)) {
            $productIds = [$productIds];
        }

        $select = $this->getConnection()->select()
            ->from($this->getMainTable(), ['product_id', 'stock_status'])
            ->where('product_id IN(?)', $productIds)
            ->where('stock_id=?', (int) $stockId)
            ->where('website_id=?', (int) $websiteId);
        return $this->getConnection()->fetchPairs($select);
    }

    /**
     * Retrieve websites and default stores
     * Return array as key website_id, value store_id
     *
     * @return array
     */
    public function getWebsiteStores()
    {
        /** @var \Magento\Store\Model\Website $website */
        $website = $this->_websiteFactory->create();
        return $this->getConnection()->fetchPairs($website->getDefaultStoresSelect(false));
    }

    /**
     * Retrieve Product Type
     *
     * @param array|int $productIds
     * @return array
     */
    public function getProductsType($productIds)
    {
        if (!is_array($productIds)) {
            $productIds = [$productIds];
        }

        $select = $this->getConnection()->select()->from(
            ['e' => $this->getTable('catalog_product_entity')],
            ['entity_id', 'type_id']
        )->where(
            'entity_id IN(?)',
            $productIds
        );
        return $this->getConnection()->fetchPairs($select);
    }

    /**
     * Retrieve Product part Collection array
     * Return array as key product id, value product type
     *
     * @param int $lastEntityId
     * @param int $limit
     * @return array
     */
    public function getProductCollection($lastEntityId = 0, $limit = 1000)
    {
        $select = $this->getConnection()->select()->from(
            ['e' => $this->getTable('catalog_product_entity')],
            ['entity_id', 'type_id']
        )
            ->order('entity_id ASC')
            ->where('entity_id > :entity_id')
            ->limit($limit);
        return $this->getConnection()->fetchPairs($select, [':entity_id' => $lastEntityId]);
    }

    /**
     * Add stock status to prepare index select
     *
     * @param \Magento\Framework\DB\Select $select
     * @param \Magento\Store\Model\Website $website
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @return Status
     */
    public function addStockStatusToSelect(\Magento\Framework\DB\Select $select, \Magento\Store\Model\Website $website)
    {
        $websiteId = $this->getStockConfiguration()->getDefaultScopeId();
        $select->joinLeft(
            ['stock_status' => $this->getMainTable()],
            'e.entity_id = stock_status.product_id AND stock_status.website_id=' . $websiteId,
            ['is_salable' => 'stock_status.stock_status']
        );

        return $this;
    }

    /**
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
     * @param bool $isFilterInStock
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
     */
    public function addStockDataToCollection($collection, $isFilterInStock)
    {
        $websiteId = $this->getStockConfiguration()->getDefaultScopeId();
        $joinCondition = $this->getConnection()->quoteInto(
            'e.entity_id = stock_status_index.product_id' . ' AND stock_status_index.website_id = ?',
            $websiteId
        );

        $joinCondition .= $this->getConnection()->quoteInto(
            ' AND stock_status_index.stock_id = ?',
            Stock::DEFAULT_STOCK_ID
        );
        $method = $isFilterInStock ? 'join' : 'joinLeft';
        $collection->getSelect()->$method(
            ['stock_status_index' => $this->getMainTable()],
            $joinCondition,
            ['is_salable' => 'stock_status']
        );

        if ($isFilterInStock) {
            $collection->getSelect()->where(
                'stock_status_index.stock_status = ?',
                Stock\Status::STATUS_IN_STOCK
            );
        }
        return $collection;
    }

    /**
     * Add only is in stock products filter to product collection
     *
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
     * @return $this
     */
    public function addIsInStockFilterToCollection($collection)
    {
        $websiteId = $this->getStockConfiguration()->getDefaultScopeId();
        $joinCondition = $this->getConnection()->quoteInto(
            'e.entity_id = stock_status_index.product_id' . ' AND stock_status_index.website_id = ?',
            $websiteId
        );

        $joinCondition .= $this->getConnection()->quoteInto(
            ' AND stock_status_index.stock_id = ?',
            Stock::DEFAULT_STOCK_ID
        );

        $collection->getSelect()->join(
            ['stock_status_index' => $this->getMainTable()],
            $joinCondition,
            []
        )->where(
            'stock_status_index.stock_status=?',
            Stock\Status::STATUS_IN_STOCK
        );
        return $this;
    }

    /**
     * Retrieve Product(s) status for store
     * Return array where key is a product_id, value - status
     *
     * @param int[] $productIds
     * @param int $storeId
     * @return array
     */
    public function getProductStatus($productIds, $storeId = null)
    {
        if (!is_array($productIds)) {
            $productIds = [$productIds];
        }

        $attribute = $this->eavConfig->getAttribute(\Magento\Catalog\Model\Product::ENTITY, 'status');
        $attributeTable = $attribute->getBackend()->getTable();
        $linkField = $attribute->getEntity()->getLinkField();

        $connection = $this->getConnection();

        if ($storeId === null || $storeId == \Magento\Store\Model\Store::DEFAULT_STORE_ID) {
            $select = $connection->select()->from($attributeTable, [$linkField, 'value'])
                ->where("{$linkField} IN (?)", $productIds)
                ->where('attribute_id = ?', $attribute->getAttributeId())
                ->where('store_id = ?', \Magento\Store\Model\Store::DEFAULT_STORE_ID);

            $rows = $connection->fetchPairs($select);
        } else {
            $select = $connection->select()->from(
                ['t1' => $attributeTable],
                [$linkField => "t1.{$linkField}", 'value' => $connection->getIfNullSql('t2.value', 't1.value')]
            )->joinLeft(
                ['t2' => $attributeTable],
                "t1.{$linkField} = t2.{$linkField} AND t1.attribute_id = t2.attribute_id AND t2.store_id = {$storeId}"
            )->where(
                't1.store_id = ?',
                \Magento\Store\Model\Store::DEFAULT_STORE_ID
            )->where(
                't1.attribute_id = ?',
                $attribute->getAttributeId()
            )->where(
                "t1.{$linkField} IN(?)",
                $productIds
            );

            $rows = $connection->fetchPairs($select);
        }

        $statuses = [];
        foreach ($productIds as $productId) {
            if (isset($rows[$productId])) {
                $statuses[$productId] = $rows[$productId];
            } else {
                $statuses[$productId] = -1;
            }
        }
        return $statuses;
    }

    /**
     * @return StockConfigurationInterface
     *
     * @deprecated
     */
    private function getStockConfiguration()
    {
        if ($this->stockConfiguration === null) {
            $this->stockConfiguration = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magento\CatalogInventory\Api\StockConfigurationInterface::class);
        }
        return $this->stockConfiguration;
    }
}
