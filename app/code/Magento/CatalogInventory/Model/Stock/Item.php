<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Model\Stock;

use Magento\Catalog\Model\Product;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Api\StockConfigurationInterface as StockConfigurationInterface;
use Magento\CatalogInventory\Api\StockItemRepositoryInterface as StockItemRepositoryInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Framework\Api\AttributeDataBuilder;
use Magento\Framework\Api\MetadataServiceInterface;
use Magento\Framework\Model\AbstractExtensibleModel;

/**
 * Catalog Inventory Stock Item Model
 *
 * @method \Magento\CatalogInventory\Model\Stock\Item setProductId(int $value)
 * @method \Magento\CatalogInventory\Model\Stock\Item setStockId(int $value)
 * @method \Magento\CatalogInventory\Model\Stock\Item setQty(float $value)
 * @method \Magento\CatalogInventory\Model\Stock\Item setMinQty(float $value)
 * @method \Magento\CatalogInventory\Model\Stock\Item setUseConfigMinQty(int $value)
 * @method \Magento\CatalogInventory\Model\Stock\Item setIsQtyDecimal(int $value)
 * @method \Magento\CatalogInventory\Model\Stock\Item setBackorders(int $value)
 * @method \Magento\CatalogInventory\Model\Stock\Item setUseConfigBackorders(int $value)
 * @method \Magento\CatalogInventory\Model\Stock\Item setMinSaleQty(float $value)
 * @method \Magento\CatalogInventory\Model\Stock\Item setUseConfigMinSaleQty(int $value)
 * @method \Magento\CatalogInventory\Model\Stock\Item setMaxSaleQty(float $value)
 * @method \Magento\CatalogInventory\Model\Stock\Item setUseConfigMaxSaleQty(int $value)
 * @method \Magento\CatalogInventory\Model\Stock\Item setIsInStock(int $value)
 * @method \Magento\CatalogInventory\Model\Stock\Item setLowStockDate(string $value)
 * @method \Magento\CatalogInventory\Model\Stock\Item setNotifyStockQty(float $value)
 * @method \Magento\CatalogInventory\Model\Stock\Item setUseConfigNotifyStockQty(int $value)
 * @method \Magento\CatalogInventory\Model\Stock\Item setManageStock(int $value)
 * @method \Magento\CatalogInventory\Model\Stock\Item setUseConfigManageStock(int $value)
 * @method \Magento\CatalogInventory\Model\Stock\Item setStockStatusChangedAutomatically(int $value)
 * @method \Magento\CatalogInventory\Model\Stock\Item setUseConfigQtyIncrements(int $value)
 * @method \Magento\CatalogInventory\Model\Stock\Item setQtyIncrements(float $value)
 * @method \Magento\CatalogInventory\Model\Stock\Item setUseConfigEnableQtyInc(int $value)
 * @method \Magento\CatalogInventory\Model\Stock\Item setEnableQtyIncrements(int $value)
 */
class Item extends AbstractExtensibleModel implements StockItemInterface
{
    /**
     * Stock item entity code
     */
    const ENTITY = 'cataloginventory_stock_item';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $eventPrefix = 'cataloginventory_stock_item';

    /**
     * Parameter name in event
     *
     * In observe method you can use $observer->getEvent()->getItem() in this case
     *
     * @var string
     */
    protected $eventObject = 'item';

    /**
     * Store model manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var StockConfigurationInterface
     */
    protected $stockConfiguration;

    /**
     * @var StockRegistryInterface
     */
    protected $stockRegistry;

    /**
     * @var StockConfigurationInterface
     */
    protected $stockItemRepository;

    /**
     * @var float|false
     */
    protected $qtyIncrements;

    /**
     * Store id
     *
     * @var int|null
     */
    protected $storeId;

