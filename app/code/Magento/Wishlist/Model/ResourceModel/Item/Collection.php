<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Wishlist\Model\ResourceModel\Item;

use DateTime;
use DateTimeZone;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Entity\Attribute;
use Magento\Catalog\Model\Entity\AttributeFactory;
use Magento\Catalog\Model\Indexer\Category\Product\TableMaintainer;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\ConfigFactory;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\CatalogInventory\Model\ResourceModel\StockStatusFilterInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\State;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactory;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Framework\Profiler;
use Magento\Framework\Stdlib\DateTime\DateTime as FrameworkDateTime;
use Magento\Sales\Helper\Admin;
use Magento\Sales\Model\ConfigInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Wishlist\Model\Config;
use Magento\Wishlist\Model\Item;
use Magento\Wishlist\Model\ResourceModel\Item as ResourceItem;
use Magento\Wishlist\Model\ResourceModel\Item\Option\CollectionFactory;
use Magento\Wishlist\Model\ResourceModel\Item\Product\CollectionBuilderInterface;
use Magento\Wishlist\Model\Wishlist;
use Psr\Log\LoggerInterface;

/**
 * Wishlist item collection
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 *
 * @api
 * @since 100.0.2
 */
class Collection extends AbstractCollection
{
    /**
     * Product Visibility Filter to product collection flag
     *
     * @var bool
     */
    protected $_productVisible = false;

    /**
     * Product Salable Filter to product collection flag
     *
     * @var bool
     */
    protected $_productSalable = false;

    /**
     * If product out of stock, its item will be removed after load
     *
     * @var bool
     */
    protected $_productInStock = false;

    /**
     * Product Ids array
     *
     * @var array
     */
    protected $_productIds = [];

    /**
     * Store Ids array
     *
     * @var array
     */
    protected $_storeIds = [];

    /**
     * Add days in wishlist filter of product collection
     *
     * @var boolean
     */
    protected $_addDaysInWishlist = false;

    /**
     * Sum of items collection qty
     *
     * @var int
     */
    protected $_itemsQty;

    /**
     * Whether product name attribute value table is joined in select
     *
     * @var boolean
     */
    protected $_isProductNameJoined = false;

    /**
     * Adminhtml sales
     *
     * @var Admin
     */
    protected $_adminhtmlSales = null;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var FrameworkDateTime
     */
    protected $_date;

    /**
     * @var Config
     */
    protected $_wishlistConfig;

    /**
     * @var Visibility
     */
    protected $_productVisibility;

    /**
     * @var ResourceConnection
     */
    protected $_coreResource;

    /**
     * @var CollectionFactory
     */
    protected $_optionCollectionFactory;

    /**
     * @var ProductCollectionFactory
     */
    protected $_productCollectionFactory;

    /**
     * @var ConfigFactory
     */
    protected $_catalogConfFactory;

    /**
     * @var AttributeFactory
     */
    protected $_catalogAttrFactory;

    /**
     * @var State
     */
    protected $_appState;

    /**
     * @var MetadataPool
     * @since 100.1.0
     */
    protected $metadataPool;

    /**
     * Whether product table is joined in select
     *
     * @var bool
     */
    private $isProductTableJoined = false;

