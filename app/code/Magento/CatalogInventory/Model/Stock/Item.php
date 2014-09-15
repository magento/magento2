<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\CatalogInventory\Model\Stock;

use Magento\Catalog\Model\Product;

/**
 * Catalog Inventory Stock Model
 *
 * @method \Magento\CatalogInventory\Model\Stock\Item setProductId(int $value)
 * @method \Magento\CatalogInventory\Model\Stock\Item setStockId(int $value)
 * @method float getQty()
 * @method \Magento\CatalogInventory\Model\Stock\Item setQty(float $value)
 * @method \Magento\CatalogInventory\Model\Stock\Item setMinQty(float $value)
 * @method int getUseConfigMinQty()
 * @method \Magento\CatalogInventory\Model\Stock\Item setUseConfigMinQty(int $value)
 * @method int getIsQtyDecimal()
 * @method \Magento\CatalogInventory\Model\Stock\Item setIsQtyDecimal(int $value)
 * @method \Magento\CatalogInventory\Model\Stock\Item setBackorders(int $value)
 * @method int getUseConfigBackorders()
 * @method \Magento\CatalogInventory\Model\Stock\Item setUseConfigBackorders(int $value)
 * @method \Magento\CatalogInventory\Model\Stock\Item setMinSaleQty(float $value)
 * @method int getUseConfigMinSaleQty()
 * @method \Magento\CatalogInventory\Model\Stock\Item setUseConfigMinSaleQty(int $value)
 * @method \Magento\CatalogInventory\Model\Stock\Item setMaxSaleQty(float $value)
 * @method int getUseConfigMaxSaleQty()
 * @method \Magento\CatalogInventory\Model\Stock\Item setUseConfigMaxSaleQty(int $value)
 * @method \Magento\CatalogInventory\Model\Stock\Item setIsInStock(int $value)
 * @method string getLowStockDate()
 * @method \Magento\CatalogInventory\Model\Stock\Item setLowStockDate(string $value)
 * @method \Magento\CatalogInventory\Model\Stock\Item setNotifyStockQty(float $value)
 * @method int getUseConfigNotifyStockQty()
 * @method \Magento\CatalogInventory\Model\Stock\Item setUseConfigNotifyStockQty(int $value)
 * @method \Magento\CatalogInventory\Model\Stock\Item setManageStock(int $value)
 * @method int getUseConfigManageStock()
 * @method \Magento\CatalogInventory\Model\Stock\Item setUseConfigManageStock(int $value)
 * @method int getStockStatusChangedAutomatically()
 * @method \Magento\CatalogInventory\Model\Stock\Item setStockStatusChangedAutomatically(int $value)
 * @method int getUseConfigQtyIncrements()
 * @method \Magento\CatalogInventory\Model\Stock\Item setUseConfigQtyIncrements(int $value)
 * @method \Magento\CatalogInventory\Model\Stock\Item setQtyIncrements(float $value)
 * @method int getUseConfigEnableQtyInc()
 * @method \Magento\CatalogInventory\Model\Stock\Item setUseConfigEnableQtyInc(int $value)
 * @method \Magento\CatalogInventory\Model\Stock\Item setEnableQtyIncrements(int $value)
 */