    /**
     * Customer group id
     *
     * @var int|null
     */
    protected $customerGroupId;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param MetadataServiceInterface $metadataService
     * @param AttributeDataBuilder $customAttributeBuilder
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param StockConfigurationInterface $stockConfiguration
     * @param StockRegistryInterface $stockRegistry
     * @param StockItemRepositoryInterface $stockItemRepository
     * @param \Magento\Framework\Model\Resource\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\Db $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        MetadataServiceInterface $metadataService,
        AttributeDataBuilder $customAttributeBuilder,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        StockConfigurationInterface $stockConfiguration,
        StockRegistryInterface $stockRegistry,
        StockItemRepositoryInterface $stockItemRepository,
        \Magento\Framework\Model\Resource\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\Db $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $metadataService,
            $customAttributeBuilder,
            $resource,
            $resourceCollection,
            $data
        );
        $this->customerSession = $customerSession;
        $this->storeManager = $storeManager;
        $this->stockConfiguration = $stockConfiguration;
        $this->stockRegistry = $stockRegistry;
        $this->stockItemRepository = $stockItemRepository;
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\CatalogInventory\Model\Resource\Stock\Item');
    }

    /**
     * @return int|null
     */
    public function getItemId()
    {
        return $this->_getData(static::ITEM_ID);
    }

    /**
     * Retrieve Website Id
     *
     * @return int
     */
    public function getWebsiteId()
    {
        $websiteId = $this->getData(static::WEBSITE_ID);
        if ($websiteId === null) {
            $websiteId = $this->stockConfiguration->getDefaultWebsiteId();
        }
        return (int) $websiteId;
    }

    /**
     * Retrieve stock identifier
     *
     * @return int
     */
    public function getStockId()
    {
        $stockId = $this->getData(static::STOCK_ID);
        if ($stockId === null) {
            $stockId = $this->stockRegistry->getStock($this->getWebsiteId())->getStockId();
        }
        return (int) $stockId;
    }

    /**
     * Retrieve Product Id
     *
     * @return int
     */
    public function getProductId()
    {
        return (int) $this->_getData(static::PRODUCT_ID);
    }

    /**
     * @return bool
     */
    public function getStockStatusChangedAuto()
    {
        return (bool) $this->_getData(static::STOCK_STATUS_CHANGED_AUTO);
    }

    /**
     * @return float
     */
    public function getQty()
    {
        return null === $this->_getData(static::QTY) ? null : floatval($this->_getData(static::QTY));
    }

    /**
     * Retrieve Stock Availability
     *
     * @return bool|int
     */
    public function getIsInStock()
    {
        if (!$this->getManageStock()) {
            return true;
        }
        return (bool) $this->_getData(static::IS_IN_STOCK);
    }

    /**
     * @return bool
     */
    public function getIsQtyDecimal()
    {
        return (bool) $this->_getData(static::IS_QTY_DECIMAL);
    }

    /**
     * @return bool
     */
    public function getIsDecimalDivided()
    {
        return (bool) $this->_getData(static::IS_DECIMAL_DIVIDED);
    }

    /**
     * @return int Timestamp
     */
    public function getLowStockDate()
    {
        return (int) $this->_getData(static::LOW_STOCK_DATE);
    }

    /**
     * Check if notification message should be added despite of backorders notification flag
     *
     * @return bool
     */
    public function getShowDefaultNotificationMessage()
    {
        return false;
    }

    /**
     * @return bool
     */
    public function getUseConfigMinQty()
    {
        return (bool) $this->_getData(static::USE_CONFIG_MIN_QTY);
    }

    /**
     * Retrieve minimal quantity available for item status in stock
     *
     * @return float
     */
    public function getMinQty()
    {
        if ($this->getUseConfigMinQty()) {
            $minQty = $this->stockConfiguration->getMinQty($this->getStoreId());
        } else {
            $minQty = (float)$this->getData(static::MIN_QTY);
        }
        return $minQty;
    }

    /**
     * @return bool
     */
    public function getUseConfigMinSaleQty()
    {
        return (bool) $this->_getData(static::USE_CONFIG_MIN_SALE_QTY);
    }
    /**
     * Retrieve Minimum Qty Allowed in Shopping Cart or NULL when there is no limitation
     *
     * @return float
     */
    public function getMinSaleQty()
    {
        if ($this->getUseConfigMinSaleQty()) {
            $customerGroupId = $this->getCustomerGroupId();
            $minSaleQty = $this->stockConfiguration->getMinSaleQty($this->getStoreId(), $customerGroupId);
        } else {
            $minSaleQty = (float) $this->getData(static::MIN_SALE_QTY);
        }
        return $minSaleQty;
    }

    /**
     * @return bool
     */
    public function getUseConfigMaxSaleQty()
    {
        return (bool) $this->_getData(static::USE_CONFIG_MAX_SALE_QTY);
    }

    /**
     * Retrieve Maximum Qty Allowed in Shopping Cart data wrapper
     *
     * @return float
     */
    public function getMaxSaleQty()
    {
        if ($this->getUseConfigMaxSaleQty()) {
            $customerGroupId = $this->getCustomerGroupId();
            $maxSaleQty = $this->stockConfiguration->getMaxSaleQty($this->getStoreId(), $customerGroupId);
        } else {
            $maxSaleQty = (float) $this->getData(static::MAX_SALE_QTY);
        }
        return $maxSaleQty;
    }

    /**
     * @return bool
     */
    public function getUseConfigNotifyStockQty()
    {
        return (bool) $this->_getData(static::USE_CONFIG_NOTIFY_STOCK_QTY);
    }

    /**
     * Retrieve Notify for Quantity Below data wrapper
     *
     * @return float
     */
    public function getNotifyStockQty()
    {
        if ($this->getUseConfigNotifyStockQty()) {
            return $this->stockConfiguration->getNotifyStockQty($this->getStoreId());
        }
        return (float) $this->getData(static::NOTIFY_STOCK_QTY);
    }

    /**
     * @return bool
     */
    public function getUseConfigEnableQtyInc()
    {
        return (bool) $this->_getData(static::USE_CONFIG_ENABLE_QTY_INC);
    }

    /**
     * Retrieve whether Quantity Increments is enabled
     *
     * @return bool
     */
    public function getEnableQtyIncrements()
    {
        if ($this->getUseConfigEnableQtyInc()) {
            return $this->stockConfiguration->getEnableQtyIncrements($this->getStoreId());
        }
        return (bool) $this->getData(static::ENABLE_QTY_INCREMENTS);
    }

    /**
     * Retrieve whether config for Quantity Increments should be used
     *
     * @return bool
     */
    public function getUseConfigQtyIncrements()
    {
        return (bool) $this->_getData(static::USE_CONFIG_QTY_INCREMENTS);
    }

    /**
     * Retrieve Quantity Increments
     *
     * @return int|false
     */
    public function getQtyIncrements()
    {
        if ($this->qtyIncrements === null) {
            if ($this->getEnableQtyIncrements()) {
                if ($this->getUseConfigQtyIncrements()) {
                    $this->qtyIncrements = $this->stockConfiguration->getQtyIncrements($this->getStoreId());
                } else {
                    $this->qtyIncrements = (int) $this->getData(static::QTY_INCREMENTS);
                }
            }
            if ($this->qtyIncrements <= 0) {
                $this->qtyIncrements = false;
            }
        }
        return $this->qtyIncrements;
    }

    /**
     * @return bool
     */
    public function getUseConfigBackorders()
    {
        return (bool) $this->_getData(static::USE_CONFIG_BACKORDERS);
    }

    /**
     * Retrieve backorders status
     *
     * @return int
     */
    public function getBackorders()
    {
        if ($this->getUseConfigBackorders()) {
            return $this->stockConfiguration->getBackorders($this->getStoreId());
        }
        return (int) $this->getData(static::BACKORDERS);
    }

    /**
     * @return bool
     */
    public function getUseConfigManageStock()
    {
        return (bool) $this->_getData(static::USE_CONFIG_MANAGE_STOCK);
    }

    /**
     * Retrieve can Manage Stock
     *
     * @return int
     */
    public function getManageStock()
    {
        if ($this->getUseConfigManageStock()) {
            return $this->stockConfiguration->getManageStock($this->getStoreId());
        }
        return (int) $this->getData(static::MANAGE_STOCK);
    }

    /**
     * Add error to Quote Item
     *
     * @param \Magento\Sales\Model\Quote\Item $item
     * @param string $itemError
     * @param string $quoteError
     * @param string $errorIndex
     * @return $this
     */
    protected function _addQuoteItemError(
        \Magento\Sales\Model\Quote\Item $item,
        $itemError,
        $quoteError,
        $errorIndex = 'error'
    ) {
        $item->setHasError(true);
        $item->setMessage($itemError);
        $item->setQuoteMessage($quoteError);
        $item->setQuoteMessageIndex($errorIndex);
        return $this;
    }

    /**
     * Save object data
     *
     * @return $this
     * @throws \Exception
     */
    public function save()
    {
        $this->stockItemRepository->save($this);
        return $this;
    }

    /**
     * Add product data to stock item
     *
     * @param Product $product
     * @return $this
     */
    public function setProduct(Product $product)
    {
        $this->setProductId($product->getId())
            ->setStoreId($product->getStoreId())
            ->setProductTypeId($product->getTypeId())
            ->setProductName($product->getName())
            ->setProductStatusChanged($product->dataHasChangedFor('status'))
            ->setProductChangedWebsites($product->getIsChangedWebsites());
        return $this;
    }

    /**
     * Setter for store id
     *
     * @param int $value Value of store id
     * @return $this
     */
    public function setStoreId($value)
    {
        $this->storeId = $value;
        return $this;
    }

    /**
     * Retrieve Store Id (product or current)
     *
     * @return int
     */
    public function getStoreId()
    {
        if ($this->storeId === null) {
            $this->storeId = $this->storeManager->getStore()->getId();
        }
        return $this->storeId;
    }

    /**
     * Getter for customer group id, return current customer group if not set
     *
     * @return int
     */
    public function getCustomerGroupId()
    {
        if ($this->customerGroupId === null) {
            return $this->customerSession->getCustomerGroupId();
        }
        return $this->customerGroupId;
    }

    /**
     * Setter for customer group id
     *
     * @param int $value Value of customer group id
     * @return $this
     */
    public function setCustomerGroupId($value)
    {
        $this->customerGroupId = $value;
        return $this;
    }
}
