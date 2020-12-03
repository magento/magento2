<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Model\ResourceModel\Stock;

use Magento\Catalog\Model\Product;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\CatalogInventory\Model\Stock;
use Magento\Eav\Model\Config;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\DB\Select;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\Website;
use Magento\Store\Model\WebsiteFactory;

/**
 * CatalogInventory Stock Status per website Resource Model
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @api
 *
 * @deprecated 100.3.0 Replaced with Multi Source Inventory
 * @link https://devdocs.magento.com/guides/v2.4/inventory/index.html
 * @link https://devdocs.magento.com/guides/v2.4/inventory/inventory-api-reference.html
 * @since 100.0.2
 */
class Status extends AbstractDb
{
    /**
     * Store model manager
     *
     * @var StoreManagerInterface
     * @deprecated 100.1.0
     */
    protected $_storeManager;

    /**
     * Website model factory
     *
     * @var WebsiteFactory
     */
    protected $_websiteFactory;

    /**
     * @var Config
     */
    protected $eavConfig;

    /**
     * @var StockConfigurationInterface
     */
    private $stockConfiguration;

    /**
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param WebsiteFactory $websiteFactory
     * @param Config $eavConfig
     * @param string $connectionName
     * @param StockConfigurationInterface $stockConfiguration
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        WebsiteFactory $websiteFactory,
        Config $eavConfig,
        $connectionName = null,
        $stockConfiguration = null
    ) {
        parent::__construct($context, $connectionName);

        $this->_storeManager = $storeManager;
        $this->_websiteFactory = $websiteFactory;
        $this->eavConfig = $eavConfig;
        $this->stockConfiguration = $stockConfiguration ?: ObjectManager::getInstance()
            ->get(StockConfigurationInterface::class);
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
     *
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
     *
     * Return array as key website_id, value store_id
     *
     * @return array
     */
    public function getWebsiteStores()
    {
        /** @var Website $website */
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
     *
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
     * @param Select $select
     * @param Website $website
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @return Status
     */
    public function addStockStatusToSelect(Select $select, Website $website)
    {
        $websiteId = $this->getWebsiteId($website->getId());
        $select->joinLeft(
            ['stock_status' => $this->getMainTable()],
            'e.entity_id = stock_status.product_id AND stock_status.website_id=' . $websiteId,
            ['is_salable' => 'stock_status.stock_status']
        );

        return $this;
    }

    /**
     * Add Stock information to Product Collection
     *
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
     * @param bool $isFilterInStock
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
     * @since 100.0.6
     */
    public function addStockDataToCollection($collection, $isFilterInStock)
    {
        $websiteId = $this->getWebsiteId();
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
        $websiteId = $this->getWebsiteId();
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
     * Get website with fallback to default
     *
     * @param Website $websiteId
     * @return int
     */
    private function getWebsiteId($websiteId = null)
    {
        if (null === $websiteId) {
            $websiteId = $this->stockConfiguration->getDefaultScopeId();
        }

        return $websiteId;
    }

    /**
     * Retrieve Product(s) status for store
     *
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

        $attribute = $this->eavConfig->getAttribute(Product::ENTITY, 'status');
        $attributeTable = $attribute->getBackend()->getTable();
        $linkField = $attribute->getEntity()->getLinkField();

        $connection = $this->getConnection();

        if ($storeId === null || $storeId == Store::DEFAULT_STORE_ID) {
            $select = $connection->select()->from($attributeTable, [$linkField, 'value'])
                ->where("{$linkField} IN (?)", $productIds, \Zend_Db::INT_TYPE)
                ->where('attribute_id = ?', $attribute->getAttributeId())
                ->where('store_id = ?', Store::DEFAULT_STORE_ID);

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
                Store::DEFAULT_STORE_ID
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
}
