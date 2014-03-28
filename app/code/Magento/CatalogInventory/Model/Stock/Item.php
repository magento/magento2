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
 * @method \Magento\CatalogInventory\Model\Resource\Stock\Item _getResource()
 * @method \Magento\CatalogInventory\Model\Resource\Stock\Item getResource()
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
 *
 * @category    Magento
 * @package     Magento_CatalogInventory
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Item extends \Magento\Model\AbstractModel
{
    const XML_PATH_GLOBAL = 'cataloginventory/options/';

    const XML_PATH_CAN_SUBTRACT = 'cataloginventory/options/can_subtract';

    const XML_PATH_CAN_BACK_IN_STOCK = 'cataloginventory/options/can_back_in_stock';

    const XML_PATH_ITEM = 'cataloginventory/item_options/';

    const XML_PATH_MIN_QTY = 'cataloginventory/item_options/min_qty';

    const XML_PATH_MIN_SALE_QTY = 'cataloginventory/item_options/min_sale_qty';

    const XML_PATH_MAX_SALE_QTY = 'cataloginventory/item_options/max_sale_qty';

    const XML_PATH_BACKORDERS = 'cataloginventory/item_options/backorders';

    const XML_PATH_NOTIFY_STOCK_QTY = 'cataloginventory/item_options/notify_stock_qty';

    const XML_PATH_MANAGE_STOCK = 'cataloginventory/item_options/manage_stock';

    const XML_PATH_ENABLE_QTY_INCREMENTS = 'cataloginventory/item_options/enable_qty_increments';

    const XML_PATH_QTY_INCREMENTS = 'cataloginventory/item_options/qty_increments';

    const ENTITY = 'cataloginventory_stock_item';

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
     * Associated product instance
     *
     * @var Product
     */
    protected $_productInstance = null;

    /**
     * Customer group id
     *
     * @var int|null
     */
    protected $_customerGroupId = null;

    /**
     * Whether index events should be processed immediately
     *
     * @var bool
     */
    protected $_processIndexEvents = true;

    /**
     * Catalog inventory minsaleqty
     *
     * @var \Magento\CatalogInventory\Helper\Minsaleqty
     */
    protected $_catalogInventoryMinsaleqty;

    /**
     * Catalog inventory data
     *
     * @var \Magento\CatalogInventory\Helper\Data
     */
    protected $_catalogInventoryData;

    /**
     * Core store config
     *
     * @var \Magento\Core\Model\Store\Config
     */
    protected $_coreStoreConfig;

    /**
     * Store model manager
     *
     * @var \Magento\Core\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Locale\FormatInterface
     */
    protected $_localeFormat;

    /**
     * @var Status
     */
    protected $_stockStatus;

    /**
     * @var \Magento\Index\Model\Indexer
     */
    protected $_indexer;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Math\Division
     */
    protected $mathDivision;

    /**
     * @var \Magento\Stdlib\DateTime\TimezoneInterface
     */
    protected $_localeDate;

    /**
     * @param \Magento\Model\Context $context
     * @param \Magento\Registry $registry
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Index\Model\Indexer $indexer
     * @param Status $stockStatus
     * @param \Magento\CatalogInventory\Helper\Data $catalogInventoryData
     * @param \Magento\CatalogInventory\Helper\Minsaleqty $catalogInventoryMinsaleqty
     * @param \Magento\Core\Model\Store\Config $coreStoreConfig
     * @param \Magento\Core\Model\StoreManagerInterface $storeManager
     * @param \Magento\Locale\FormatInterface $localeFormat
     * @param \Magento\Math\Division $mathDivision
     * @param \Magento\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Model\Resource\AbstractResource $resource
     * @param \Magento\Data\Collection\Db $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Model\Context $context,
        \Magento\Registry $registry,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Index\Model\Indexer $indexer,
        Status $stockStatus,
        \Magento\CatalogInventory\Helper\Data $catalogInventoryData,
        \Magento\CatalogInventory\Helper\Minsaleqty $catalogInventoryMinsaleqty,
        \Magento\Core\Model\Store\Config $coreStoreConfig,
        \Magento\Core\Model\StoreManagerInterface $storeManager,
        \Magento\Locale\FormatInterface $localeFormat,
        \Magento\Math\Division $mathDivision,
        \Magento\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Model\Resource\AbstractResource $resource = null,
        \Magento\Data\Collection\Db $resourceCollection = null,
        array $data = array()
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);

        $this->_customerSession = $customerSession;
        $this->_indexer = $indexer;
        $this->_stockStatus = $stockStatus;
        $this->_catalogInventoryData = $catalogInventoryData;
        $this->_catalogInventoryMinsaleqty = $catalogInventoryMinsaleqty;
        $this->_coreStoreConfig = $coreStoreConfig;
        $this->_storeManager = $storeManager;
        $this->_localeFormat = $localeFormat;
        $this->mathDivision = $mathDivision;
        $this->_localeDate = $localeDate;
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
        return 1;
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
        return $this->getManageStock() && $this->_coreStoreConfig->getConfigFlag(self::XML_PATH_CAN_SUBTRACT);
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
        $config = $this->_coreStoreConfig->getConfigFlag(self::XML_PATH_CAN_SUBTRACT);
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
     * Adding stock data to product
     *
     * @param Product $product
     * @return $this
     */
    public function assignProduct(Product $product)
    {
        if (!$this->getId() || !$this->getProductId()) {
            $this->_getResource()->loadByProductId($this, $product->getId());
            $this->setOrigData();
        }

        $this->setProduct($product);
        $product->setStockItem($this);

        $product->setIsInStock($this->getIsInStock());

        $this->_stockStatus->assignProduct($product, $this->getStockId(), $this->getStockStatus());

        return $this;
    }

    /**
     * Retrieve minimal quantity available for item status in stock
     *
     * @return float
     */
    public function getMinQty()
    {
        return (double)($this->getUseConfigMinQty() ? $this->_coreStoreConfig->getConfig(
            self::XML_PATH_MIN_QTY
        ) : $this->getData(
            'min_qty'
        ));
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
            $minSaleQty = $this->getUseConfigMinSaleQty() ? $this->_catalogInventoryMinsaleqty->getConfigValue(
                $customerGroupId
            ) : $this->getData(
                'min_sale_qty'
            );

            $this->_minSaleQtyCache[$customerGroupId] = empty($minSaleQty) ? 0 : (double)$minSaleQty;
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
        return (double)($this->getUseConfigMaxSaleQty() ? $this->_coreStoreConfig->getConfig(
            self::XML_PATH_MAX_SALE_QTY
        ) : $this->getData(
            'max_sale_qty'
        ));
    }

    /**
     * Retrieve Notify for Quantity Below data wrapper
     *
     * @return float
     */
    public function getNotifyStockQty()
    {
        if ($this->getUseConfigNotifyStockQty()) {
            return (double)$this->_coreStoreConfig->getConfig(self::XML_PATH_NOTIFY_STOCK_QTY);
        }
        return (double)$this->getData('notify_stock_qty');
    }

    /**
     * Retrieve whether Quantity Increments is enabled
     *
     * @return bool
     */
    public function getEnableQtyIncrements()
    {
        if ($this->getUseConfigEnableQtyInc()) {
            return $this->_coreStoreConfig->getConfigFlag(self::XML_PATH_ENABLE_QTY_INCREMENTS);
        }
        return (bool)$this->getData('enable_qty_increments');
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
                $this->_qtyIncrements = (double)($this
                    ->getUseConfigQtyIncrements() ? $this
                    ->_coreStoreConfig
                    ->getConfig(self::XML_PATH_QTY_INCREMENTS) : $this->getData('qty_increments'));
                if ($this->_qtyIncrements <= 0) {
                    $this->_qtyIncrements = false;
                }
            } else {
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
        return $this->_coreStoreConfig->getConfigFlag(
            self::XML_PATH_ENABLE_QTY_INCREMENTS
        ) ? (int)$this->_coreStoreConfig->getConfig(
            self::XML_PATH_QTY_INCREMENTS
        ) : false;
    }

    /**
     * Retrieve backorders status
     *
     * @return int
     */
    public function getBackorders()
    {
        if ($this->getUseConfigBackorders()) {
            return (int)$this->_coreStoreConfig->getConfig(self::XML_PATH_BACKORDERS);
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
            return (int)$this->_coreStoreConfig->getConfigFlag(self::XML_PATH_MANAGE_STOCK);
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
        return $this->_coreStoreConfig->getConfigFlag(self::XML_PATH_CAN_BACK_IN_STOCK);
    }

    /**
     * Check quantity
     *
     * @param int|float $qty
     * @exception \Magento\Model\Exception
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
                    break;
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
     * @return \Magento\Object
     */
    public function checkQuoteItemQty($qty, $summaryQty, $origQty = 0)
    {
        $result = new \Magento\Object();
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
            $result->setHasError(
                true
            )->setMessage(
                __('The fewest you may purchase is %1.', $this->getMinSaleQty() * 1)
            )->setErrorCode(
                'qty_min'
            )->setQuoteMessage(
                __('Please correct the quantity for some products.')
            )->setQuoteMessageIndex(
                'qty'
            );
            return $result;
        }

        if ($this->getMaxSaleQty() && $qty > $this->getMaxSaleQty()) {
            $result->setHasError(
                true
            )->setMessage(
                __('The most you may purchase is %1.', $this->getMaxSaleQty() * 1)
            )->setErrorCode(
                'qty_max'
            )->setQuoteMessage(
                __('Please correct the quantity for some products.')
            )->setQuoteMessageIndex(
                'qty'
            );
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
            $result->setHasError(
                true
            )->setMessage(
                __('This product is out of stock.')
            )->setQuoteMessage(
                __('Some of the products are currently out of stock.')
            )->setQuoteMessageIndex(
                'stock'
            );
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
                        $backorderQty = $this->getQty() > 0 ? ($summaryQty - $this->getQty()) * 1 : $qty * 1;
                        if ($backorderQty > $qty) {
                            $backorderQty = $qty;
                        }

                        $result->setItemBackorders($backorderQty);
                    } else {
                        $orderedItems = $this->getOrderedItems();
                        $itemsLeft = $this->getQty() > $orderedItems ? ($this->getQty() - $orderedItems) * 1 : 0;
                        $backorderQty = $itemsLeft > 0 ? ($qty - $itemsLeft) * 1 : $qty * 1;

                        if ($backorderQty > 0) {
                            $result->setItemBackorders($backorderQty);
                        }
                        $this->setOrderedItems($orderedItems + $qty);
                    }

                    if ($this->getBackorders() == \Magento\CatalogInventory\Model\Stock::BACKORDERS_YES_NOTIFY) {
                        if (!$this->getIsChildItem()) {
                            $result->setMessage(
                                __(
                                    'We don\'t have as many "%1" as you requested, but we\'ll back order the remaining %2.',
                                    $this->getProductName(),
                                    $backorderQty * 1
                                )
                            );
                        } else {
                            $result->setMessage(
                                __(
                                    'We don\'t have "%1" in the requested quantity, so we\'ll back order the remaining %2.',
                                    $this->getProductName(),
                                    $backorderQty * 1
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
                    $this->setOrderedItems($qty + (int)$this->getOrderedItems());
                }
            }
        }

        return $result;
    }

    /**
     * Check qty increments
     *
     * @param int|float $qty
     * @return \Magento\Object
     */
    public function checkQtyIncrements($qty)
    {
        $result = new \Magento\Object();
        if ($this->getSuppressCheckQtyIncrements()) {
            return $result;
        }

        $qtyIncrements = $this->getQtyIncrements();

        if ($qtyIncrements && $this->mathDivision->getExactDivision($qty, $qtyIncrements) != 0) {
            $result->setHasError(
                true
            )->setQuoteMessage(
                __('Please correct the quantity for some products.')
            )->setErrorCode(
                'qty_increments'
            )->setQuoteMessageIndex(
                'qty'
            );
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
        // see if quantity is defined for this item type
        $typeId = $this->getTypeId();
        if ($productTypeId = $this->getProductTypeId()) {
            $typeId = $productTypeId;
        }

        $isQty = $this->_catalogInventoryData->isQty($typeId);

        if ($isQty) {
            if ($this->getManageStock() && !$this->verifyStock()) {
                $this->setIsInStock(false)->setStockStatusChangedAutomaticallyFlag(true);
            }

            // if qty is below notify qty, update the low stock date to today date otherwise set null
            $this->setLowStockDate(null);
            if ($this->verifyNotification()) {
                $this->setLowStockDate(
                    $this->_localeDate->date(
                        null,
                        null,
                        null,
                        false
                    )->toString(
                        \Magento\Stdlib\DateTime::DATETIME_INTERNAL_FORMAT
                    )
                );
            }

            $this->setStockStatusChangedAuto(0);
            if ($this->hasStockStatusChangedAutomaticallyFlag()) {
                $this->setStockStatusChangedAuto((int)$this->getStockStatusChangedAutomaticallyFlag());
            }
        } else {
            $this->setQty(0);
        }

        return $this;
    }

    /**
     * Chceck if item should be in stock or out of stock based on $qty param of existing item qty
     *
     * @param float|null $qty
     * @return bool true - item in stock | false - item out of stock
     */
    public function verifyStock($qty = null)
    {
        if ($qty === null) {
            $qty = $this->getQty();
        }
        if ($qty !== null &&
            $this->getBackorders() == \Magento\CatalogInventory\Model\Stock::BACKORDERS_NO &&
            $qty <= $this->getMinQty()
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
        return (double)$qty < $this->getNotifyStockQty();
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
            $this->_indexer->processEntityAction($this, self::ENTITY, \Magento\Index\Model\Event::TYPE_SAVE);
        } else {
            $this->_indexer->logEvent($this, self::ENTITY, \Magento\Index\Model\Event::TYPE_SAVE);
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
        $this->setProductId(
            $product->getId()
        )->setProductName(
            $product->getName()
        )->setStoreId(
            $product->getStoreId()
        )->setProductName(
            $product->getName()
        )->setProductTypeId(
            $product->getTypeId()
        )->setProductStatusChanged(
            $product->dataHasChangedFor('status')
        )->setProductChangedWebsites(
            $product->getIsChangedWebsites()
        );

        $this->_productInstance = $product;

        return $this;
    }

    /**
     * Returns product instance
     *
     * @return Product|null
     */
    public function getProduct()
    {
        return $this->_productInstance ? $this->_productInstance : $this->_getData('product');
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
            // prevent possible recursive loop
            $product = $this->_productInstance;
            if (!$product || !$product->isComposite()) {
                $stockQty = $this->getQty();
            } else {
                $stockQty = null;
                $productsByGroups = $product->getTypeInstance()->getProductsToPurchaseByReqGroups($product);
                foreach ($productsByGroups as $productsInGroup) {
                    $qty = 0;
                    foreach ($productsInGroup as $childProduct) {
                        if ($childProduct->hasStockItem()) {
                            $qty += $childProduct->getStockItem()->getStockQty();
                        }
                    }
                    if (is_null($stockQty) || $qty < $stockQty) {
                        $stockQty = $qty;
                    }
                }
            }
            $stockQty = (double)$stockQty;
            if ($stockQty < 0 ||
                !$this->getManageStock() ||
                !$this->getIsInStock() ||
                $product && !$product->isSaleable()
            ) {
                $stockQty = 0;
            }
            $this->setStockQty($stockQty);
        }
        return $this->getData('stock_qty');
    }

    /**
     * Reset model data
     * @return $this
     */
    public function reset()
    {
        if ($this->_productInstance) {
            $this->_productInstance = null;
        }
        return $this;
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
        return (bool)$this->getManageStock();
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
}
