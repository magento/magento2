<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Model\Stock;

use Magento\Catalog\Model\Product;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Api\StockConfigurationInterface as StockConfigurationInterface;
use Magento\CatalogInventory\Api\StockItemRepositoryInterface as StockItemRepositoryInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\Model\AbstractExtensibleModel;

/**
 * Catalog Inventory Stock Item Model
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
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
    protected $_eventPrefix = 'cataloginventory_stock_item';

    const WEBSITE_ID = 'website_id';

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
     * @param ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param StockConfigurationInterface $stockConfiguration
     * @param StockRegistryInterface $stockRegistry
     * @param StockItemRepositoryInterface $stockItemRepository
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        StockConfigurationInterface $stockConfiguration,
        StockRegistryInterface $stockRegistry,
        StockItemRepositoryInterface $stockItemRepository,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
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
        $this->_init(\Magento\CatalogInventory\Model\ResourceModel\Stock\Item::class);
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
            $websiteId = $this->stockConfiguration->getDefaultScopeId();
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
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
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
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getIsQtyDecimal()
    {
        return (bool) $this->_getData(static::IS_QTY_DECIMAL);
    }

    /**
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getIsDecimalDivided()
    {
        return (bool) $this->_getData(static::IS_DECIMAL_DIVIDED);
    }

    /**
     * @return string Timestamp
     */
    public function getLowStockDate()
    {
        return $this->_getData(static::LOW_STOCK_DATE);
    }

    /**
     * Check if notification message should be added despite of backorders notification flag
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getShowDefaultNotificationMessage()
    {
        return false;
    }

    /**
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
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
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
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
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
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
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
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
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getUseConfigEnableQtyInc()
    {
        return (bool) $this->_getData(static::USE_CONFIG_ENABLE_QTY_INC);
    }

    /**
     * Retrieve whether Quantity Increments is enabled
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
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
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
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
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
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
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
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
     * @param \Magento\Quote\Model\Quote\Item $item
     * @param string $itemError
     * @param string $quoteError
     * @param string $errorIndex
     * @return $this
     */
    protected function _addQuoteItemError(
        \Magento\Quote\Model\Quote\Item $item,
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

    //@codeCoverageIgnoreStart

    /**
     * @param int $itemId
     * @return $this
     */
    public function setItemId($itemId)
    {
        return $this->setData(self::ITEM_ID, $itemId);
    }

    /**
     * @param int $productId
     * @return $this
     */
    public function setProductId($productId)
    {
        return $this->setData(self::PRODUCT_ID, $productId);
    }

    /**
     * Set Website Id
     *
     * @param int $websiteId
     * @return $this
     */
    public function setWebsiteId($websiteId)
    {
        return $this->setData(self::WEBSITE_ID, $websiteId);
    }

    /**
     * Set stock identifier
     *
     * @param int $stockId
     * @return $this
     */
    public function setStockId($stockId)
    {
        return $this->setData(self::STOCK_ID, $stockId);
    }

    /**
     * @param float $qty
     * @return $this
     */
    public function setQty($qty)
    {
        return $this->setData(self::QTY, $qty);
    }

    /**
     * Set Stock Availability
     *
     * @param bool|int $isInStock
     * @return $this
     */
    public function setIsInStock($isInStock)
    {
        return $this->setData(self::IS_IN_STOCK, $isInStock);
    }

    /**
     * @param bool $isQtyDecimal
     * @return $this
     */
    public function setIsQtyDecimal($isQtyDecimal)
    {
        return $this->setData(self::IS_QTY_DECIMAL, $isQtyDecimal);
    }

    /**
     * @param bool $useConfigMinQty
     * @return $this
     */
    public function setUseConfigMinQty($useConfigMinQty)
    {
        return $this->setData(self::USE_CONFIG_MIN_QTY, $useConfigMinQty);
    }

    /**
     * Set minimal quantity available for item status in stock
     *
     * @param float $minQty
     * @return $this
     */
    public function setMinQty($minQty)
    {
        return $this->setData(self::MIN_QTY, $minQty);
    }

    /**
     * @param int $useConfigMinSaleQty
     * @return $this
     */
    public function setUseConfigMinSaleQty($useConfigMinSaleQty)
    {
        return $this->setData(self::USE_CONFIG_MIN_SALE_QTY, $useConfigMinSaleQty);
    }

    /**
     * Set Minimum Qty Allowed in Shopping Cart or NULL when there is no limitation
     *
     * @param float $minSaleQty
     * @return $this
     */
    public function setMinSaleQty($minSaleQty)
    {
        return $this->setData(self::MIN_SALE_QTY, $minSaleQty);
    }

    /**
     * @param bool $useConfigMaxSaleQty
     * @return $this
     */
    public function setUseConfigMaxSaleQty($useConfigMaxSaleQty)
    {
        return $this->setData(self::USE_CONFIG_MAX_SALE_QTY, $useConfigMaxSaleQty);
    }

    /**
     * Set Maximum Qty Allowed in Shopping Cart data wrapper
     *
     * @param float $maxSaleQty
     * @return $this
     */
    public function setMaxSaleQty($maxSaleQty)
    {
        return $this->setData(self::MAX_SALE_QTY, $maxSaleQty);
    }

    /**
     * @param bool $useConfigBackorders
     * @return $this
     */
    public function setUseConfigBackorders($useConfigBackorders)
    {
        return $this->setData(self::USE_CONFIG_BACKORDERS, $useConfigBackorders);
    }

    /**
     * Set backOrders status
     *
     * @param int $backOrders
     * @return $this
     */
    public function setBackorders($backOrders)
    {
        return $this->setData(self::BACKORDERS, $backOrders);
    }

    /**
     * @param bool $useConfigNotifyStockQty
     * @return $this
     */
    public function setUseConfigNotifyStockQty($useConfigNotifyStockQty)
    {
        return $this->setData(self::USE_CONFIG_NOTIFY_STOCK_QTY, $useConfigNotifyStockQty);
    }

    /**
     * Set Notify for Quantity Below data wrapper
     *
     * @param float $notifyStockQty
     * @return $this
     */
    public function setNotifyStockQty($notifyStockQty)
    {
        return $this->setData(self::NOTIFY_STOCK_QTY, $notifyStockQty);
    }

    /**
     * @param bool $useConfigQtyIncrements
     * @return $this
     */
    public function setUseConfigQtyIncrements($useConfigQtyIncrements)
    {
        return $this->setData(self::USE_CONFIG_QTY_INCREMENTS, $useConfigQtyIncrements);
    }

    /**
     * Set Quantity Increments data wrapper
     *
     * @param float $qtyIncrements
     * @return $this
     */
    public function setQtyIncrements($qtyIncrements)
    {
        return $this->setData(self::QTY_INCREMENTS, $qtyIncrements);
    }

    /**
     * @param bool $useConfigEnableQtyInc
     * @return $this
     */
    public function setUseConfigEnableQtyInc($useConfigEnableQtyInc)
    {
        return $this->setData(self::USE_CONFIG_ENABLE_QTY_INC, $useConfigEnableQtyInc);
    }

    /**
     * Set whether Quantity Increments is enabled
     *
     * @param bool $enableQtyIncrements
     * @return $this
     */
    public function setEnableQtyIncrements($enableQtyIncrements)
    {
        return $this->setData(self::ENABLE_QTY_INCREMENTS, $enableQtyIncrements);
    }

    /**
     * @param bool $useConfigManageStock
     * @return $this
     */
    public function setUseConfigManageStock($useConfigManageStock)
    {
        return $this->setData(self::USE_CONFIG_MANAGE_STOCK, $useConfigManageStock);
    }

    /**
     * @param bool $manageStock
     * @return $this
     */
    public function setManageStock($manageStock)
    {
        return $this->setData(self::MANAGE_STOCK, $manageStock);
    }

    /**
     * @param string $lowStockDate
     * @return $this
     */
    public function setLowStockDate($lowStockDate)
    {
        return $this->setData(self::LOW_STOCK_DATE, $lowStockDate);
    }

    /**
     * @param bool $isDecimalDivided
     * @return $this
     */
    public function setIsDecimalDivided($isDecimalDivided)
    {
        return $this->setData(self::IS_DECIMAL_DIVIDED, $isDecimalDivided);
    }

    /**
     * @param int $stockStatusChangedAuto
     * @return $this
     */
    public function setStockStatusChangedAuto($stockStatusChangedAuto)
    {
        return $this->setData(self::STOCK_STATUS_CHANGED_AUTO, $stockStatusChangedAuto);
    }

    /**
     * {@inheritdoc}
     *
     * @return \Magento\CatalogInventory\Api\Data\StockItemExtensionInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * {@inheritdoc}
     *
     * @param \Magento\CatalogInventory\Api\Data\StockItemExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Magento\CatalogInventory\Api\Data\StockItemExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
    //@codeCoverageIgnoreEnd
}