    /**
     * @param EntityFactory $entityFactory
     * @param LoggerInterface $logger
     * @param FetchStrategyInterface $fetchStrategy
     * @param ManagerInterface $eventManager
     * @param StockConfigurationInterface $stockConfiguration
     * @param Admin $adminhtmlSales
     * @param StoreManagerInterface $storeManager
     * @param FrameworkDateTime $date
     * @param Config $wishlistConfig
     * @param Visibility $productVisibility
     * @param ResourceConnection $coreResource
     * @param CollectionFactory $optionCollectionFactory
     * @param ProductCollectionFactory $productCollectionFactory
     * @param ConfigFactory $catalogConfFactory
     * @param AttributeFactory $catalogAttrFactory
     * @param ResourceItem $resource
     * @param State $appState
     * @param AdapterInterface|null $connection
     * @param TableMaintainer|null $tableMaintainer
     * @param ConfigInterface|null $salesConfig
     * @param CollectionBuilderInterface|null $productCollectionBuilder
     * @param StockStatusFilterInterface|null $stockStatusFilter
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        EntityFactory $entityFactory,
        LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
        protected ?StockConfigurationInterface $stockConfiguration = null,
        Admin $adminhtmlSales,
        StoreManagerInterface $storeManager,
        FrameworkDateTime $date,
        Config $wishlistConfig,
        Visibility $productVisibility,
        ResourceConnection $coreResource,
        CollectionFactory $optionCollectionFactory,
        ProductCollectionFactory $productCollectionFactory,
        ConfigFactory $catalogConfFactory,
        AttributeFactory $catalogAttrFactory,
        ResourceItem $resource,
        State $appState,
        AdapterInterface $connection = null,
        private ?TableMaintainer $tableMaintainer = null,
        private ?ConfigInterface $salesConfig = null,
        private ?CollectionBuilderInterface $productCollectionBuilder = null,
        private ?StockStatusFilterInterface $stockStatusFilter = null
    ) {
        $this->_adminhtmlSales = $adminhtmlSales;
        $this->_storeManager = $storeManager;
        $this->_date = $date;
        $this->_wishlistConfig = $wishlistConfig;
        $this->_productVisibility = $productVisibility;
        $this->_coreResource = $coreResource;
        $this->_optionCollectionFactory = $optionCollectionFactory;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_catalogConfFactory = $catalogConfFactory;
        $this->_catalogAttrFactory = $catalogAttrFactory;
        $this->_appState = $appState;
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
        $this->tableMaintainer = $tableMaintainer ?: ObjectManager::getInstance()->get(TableMaintainer::class);
        $this->salesConfig = $salesConfig ?: ObjectManager::getInstance()->get(ConfigInterface::class);
        $this->productCollectionBuilder = $productCollectionBuilder
            ?: ObjectManager::getInstance()->get(CollectionBuilderInterface::class);
        $this->stockStatusFilter = $stockStatusFilter
            ?: ObjectManager::getInstance()->get(StockStatusFilterInterface::class);
    }

    /**
     * Initialize resource model for collection
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init(Item::class, ResourceItem::class);
        $this->addFilterToMap('store_id', 'main_table.store_id');
    }

    /**
     * Get metadata pool object
     *
     * @return MetadataPool
     * @since 100.1.0
     */
    protected function getMetadataPool()
    {
        if ($this->metadataPool == null) {
            $this->metadataPool = ObjectManager::getInstance()
                ->get(MetadataPool::class);
        }
        return $this->metadataPool;
    }

    /**
     * After load processing
     *
     * @return $this
     */
    protected function _afterLoad()
    {
        parent::_afterLoad();

        /**
         * Assign products
         */
        $this->_assignOptions();
        $this->_assignProducts();
        $this->resetItemsDataChanged();

        $this->getPageSize();

        return $this;
    }

    /**
     * Add options to items
     *
     * @return $this
     */
    protected function _assignOptions()
    {
        $itemIds = array_keys($this->_items);
        /* @var $optionCollection Option\Collection */
        $optionCollection = $this->_optionCollectionFactory->create();
        $optionCollection->addItemFilter($itemIds);

        /* @var $item Item */
        foreach ($this as $item) {
            $item->setOptions($optionCollection->getOptionsByItem($item));
        }
        $productIds = $optionCollection->getProductIds();
        $this->_productIds = array_merge($this->_productIds, $productIds);

        return $this;
    }

    /**
     * Add products to items and item options
     *
     * @return $this
     */
    protected function _assignProducts()
    {
        Profiler::start(
            'WISHLIST:' . __METHOD__,
            ['group' => 'WISHLIST', 'method' => __METHOD__]
        );

        /** @var ProductCollection $productCollection */
        $productCollection = $this->_productCollectionFactory->create();

        if ($this->_productVisible) {
            $productCollection->setVisibility($this->_productVisibility->getVisibleInSiteIds());
        }

        $productCollection->addIdFilter($this->_productIds)
            ->addAttributeToSelect($this->_wishlistConfig->getProductAttributes());

        $productCollection = $this->productCollectionBuilder->build($this, $productCollection);

        if ($this->_productSalable) {
            $productCollection = $this->_adminhtmlSales->applySalableProductTypesFilter($productCollection);
        }

        $this->_eventManager->dispatch(
            'wishlist_item_collection_products_after_load',
            ['product_collection' => $productCollection]
        );

        $checkInStock = $this->_productInStock && !$this->stockConfiguration->isShowOutOfStock();

        /** @var Item $item */
        foreach ($this as $item) {
            $product = $productCollection->getItemById($item->getProductId());
            if ($product) {
                if ($checkInStock && !$product->isInStock()) {
                    $this->removeItemByKey($item->getId());
                } else {
                    $product->setCustomOptions([]);
                    $item->setProduct($product);
                    $item->setProductName($product->getName());
                    $item->setName($product->getName());
                    $item->setPrice($product->getPrice());
                }
            } else {
                $this->removeItemByKey($item->getId());
            }
        }

        Profiler::stop('WISHLIST:' . __METHOD__);

        return $this;
    }