class Item extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Inventory options config path
     */
    const XML_PATH_GLOBAL = 'cataloginventory/options/';

    /**
     * Subtract config path
     */
    const XML_PATH_CAN_SUBTRACT = 'cataloginventory/options/can_subtract';

    /**
     * Back in stock config path
     */
    const XML_PATH_CAN_BACK_IN_STOCK = 'cataloginventory/options/can_back_in_stock';

    /**
     * Item options config path
     */
    const XML_PATH_ITEM = 'cataloginventory/item_options/';

    /**
     * Max qty config path
     */
    const XML_PATH_MIN_QTY = 'cataloginventory/item_options/min_qty';

    /**
     * Min sale qty config path
     */
    const XML_PATH_MIN_SALE_QTY = 'cataloginventory/item_options/min_sale_qty';

    /**
     * Max sale qty config path
     */
    const XML_PATH_MAX_SALE_QTY = 'cataloginventory/item_options/max_sale_qty';

    /**
     * Back orders config path
     */
    const XML_PATH_BACKORDERS = 'cataloginventory/item_options/backorders';

    /**
     * Notify stock config path
     */
    const XML_PATH_NOTIFY_STOCK_QTY = 'cataloginventory/item_options/notify_stock_qty';

    /**
     * Manage stock config path
     */
    const XML_PATH_MANAGE_STOCK = 'cataloginventory/item_options/manage_stock';

    /**
     * Enable qty increments config path
     */
    const XML_PATH_ENABLE_QTY_INCREMENTS = 'cataloginventory/item_options/enable_qty_increments';

    /**
     * Qty increments config path
     */
    const XML_PATH_QTY_INCREMENTS = 'cataloginventory/item_options/qty_increments';

    /**
     * Stock item entity code
     */
    const ENTITY = 'cataloginventory_stock_item';

    /**
     * Default stock id
     */
    const DEFAULT_STOCK_ID = 1;

    /**
     * @var array
     */
    private $_minSaleQtyCache = array();

    /**
     * @var float|false
     */
    protected $_qtyIncrements;

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'cataloginventory_stock_item';

    /**
     * Parameter name in event
     *
     * In observe method you can use $observer->getEvent()->getItem() in this case
     *
     * @var string
     */
    protected $_eventObject = 'item';

    /**
     * Customer group id
     *
     * @var int|null
     */
    protected $_customerGroupId;

    /**
     * Whether index events should be processed immediately
     *
     * @var bool
     */
    protected $_processIndexEvents = true;

    /**
     * Catalog inventory min sale qty
     *
     * @var \Magento\CatalogInventory\Helper\Minsaleqty
     */
    protected $_catalogInventoryMinsaleqty;

    /**
     * @var \Magento\CatalogInventory\Service\V1\StockItemService
     */
    protected $stockItemService;

    /**
     * @var \Magento\CatalogInventory\Model\Stock\ItemRegistry
     */
    protected $stockItemRegistry;

    /**
     * Core store config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * Store model manager
     *
     * @var \Magento\Framework\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\Locale\FormatInterface
     */
    protected $_localeFormat;

    /**
     * @var Status
     */
    protected $_stockStatus;

    /**
     * @var \Magento\CatalogInventory\Model\Indexer\Stock\Processor
     */
    protected $_stockIndexerProcessor;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Framework\Math\Division
     */
    protected $mathDivision;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $_localeDate;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\CatalogInventory\Model\Indexer\Stock\Processor $stockIndexerProcessor
     * @param Status $stockStatus
     * @param \Magento\CatalogInventory\Service\V1\StockItemService $stockItemService
     * @param ItemRegistry $stockItemRegistry
     * @param \Magento\CatalogInventory\Helper\Minsaleqty $catalogInventoryMinsaleqty
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Locale\FormatInterface $localeFormat
     * @param \Magento\Framework\Math\Division $mathDivision
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Framework\Model\Resource\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\Db $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\CatalogInventory\Model\Indexer\Stock\Processor $stockIndexerProcessor,
        Status $stockStatus,
        \Magento\CatalogInventory\Service\V1\StockItemService $stockItemService,
        \Magento\CatalogInventory\Model\Stock\ItemRegistry $stockItemRegistry,
        \Magento\CatalogInventory\Helper\Minsaleqty $catalogInventoryMinsaleqty,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\StoreManagerInterface $storeManager,
        \Magento\Framework\Locale\FormatInterface $localeFormat,
        \Magento\Framework\Math\Division $mathDivision,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Framework\Model\Resource\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\Db $resourceCollection = null,
        array $data = array()
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);

        $this->_customerSession = $customerSession;
        $this->_stockIndexerProcessor = $stockIndexerProcessor;
        $this->_stockStatus = $stockStatus;
        $this->stockItemService = $stockItemService;
        $this->stockItemRegistry = $stockItemRegistry;
        $this->_catalogInventoryMinsaleqty = $catalogInventoryMinsaleqty;
        $this->_scopeConfig = $scopeConfig;
        $this->_storeManager = $storeManager;
        $this->_localeFormat = $localeFormat;
        $this->mathDivision = $mathDivision;
        $this->_localeDate = $localeDate;
        $this->productFactory = $productFactory;
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
     * Retrieve stock identifier
     *
     * @todo multi stock
     * @return int
     */
    public function getStockId()
    {
        return self::DEFAULT_STOCK_ID;
    }

    /**
     * Retrieve Product Id data wrapper
     *
     * @return int
     */
    public function getProductId()
    {
        return $this->_getData('product_id');
    }

    /**
     * Load item data by product
     *
     * @param int|Product $product
     * @return $this
     */
    public function loadByProduct($product)
    {
        if ($product instanceof Product) {
            $product = $product->getId();
        }
        $this->_getResource()->loadByProductId($this, $product);
        $this->setOrigData();
        return $this;
    }

    /**
     * Subtract quote item quantity
     *
     * @param int|float $qty
     * @return $this
     */
    public function subtractQty($qty)
    {
        if ($this->canSubtractQty()) {
            $this->setQty($this->getQty() - $qty);
        }
        return $this;
    }

    /**
     * Check if is possible subtract value from item qty
     *
     * @return bool
     */
    public function canSubtractQty()
    {
        return $this->getManageStock() && $this->_scopeConfig->isSetFlag(
            self::XML_PATH_CAN_SUBTRACT,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Add quantity process
     *
     * @param float $qty
     * @return $this
     */
    public function addQty($qty)
    {
        if (!$this->getManageStock()) {
            return $this;
        }
        $config = $this->_scopeConfig->isSetFlag(
            self::XML_PATH_CAN_SUBTRACT,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        if (!$config) {
            return $this;
        }

        $this->setQty($this->getQty() + $qty);
        return $this;
    }

    /**
     * Retrieve Store Id (product or current)
     *
     * @return int
     */
    public function getStoreId()
    {
        $storeId = $this->getData('store_id');
        if (is_null($storeId)) {
            $storeId = $this->_storeManager->getStore()->getId();
            $this->setData('store_id', $storeId);
        }
        return $storeId;
    }

    /**
     * Retrieve minimal quantity available for item status in stock
     *
     * @return float
     */
    public function getMinQty()
    {
        if ($this->getUseConfigMinQty()) {
            $minQty = (float) $this->_scopeConfig->getValue(
                self::XML_PATH_MIN_QTY,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
        } else {
            $minQty = (float) $this->getData('min_qty');
        }
        return $minQty;
    }

    /**
     * Getter for customer group id, return current customer group if not set
     *
     * @return int
     */
    public function getCustomerGroupId()
    {
        if ($this->_customerGroupId === null) {
            return $this->_customerSession->getCustomerGroupId();
        }
        return $this->_customerGroupId;
    }

    /**
     * Setter for customer group id
     *
     * @param int $value Value of customer group id
     * @return $this
     */
    public function setCustomerGroupId($value)
    {
        $this->_customerGroupId = $value;
        return $this;
    }

    /**
     * Retrieve Minimum Qty Allowed in Shopping Cart or NULL when there is no limitation
     *
     * @return float|null
     */
    public function getMinSaleQty()
    {
        $customerGroupId = $this->getCustomerGroupId();
        if (!isset($this->_minSaleQtyCache[$customerGroupId])) {
            if ($this->getUseConfigMinSaleQty()) {
                $minSaleQty = $this->_catalogInventoryMinsaleqty->getConfigValue($customerGroupId);
            } else {
                $minSaleQty = $this->getData('min_sale_qty');
            }
            $this->_minSaleQtyCache[$customerGroupId] = empty($minSaleQty) ? 0 : (float) $minSaleQty;
        }

        return $this->_minSaleQtyCache[$customerGroupId] ? $this->_minSaleQtyCache[$customerGroupId] : null;
    }

    /**
     * Retrieve Maximum Qty Allowed in Shopping Cart data wrapper
     *
     * @return float
     */
    public function getMaxSaleQty()
    {
        if ($this->getUseConfigMaxSaleQty()) {
            $maxSaleQty = (float) $this->_scopeConfig->getValue(
                self::XML_PATH_MAX_SALE_QTY,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
        } else {
            $maxSaleQty = (float) $this->getData('max_sale_qty');
        }
        return $maxSaleQty;
    }

    /**
     * Retrieve Notify for Quantity Below data wrapper
     *
     * @return float
     */
    public function getNotifyStockQty()
    {
        if ($this->getUseConfigNotifyStockQty()) {
            return (float) $this->_scopeConfig->getValue(
                self::XML_PATH_NOTIFY_STOCK_QTY,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
        }
        return (float) $this->getData('notify_stock_qty');
    }

    /**
     * Retrieve whether Quantity Increments is enabled
     *
     * @return bool
     */
    public function getEnableQtyIncrements()
    {
        if ($this->getUseConfigEnableQtyInc()) {
            return $this->_scopeConfig->isSetFlag(
                self::XML_PATH_ENABLE_QTY_INCREMENTS,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
        }
        return (bool) $this->getData('enable_qty_increments');
    }

    /**
     * Retrieve Quantity Increments data wrapper
     *
     * @return float|false
     */
    public function getQtyIncrements()
    {
        if ($this->_qtyIncrements === null) {
            if ($this->getEnableQtyIncrements()) {
                if ($this->getUseConfigQtyIncrements()) {
                    $this->_qtyIncrements = (float) $this->_scopeConfig->getValue(
                        self::XML_PATH_QTY_INCREMENTS,
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                    );
                } else {
                    $this->_qtyIncrements = (float) $this->getData('qty_increments');
                }
            }
            if ($this->_qtyIncrements <= 0) {
                $this->_qtyIncrements = false;
            }
        }
        return $this->_qtyIncrements;
    }

    /**
     * Retrieve Default Quantity Increments data wrapper
     *
     * @deprecated since 1.7.0.0
     * @return int|false
     */
    public function getDefaultQtyIncrements()
    {
        $isEnabledQtyIncrements = $this->_scopeConfig->isSetFlag(
            self::XML_PATH_ENABLE_QTY_INCREMENTS,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        $result = false;
        if ($isEnabledQtyIncrements) {
            $result = (int) $this->_scopeConfig->getValue(
                self::XML_PATH_QTY_INCREMENTS,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
        }

        return $result;
    }

    /**
     * Retrieve backorders status
     *
     * @return int
     */
    public function getBackorders()
    {
        if ($this->getUseConfigBackorders()) {
            return (int) $this->_scopeConfig->getValue(
                self::XML_PATH_BACKORDERS,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
        }
        return $this->getData('backorders');
    }

    /**
     * Retrieve Manage Stock data wrapper
     *
     * @return int
     */
    public function getManageStock()
    {
        if ($this->getUseConfigManageStock()) {
            return (int) $this->_scopeConfig->isSetFlag(
                self::XML_PATH_MANAGE_STOCK,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
        }
        return $this->getData('manage_stock');
    }

    /**
     * Retrieve can Back in stock
     *
     * @return bool
     */
    public function getCanBackInStock()
    {
        return $this->_scopeConfig->isSetFlag(
            self::XML_PATH_CAN_BACK_IN_STOCK,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Check quantity
     *
     * @param int|float $qty
     * @exception \Magento\Framework\Model\Exception
     * @return bool
     */
    public function checkQty($qty)
    {
        if (!$this->_isQtyCheckApplicable()) {
            return true;
        }

        if ($this->getQty() - $this->getMinQty() - $qty < 0) {
            switch ($this->getBackorders()) {
                case \Magento\CatalogInventory\Model\Stock::BACKORDERS_YES_NONOTIFY:
                case \Magento\CatalogInventory\Model\Stock::BACKORDERS_YES_NOTIFY:
                    break;
                default:
                    return false;
            }
        }
        return true;
    }

    /**
     * Returns suggested qty that satisfies qty increments and minQty/maxQty/minSaleQty/maxSaleQty conditions
     * or original qty if such value does not exist
     *
     * @param int|float $qty
     * @return int|float
     */
    public function suggestQty($qty)
    {
        // We do not manage stock
        if ($qty <= 0 || !$this->getManageStock()) {
            return $qty;
        }

        $qtyIncrements = (int)$this->getQtyIncrements();
        // Currently only integer increments supported
        if ($qtyIncrements < 2) {
            return $qty;
        }

        $minQty = max($this->getMinSaleQty(), $qtyIncrements);
        $divisibleMin = ceil($minQty / $qtyIncrements) * $qtyIncrements;

        $maxQty = min($this->getQty() - $this->getMinQty(), $this->getMaxSaleQty());
        $divisibleMax = floor($maxQty / $qtyIncrements) * $qtyIncrements;

        if ($qty < $minQty || $qty > $maxQty || $divisibleMin > $divisibleMax) {
            // Do not perform rounding for qty that does not satisfy min/max conditions to not confuse customer
            return $qty;
        }

        // Suggest value closest to given qty
        $closestDivisibleLeft = floor($qty / $qtyIncrements) * $qtyIncrements;
        $closestDivisibleRight = $closestDivisibleLeft + $qtyIncrements;
        $acceptableLeft = min(max($divisibleMin, $closestDivisibleLeft), $divisibleMax);
        $acceptableRight = max(min($divisibleMax, $closestDivisibleRight), $divisibleMin);
        return abs($acceptableLeft - $qty) < abs($acceptableRight - $qty) ? $acceptableLeft : $acceptableRight;
    }

    /**
     * Checking quote item quantity
     *
     * Second parameter of this method specifies quantity of this product in whole shopping cart
     * which should be checked for stock availability
     *
     * @param int|float $qty quantity of this item (item qty x parent item qty)
     * @param int|float $summaryQty quantity of this product
     * @param int|float $origQty original qty of item (not multiplied on parent item qty)
     * @return \Magento\Framework\Object
     */
    public function checkQuoteItemQty($qty, $summaryQty, $origQty = 0)
    {
        $result = new \Magento\Framework\Object();
        $result->setHasError(false);

        if (!is_numeric($qty)) {
            $qty = $this->_localeFormat->getNumber($qty);
        }

        /**
         * Check quantity type
         */
        $result->setItemIsQtyDecimal($this->getIsQtyDecimal());

        if (!$this->getIsQtyDecimal()) {
            $result->setHasQtyOptionUpdate(true);
            $qty = intval($qty);

            /**
             * Adding stock data to quote item
             */
            $result->setItemQty($qty);

            if (!is_numeric($qty)) {
                $qty = $this->_localeFormat->getNumber($qty);
            }
            $origQty = intval($origQty);
            $result->setOrigQty($origQty);
        }

        if ($this->getMinSaleQty() && $qty < $this->getMinSaleQty()) {
            $result->setHasError(true)
                ->setMessage(__('The fewest you may purchase is %1.', $this->getMinSaleQty() * 1))
                ->setErrorCode('qty_min')
                ->setQuoteMessage(__('Please correct the quantity for some products.'))
                ->setQuoteMessageIndex('qty');
            return $result;
        }

        if ($this->getMaxSaleQty() && $qty > $this->getMaxSaleQty()) {
            $result->setHasError(true)
                ->setMessage(__('The most you may purchase is %1.', $this->getMaxSaleQty() * 1))
                ->setErrorCode('qty_max')
                ->setQuoteMessage(__('Please correct the quantity for some products.'))
                ->setQuoteMessageIndex('qty');
            return $result;
        }

        $result->addData($this->checkQtyIncrements($qty)->getData());
        if ($result->getHasError()) {
            return $result;
        }

        if (!$this->getManageStock()) {
            return $result;
        }

        if (!$this->getIsInStock()) {
            $result->setHasError(true)
                ->setMessage(__('This product is out of stock.'))
                ->setQuoteMessage(__('Some of the products are currently out of stock.'))
                ->setQuoteMessageIndex('stock');
            $result->setItemUseOldQty(true);
            return $result;
        }

        if (!$this->checkQty($summaryQty) || !$this->checkQty($qty)) {
            $message = __('We don\'t have as many "%1" as you requested.', $this->getProductName());
            $result->setHasError(true)->setMessage($message)->setQuoteMessage($message)->setQuoteMessageIndex('qty');
            return $result;
        } else {
            if ($this->getQty() - $summaryQty < 0) {
                if ($this->getProductName()) {
                    if ($this->getIsChildItem()) {
                        $backOrderQty = $this->getQty() > 0 ? ($summaryQty - $this->getQty()) * 1 : $qty * 1;
                        if ($backOrderQty > $qty) {
                            $backOrderQty = $qty;
                        }

                        $result->setItemBackorders($backOrderQty);
                    } else {
                        $orderedItems = (int)$this->getOrderedItems();

                        // Available item qty in stock excluding item qty in other quotes
                        $qtyAvailable = ($this->getQty() - ($summaryQty - $qty))* 1;
                        if ($qtyAvailable > 0) {
                            $backOrderQty = $qty * 1 - $qtyAvailable;
                        } else {
                            $backOrderQty = $qty * 1;
                        }

                        if ($backOrderQty > 0) {
                            $result->setItemBackorders($backOrderQty);
                        }
                        $this->setOrderedItems($orderedItems + $qty);
                    }

                    if ($this->getBackorders() == \Magento\CatalogInventory\Model\Stock::BACKORDERS_YES_NOTIFY) {
                        if (!$this->getIsChildItem()) {
                            $result->setMessage(
                                __(
                                    'We don\'t have as many "%1" as you requested, but we\'ll back order the remaining %2.',
                                    $this->getProductName(),
                                    $backOrderQty * 1
                                )
                            );
                        } else {
                            $result->setMessage(
                                __(
                                    'We don\'t have "%1" in the requested quantity, so we\'ll back order the remaining %2.',
                                    $this->getProductName(),
                                    $backOrderQty * 1
                                )
                            );
                        }
                    } elseif ($this->_hasDefaultNotificationMessage()) {
                        $result->setMessage(
                            __('We don\'t have as many "%1" as you requested.', $this->getProductName())
                        );
                    }
                }
            } else {
                if (!$this->getIsChildItem()) {
                    $this->setOrderedItems($qty + (int) $this->getOrderedItems());
                }
            }
        }

        return $result;
    }

    /**
     * Check qty increments
     *
     * @param int|float $qty
     * @return \Magento\Framework\Object
     */
    public function checkQtyIncrements($qty)
    {
        $result = new \Magento\Framework\Object();
        if ($this->getSuppressCheckQtyIncrements()) {
            return $result;
        }

        $qtyIncrements = $this->getQtyIncrements();

        if ($qtyIncrements && $this->mathDivision->getExactDivision($qty, $qtyIncrements) != 0) {
            $result->setHasError(true)
                ->setQuoteMessage(__('Please correct the quantity for some products.'))
                ->setErrorCode('qty_increments')
                ->setQuoteMessageIndex('qty');
            if ($this->getIsChildItem()) {
                $result->setMessage(
                    __('You can buy %1 only in increments of %2.', $this->getProductName(), $qtyIncrements * 1)
                );
            } else {
                $result->setMessage(__('You can buy this product only in increments of %1.', $qtyIncrements * 1));
            }
        }

        return $result;
    }

    /**
     * Add join for catalog in stock field to product collection
     *
     * @param \Magento\Catalog\Model\Resource\Product\Collection $productCollection
     * @return $this
     */
    public function addCatalogInventoryToProductCollection($productCollection)
    {
        $this->_getResource()->addCatalogInventoryToProductCollection($productCollection);
        return $this;
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
     * Before save prepare process
     *
     * @return $this
     */
    protected function _beforeSave()
    {
        parent::_beforeSave();
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $this->productFactory->create();
        $product->load($this->getProductId());
        $typeId = $product->getTypeId() ? $product->getTypeId() : $this->getTypeId();

        $isQty = $this->stockItemService->isQty($typeId);

        if ($isQty) {
            if (!$this->getId()) {
                $this->processIsInStock();
            }
            if ($this->getManageStock() && !$this->verifyStock()) {
                $this->setIsInStock(false)->setStockStatusChangedAutomaticallyFlag(true);
            }

            // if qty is below notify qty, update the low stock date to today date otherwise set null
            $this->setLowStockDate(null);
            if ($this->verifyNotification()) {
                $this->setLowStockDate(
                    $this->_localeDate->date(null, null, null, false)
                        ->toString(\Magento\Framework\Stdlib\DateTime::DATETIME_INTERNAL_FORMAT)
                );
            }

            $this->setStockStatusChangedAuto(0);
            if ($this->hasStockStatusChangedAutomaticallyFlag()) {
                $this->setStockStatusChangedAuto((int) $this->getStockStatusChangedAutomaticallyFlag());
            }
        } else {
            $this->setQty(0);
        }

        return $this;
    }

    /**
     * Check if item should be in stock or out of stock based on $qty param of existing item qty
     *
     * @param float|null $qty
     * @return bool true - item in stock | false - item out of stock
     */
    public function verifyStock($qty = null)
    {
        if ($qty === null) {
            $qty = $this->getQty();
        }
        if ($qty !== null
            && $this->getBackorders() == \Magento\CatalogInventory\Model\Stock::BACKORDERS_NO
            && $qty <= $this->getMinQty()
        ) {
            return false;
        }
        return true;
    }

    /**
     * Check if item qty require stock status notification
     *
     * @param float|null $qty
     * @return bool (true - if require, false - if not require)
     */
    public function verifyNotification($qty = null)
    {
        if ($qty === null) {
            $qty = $this->getQty();
        }
        return (float) $qty < $this->getNotifyStockQty();
    }

    /**
     * Reindex CatalogInventory save event
     *
     * @return $this
     */
    protected function _afterSave()
    {
        parent::_afterSave();

        if ($this->_processIndexEvents) {
            $this->_stockIndexerProcessor->reindexRow($this->getProductId());
        }
        return $this;
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
        return $this->_getData('is_in_stock');
    }

    /**
     * Add product data to stock item
     *
     * @param Product $product
     * @return $this
     */
    public function setProduct($product)
    {
        $this->setProductId($product->getId())
            ->setProductName($product->getName())
            ->setStoreId($product->getStoreId())
            ->setProductTypeId($product->getTypeId())
            ->setProductStatusChanged($product->dataHasChangedFor('status'))
            ->setProductChangedWebsites($product->getIsChangedWebsites());

        return $this;
    }

    /**
     * Retrieve stock qty whether product is composite or no
     *
     * @return float
     */
    public function getStockQty()
    {
        if (!$this->hasStockQty()) {
            $this->setStockQty(0);

            /** @var Product $product */
            $product = $this->productFactory->create();
            $product->load($this->getProductId());
            // prevent possible recursive loop
            if (!$product->isComposite()) {
                $stockQty = $this->getQty();
            } else {
                $stockQty = null;
                $productsByGroups = $product->getTypeInstance()->getProductsToPurchaseByReqGroups($product);
                foreach ($productsByGroups as $productsInGroup) {
                    $qty = 0;
                    foreach ($productsInGroup as $childProduct) {
                        $qty += $this->stockItemRegistry->retrieve($childProduct->getId())->getStockQty();
                    }
                    if (null === $stockQty || $qty < $stockQty) {
                        $stockQty = $qty;
                    }
                }
            }
            $stockQty = (float) $stockQty;
            if ($stockQty < 0 || !$this->getManageStock() || !$this->getIsInStock() || !$product->isSaleable()) {
                $stockQty = 0;
            }
            $this->setStockQty($stockQty);
        }
        return (float) $this->getData('stock_qty');
    }

    /**
     * Set whether index events should be processed immediately
     *
     * @param bool $process
     * @return $this
     */
    public function setProcessIndexEvents($process = true)
    {
        $this->_processIndexEvents = $process;
        return $this;
    }

    /**
     * Check if qty check can be skipped
     *
     * @return bool
     */
    protected function _isQtyCheckApplicable()
    {
        return (bool) $this->getManageStock();
    }

    /**
     * Check if notification message should be added despite of backorders notification flag
     *
     * @return bool
     */
    protected function _hasDefaultNotificationMessage()
    {
        return false;
    }

    /**
     * Process data and set in_stock availability
     *
     * @return $this
     */
    public function processIsInStock()
    {
        $this->setData('is_in_stock', $this->verifyStock() ? Status::STATUS_IN_STOCK : Status::STATUS_OUT_OF_STOCK);
        return $this;
    }
}
