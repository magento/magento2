<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Wishlist\Model\ResourceModel\Item;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\EntityManager\MetadataPool;

/**
 * Wishlist item collection
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 *
 * @api
 * @since 2.0.0
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Product Visibility Filter to product collection flag
     *
     * @var bool
     * @since 2.0.0
     */
    protected $_productVisible = false;

    /**
     * Product Salable Filter to product collection flag
     *
     * @var bool
     * @since 2.0.0
     */
    protected $_productSalable = false;

    /**
     * If product out of stock, its item will be removed after load
     *
     * @var bool
     * @since 2.0.0
     */
    protected $_productInStock = false;

    /**
     * Product Ids array
     *
     * @var array
     * @since 2.0.0
     */
    protected $_productIds = [];

    /**
     * Store Ids array
     *
     * @var array
     * @since 2.0.0
     */
    protected $_storeIds = [];

    /**
     * Add days in wishlist filter of product collection
     *
     * @var boolean
     * @since 2.0.0
     */
    protected $_addDaysInWishlist = false;

    /**
     * Sum of items collection qty
     *
     * @var int
     * @since 2.0.0
     */
    protected $_itemsQty;

    /**
     * Whether product name attribute value table is joined in select
     *
     * @var boolean
     * @since 2.0.0
     */
    protected $_isProductNameJoined = false;

    /**
     * Adminhtml sales
     *
     * @var \Magento\Sales\Helper\Admin
     * @since 2.0.0
     */
    protected $_adminhtmlSales = null;

    /**
     * Catalog inventory data
     *
     * @var \Magento\CatalogInventory\Api\StockConfigurationInterface
     * @since 2.0.0
     */
    protected $stockConfiguration = null;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     * @since 2.0.0
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     * @since 2.0.0
     */
    protected $_date;

    /**
     * @var \Magento\Wishlist\Model\Config
     * @since 2.0.0
     */
    protected $_wishlistConfig;

    /**
     * @var \Magento\Catalog\Model\Product\Visibility
     * @since 2.0.0
     */
    protected $_productVisibility;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     * @since 2.0.0
     */
    protected $_coreResource;

    /**
     * @var \Magento\Wishlist\Model\ResourceModel\Item\Option\CollectionFactory
     * @since 2.0.0
     */
    protected $_optionCollectionFactory;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     * @since 2.0.0
     */
    protected $_productCollectionFactory;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\ConfigFactory
     * @since 2.0.0
     */
    protected $_catalogConfFactory;

    /**
     * @var \Magento\Catalog\Model\Entity\AttributeFactory
     * @since 2.0.0
     */
    protected $_catalogAttrFactory;

    /**
     * @var \Magento\Framework\App\State
     * @since 2.0.0
     */
    protected $_appState;

    /**
     * @var MetadataPool
     * @since 2.1.0
     */
    protected $metadataPool;

    /**
     * @param \Magento\Framework\Data\Collection\EntityFactory $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\CatalogInventory\Api\StockConfigurationInterface $stockConfiguration
     * @param \Magento\Sales\Helper\Admin $adminhtmlSales
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magento\Wishlist\Model\Config $wishlistConfig
     * @param \Magento\Catalog\Model\Product\Visibility $productVisibility
     * @param \Magento\Framework\App\ResourceConnection $coreResource
     * @param \Magento\Wishlist\Model\ResourceModel\Item\Option\CollectionFactory $optionCollectionFactory
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param \Magento\Catalog\Model\ResourceModel\ConfigFactory $catalogConfFactory
     * @param \Magento\Catalog\Model\Entity\AttributeFactory $catalogAttrFactory
     * @param \Magento\Wishlist\Model\ResourceModel\Item $resource
     * @param \Magento\Framework\App\State $appState
     * @param \Magento\Framework\DB\Adapter\AdapterInterface $connection
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactory $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\CatalogInventory\Api\StockConfigurationInterface $stockConfiguration,
        \Magento\Sales\Helper\Admin $adminhtmlSales,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Wishlist\Model\Config $wishlistConfig,
        \Magento\Catalog\Model\Product\Visibility $productVisibility,
        \Magento\Framework\App\ResourceConnection $coreResource,
        \Magento\Wishlist\Model\ResourceModel\Item\Option\CollectionFactory $optionCollectionFactory,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Catalog\Model\ResourceModel\ConfigFactory $catalogConfFactory,
        \Magento\Catalog\Model\Entity\AttributeFactory $catalogAttrFactory,
        \Magento\Wishlist\Model\ResourceModel\Item $resource,
        \Magento\Framework\App\State $appState,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null
    ) {
        $this->stockConfiguration = $stockConfiguration;
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
    }

    /**
     * Initialize resource model for collection
     *
     * @return void
     * @since 2.0.0
     */
    public function _construct()
    {
        $this->_init(\Magento\Wishlist\Model\Item::class, \Magento\Wishlist\Model\ResourceModel\Item::class);
        $this->addFilterToMap('store_id', 'main_table.store_id');
    }

    /**
     * Get metadata pool object
     *
     * @return MetadataPool
     * @since 2.1.0
     */
    protected function getMetadataPool()
    {
        if ($this->metadataPool == null) {
            $this->metadataPool = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magento\Framework\EntityManager\MetadataPool::class);
        }
        return $this->metadataPool;
    }

    /**
     * After load processing
     *
     * @return $this
     * @since 2.0.0
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
     * @since 2.0.0
     */
    protected function _assignOptions()
    {
        $itemIds = array_keys($this->_items);
        /* @var $optionCollection \Magento\Wishlist\Model\ResourceModel\Item\Option\Collection */
        $optionCollection = $this->_optionCollectionFactory->create();
        $optionCollection->addItemFilter($itemIds);

        /* @var $item \Magento\Wishlist\Model\Item */
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
     * @since 2.0.0
     */
    protected function _assignProducts()
    {
        \Magento\Framework\Profiler::start(
            'WISHLIST:' . __METHOD__,
            ['group' => 'WISHLIST', 'method' => __METHOD__]
        );

        /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $productCollection */
        $productCollection = $this->_productCollectionFactory->create();

        if ($this->_productVisible) {
            $productCollection->setVisibility($this->_productVisibility->getVisibleInSiteIds());
        }

        $productCollection->addPriceData()
            ->addTaxPercents()
            ->addIdFilter($this->_productIds)
            ->addAttributeToSelect($this->_wishlistConfig->getProductAttributes())
            ->addOptionsToResult()
            ->addUrlRewrite();

        if ($this->_productSalable) {
            $productCollection = $this->_adminhtmlSales->applySalableProductTypesFilter($productCollection);
        }

        $this->_eventManager->dispatch(
            'wishlist_item_collection_products_after_load',
            ['product_collection' => $productCollection]
        );

        $checkInStock = $this->_productInStock && !$this->stockConfiguration->isShowOutOfStock();

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
                $item->isDeleted(true);
            }
        }

        \Magento\Framework\Profiler::stop('WISHLIST:' . __METHOD__);

        return $this;
    }

    /**
     * Add filter by wishlist object
     *
     * @param \Magento\Wishlist\Model\Wishlist $wishlist
     * @return $this
     * @since 2.0.0
     */
    public function addWishlistFilter(\Magento\Wishlist\Model\Wishlist $wishlist)
    {
        $this->addFieldToFilter('wishlist_id', $wishlist->getId());
        return $this;
    }

    /**
     * Add filtration by customer id
     *
     * @param int $customerId
     * @return $this
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function resetSortOrder()
    {
        $this->getSelect()->reset(\Magento\Framework\DB\Select::ORDER);
        return $this;
    }

    /**
     * Set product Visibility Filter to product collection flag
     *
     * @param bool $flag
     * @return $this
     * @since 2.0.0
     */
    public function setVisibilityFilter($flag = true)
    {
        $this->_productVisible = (bool)$flag;
        return $this;
    }

    /**
     * Set Salable Filter.
     * This filter apply Salable Product Types Filter to product collection.
     *
     * @param bool $flag
     * @return $this
     * @since 2.0.0
     */
    public function setSalableFilter($flag = true)
    {
        $this->_productSalable = (bool)$flag;
        return $this;
    }

    /**
     * Set In Stock Filter.
     * This filter remove items with no salable product.
     *
     * @param bool $flag
     * @return $this
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function addDaysFilter($constraints)
    {
        if (!is_array($constraints)) {
            return $this;
        }

        $filter = [];

        $gmtOffset = (new \DateTimeZone(date_default_timezone_get()))->getOffset(new \DateTime());
        if (isset($constraints['from'])) {
            $lastDay = new \DateTime();
            $lastDay->modify('-' . $gmtOffset . ' second')->modify('-' . $constraints['from'] . ' day');
            $filter['to'] = $lastDay;
        }

        if (isset($constraints['to'])) {
            $firstDay = new \DateTime();
            $firstDay->modify('-' . $gmtOffset . ' second')->modify('-' . (intval($constraints['to']) + 1) . ' day');
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
     * @since 2.0.0
     */
    protected function _joinProductNameTable()
    {
        if (!$this->_isProductNameJoined) {
            $entityTypeId = $this->_catalogConfFactory->create()->getEntityTypeId();
            /** @var \Magento\Catalog\Model\Entity\Attribute $attribute */
            $attribute = $this->_catalogAttrFactory->create()->loadByCode($entityTypeId, 'name');

            $storeId = $this->_storeManager->getStore(\Magento\Store\Model\Store::ADMIN_CODE)->getId();

            $entityMetadata = $this->getMetadataPool()->getMetadata(ProductInterface::class);

            $this->getSelect()->join(
                ['product_name_table' => $attribute->getBackendTable()],
                'product_name_table.' . $entityMetadata->getLinkField() . ' = main_table.product_id' .
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
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @return $this
     * @since 2.0.0
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
}