    /**
     * @inheritdoc
     * @since 101.1.3
     */
    protected function _renderFiltersBefore()
    {
        parent::_renderFiltersBefore();

        $mainTableName = 'main_table';
        $connection = $this->getConnection();

        if ($this->_productInStock && !$this->stockConfiguration->isShowOutOfStock()) {
            $this->joinProductTable();
            $this->stockStatusFilter->execute($this->getSelect(), 'product_entity', 'stockItem');
        }

        if ($this->_productVisible) {
            $rootCategoryId = $this->_storeManager->getStore()->getRootCategoryId();
            $visibleInSiteIds = $this->_productVisibility->getVisibleInSiteIds();
            $visibilityConditions = [
                "cat_index.product_id = {$mainTableName}.product_id",
                $connection->quoteInto('cat_index.category_id = ?', $rootCategoryId),
                $connection->quoteInto('cat_index.visibility IN (?)', $visibleInSiteIds)
            ];
            $this->getSelect()->join(
                ['cat_index' => $this->tableMaintainer->getMainTable($this->_storeManager->getStore()->getId())],
                join(' AND ', $visibilityConditions),
                []
            );
        }

        if ($this->_productSalable) {
            $availableProductTypes = $this->salesConfig->getAvailableProductTypes();
            $this->getSelect()->join(
                ['cat_prod' => $this->getTable('catalog_product_entity')],
                $this->getConnection()
                    ->quoteInto(
                        "cat_prod.type_id IN (?) AND {$mainTableName}.product_id = cat_prod.entity_id",
                        $availableProductTypes
                    ),
                []
            );
        }
    }

    /**
     * Add filter by wishlist object
     *
     * @param Wishlist $wishlist
     * @return $this
     */
    public function addWishlistFilter(Wishlist $wishlist)
    {
        $this->addFieldToFilter('wishlist_id', $wishlist->getId());
        return $this;
    }

    /**
     * Add filtration by customer id
     *
     * @param int $customerId
     * @return $this
     */
    public function addCustomerIdFilter($customerId)
    {
        $this->getSelect()->join(
            ['wishlist' => $this->getTable('wishlist')],
            'main_table.wishlist_id = wishlist.wishlist_id',
            []
        )->where(
            'wishlist.customer_id = ?',
            $customerId
        );
        return $this;
    }

    /**
     * Add filter by shared stores
     *
     * @param array $storeIds
     * @return $this
     */
    public function addStoreFilter($storeIds = [])
    {
        if (!is_array($storeIds)) {
            $storeIds = [$storeIds];
        }
        $this->_storeIds = $storeIds;
        $this->addFieldToFilter('store_id', ['in' => $this->_storeIds]);

        return $this;
    }

    /**
     * Add items store data to collection
     *
     * @return $this
     */
    public function addStoreData()
    {
        $storeTable = $this->_coreResource->getTableName('store');
        $this->getSelect()->join(
            ['store' => $storeTable],
            'main_table.store_id=store.store_id',
            ['store_name' => 'name', 'item_store_id' => 'store_id']
        );
        return $this;
    }

    /**
     * Reset sort order
     *
     * @return $this
     */
    public function resetSortOrder()
    {
        $this->getSelect()->reset(Select::ORDER);
        return $this;
    }

    /**
     * Set product Visibility Filter to product collection flag
     *
     * @param bool $flag
     * @return $this
     */
    public function setVisibilityFilter($flag = true)
    {
        $this->_productVisible = (bool)$flag;
        return $this;
    }

    /**
     * Set Salable Filter.
     *
     * This filter apply Salable Product Types Filter to product collection.
     *
     * @param bool $flag
     * @return $this
     */
    public function setSalableFilter($flag = true)
    {
        $this->_productSalable = (bool)$flag;
        return $this;
    }

    /**
     * Set In Stock Filter.
     *
     * This filter remove items with no salable product.
     *
     * @param bool $flag
     * @return $this
     */
    public function setInStockFilter($flag = true)
    {
        $this->_productInStock = (bool)$flag;
        return $this;
    }

    /**
     * Set flag of adding days in wishlist
     *
     * @return $this
     */
    public function addDaysInWishlist()
    {
        $this->_addDaysInWishlist = true;
        return $this;
    }

    /**
     * Adds filter on days in wishlist
     *
     * The $constraints may contain 'from' and 'to' indexes with number of days to look for items
     *
     * @param array $constraints
     * @return $this
     */
    public function addDaysFilter($constraints)
    {
        if (!is_array($constraints)) {
            return $this;
        }

        $filter = [];

        $gmtOffset = (new DateTimeZone(date_default_timezone_get()))->getOffset(new DateTime());
        if (isset($constraints['from'])) {
            $lastDay = new DateTime();
            $lastDay->modify('-' . $gmtOffset . ' second')->modify('-' . $constraints['from'] . ' day');
            $filter['to'] = $lastDay;
        }

        if (isset($constraints['to'])) {
            $firstDay = new DateTime();
            $firstDay->modify('-' . $gmtOffset . ' second')->modify('-' . ((int)($constraints['to']) + 1) . ' day');
            $filter['from'] = $firstDay;
        }

        if ($filter) {
            $filter['datetime'] = true;
            $this->addFieldToFilter('added_at', $filter);
        }

        return $this;
    }

    /**
     * Joins product name attribute value to use it in WHERE and ORDER clauses
     *
     * @return $this
     */
    protected function _joinProductNameTable()
    {
        if (!$this->_isProductNameJoined) {
            $entityTypeId = $this->_catalogConfFactory->create()->getEntityTypeId();
            /** @var Attribute $attribute */
            $attribute = $this->_catalogAttrFactory->create()->loadByCode($entityTypeId, 'name');

            $storeId = $this->_storeManager->getStore(Store::ADMIN_CODE)->getId();

            $entityMetadata = $this->getMetadataPool()->getMetadata(ProductInterface::class);
            $linkField = $entityMetadata->getLinkField();
            $this->joinProductTable();

            $this->getSelect()->join(
                ['product_name_table' => $attribute->getBackendTable()],
                'product_name_table.' . $linkField . ' = product_entity.' . $linkField .
                ' AND product_name_table.store_id = ' .
                $storeId .
                ' AND product_name_table.attribute_id = ' .
                $attribute->getId(),
                []
            );

            $this->_isProductNameJoined = true;
        }

        return $this;
    }

    /**
     * Adds filter on product name
     *
     * @param string $productName
     * @return $this
     */
    public function addProductNameFilter($productName)
    {
        $this->_joinProductNameTable();
        $this->getSelect()->where('INSTR(product_name_table.value, ?)', $productName);

        return $this;
    }

    /**
     * Sets ordering by product name
     *
     * @param string $dir
     * @return $this
     */
    public function setOrderByProductName($dir)
    {
        $this->_joinProductNameTable();
        $this->getSelect()->order('product_name_table.value ' . $dir);
        return $this;
    }

    /**
     * Get sum of items collection qty
     *
     * @return int
     */
    public function getItemsQty()
    {
        if ($this->_itemsQty === null) {
            $this->_itemsQty = 0;
            foreach ($this as $wishlistItem) {
                $qty = $wishlistItem->getQty();
                $this->_itemsQty += $qty === 0 ? 1 : $qty;
            }
        }

        return (int)$this->_itemsQty;
    }

    /**
     * After load data
     *
     * @return $this
     */
    protected function _afterLoadData()
    {
        parent::_afterLoadData();

        if ($this->_addDaysInWishlist) {
            $gmtOffset = (int)$this->_date->getGmtOffset();
            $nowTimestamp = $this->_date->timestamp();

            foreach ($this as $wishlistItem) {
                $wishlistItemTimestamp = $this->_date->timestamp($wishlistItem->getAddedAt());

                $wishlistItem->setDaysInWishlist(
                    (int)(($nowTimestamp - $gmtOffset - $wishlistItemTimestamp) / 24 / 60 / 60)
                );
            }
        }

        return $this;
    }

    /**
     * Join product table to select if not already joined
     *
     * @return void
     */
    private function joinProductTable(): void
    {
        if (!$this->isProductTableJoined) {
            $this->getSelect()->join(
                ['product_entity' => $this->getTable('catalog_product_entity')],
                'product_entity.entity_id = main_table.product_id',
                []
            );
            $this->isProductTableJoined = true;
        }
    }
}
