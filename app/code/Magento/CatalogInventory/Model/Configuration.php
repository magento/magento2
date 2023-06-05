<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Model;

use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\CatalogInventory\Helper\Minsaleqty as MinsaleqtyHelper;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Catalog\Model\ProductTypes\ConfigInterface;
use Magento\Framework\ObjectManager\ResetAfterRequestInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class Configuration implements StockConfigurationInterface, ResetAfterRequestInterface
{
    /**
     * Default website id
     */
    public const DEFAULT_WEBSITE_ID = 1;

    /**
     * Inventory options config path
     */
    public const XML_PATH_GLOBAL = 'cataloginventory/options/';

    /**
     * Subtract config path
     */
    public const XML_PATH_CAN_SUBTRACT = 'cataloginventory/options/can_subtract';

    /**
     * Back in stock config path
     */
    public const XML_PATH_CAN_BACK_IN_STOCK = 'cataloginventory/options/can_back_in_stock';

    /**
     * Item options config path
     */
    public const XML_PATH_ITEM = 'cataloginventory/item_options/';

    /**
     * Max qty config path
     */
    public const XML_PATH_MIN_QTY = 'cataloginventory/item_options/min_qty';

    /**
     * Min sale qty config path
     */
    public const XML_PATH_MIN_SALE_QTY = 'cataloginventory/item_options/min_sale_qty';

    /**
     * Max sale qty config path
     */
    public const XML_PATH_MAX_SALE_QTY = 'cataloginventory/item_options/max_sale_qty';

    /**
     * Back orders config path
     */
    public const XML_PATH_BACKORDERS = 'cataloginventory/item_options/backorders';

    /**
     * Notify stock config path
     */
    public const XML_PATH_NOTIFY_STOCK_QTY = 'cataloginventory/item_options/notify_stock_qty';

    /**
     * Manage stock config path
     */
    public const XML_PATH_MANAGE_STOCK = 'cataloginventory/item_options/manage_stock';

    /**
     * Enable qty increments config path
     */
    public const XML_PATH_ENABLE_QTY_INCREMENTS = 'cataloginventory/item_options/enable_qty_increments';

    /**
     * Qty increments config path
     */
    public const XML_PATH_QTY_INCREMENTS = 'cataloginventory/item_options/qty_increments';

    /**
     * Show out of stock config path
     */
    public const XML_PATH_SHOW_OUT_OF_STOCK = 'cataloginventory/options/show_out_of_stock';

    /**
     * Auto return config path
     */
    public const XML_PATH_ITEM_AUTO_RETURN = 'cataloginventory/item_options/auto_return';

    /**
     * Path to configuration option 'Display product stock status'
     */
    public const XML_PATH_DISPLAY_PRODUCT_STOCK_STATUS = 'cataloginventory/options/display_product_stock_status';

    /**
     * Threshold qty config path
     */
    public const XML_PATH_STOCK_THRESHOLD_QTY = 'cataloginventory/options/stock_threshold_qty';

    /**
     * @var ConfigInterface
     */
    protected $config;

    /**
     * Core store config
     *
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var MinsaleqtyHelper
     */
    protected $minsaleqtyHelper;

    /**
     * All product types registry in scope of quantity availability
     *
     * @var array|null
     */
    protected $isQtyTypeIds;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Configuration constructor.
     *
     * @param ConfigInterface $config
     * @param ScopeConfigInterface $scopeConfig
     * @param MinsaleqtyHelper $minsaleqtyHelper
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ConfigInterface $config,
        ScopeConfigInterface $scopeConfig,
        MinsaleqtyHelper $minsaleqtyHelper,
        StoreManagerInterface $storeManager
    ) {
        $this->config = $config;
        $this->scopeConfig = $scopeConfig;
        $this->minsaleqtyHelper = $minsaleqtyHelper;
        $this->storeManager = $storeManager;
    }

    /**
     * @inheritDoc
     */
    public function _resetState(): void
    {
        $this->isQtyTypeIds = null;
    }

    /**
     * @inheritdoc
     */
    public function getDefaultScopeId()
    {
        // TODO: should be fixed in MAGETWO-46043
        // "0" is id of admin website, which is used in backend during save entity
        return 0;
    }

    /**
     * Is Qty Type Ids
     *
     * @param int|null $filter
     * @return array
     */
    public function getIsQtyTypeIds($filter = null)
    {
        if (null === $this->isQtyTypeIds) {
            $this->isQtyTypeIds = [];
            foreach ($this->config->getAll() as $typeId => $typeConfig) {
                $this->isQtyTypeIds[$typeId] = isset($typeConfig['is_qty']) ? $typeConfig['is_qty'] : false;
            }
        }
        $result = $this->isQtyTypeIds;
        if ($filter !== null) {
            foreach ($result as $key => $value) {
                if ($value !== $filter) {
                    unset($result[$key]);
                }
            }
        }
        return $result;
    }

    /**
     * Is Qty
     *
     * @param int $productTypeId
     * @return bool
     */
    public function isQty($productTypeId)
    {
        $result = $this->getIsQtyTypeIds();
        return isset($result[$productTypeId]) ? $result[$productTypeId] : false;
    }

    /**
     * Check if is possible subtract value from item qty
     *
     * @param null|string|bool|int|\Magento\Store\Model\Store $store
     * @return bool
     */
    public function canSubtractQty($store = null)
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_CAN_SUBTRACT,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Get Min Qty
     *
     * @param null|string|bool|int|\Magento\Store\Model\Store $store
     * @return float
     */
    public function getMinQty($store = null)
    {
        return (float)$this->scopeConfig->getValue(
            self::XML_PATH_MIN_QTY,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Get Min Sale Qty
     *
     * @param null|string|bool|int|\Magento\Store\Model\Store $store
     * @param int $customerGroupId
     * @return float
     */
    public function getMinSaleQty($store = null, $customerGroupId = null)
    {
        return (float)$this->minsaleqtyHelper->getConfigValue($customerGroupId, $store);
    }

    /**
     * Get Max Sale Qty
     *
     * @param null|string|bool|int|\Magento\Store\Model\Store $store
     * @return float|null
     */
    public function getMaxSaleQty($store = null)
    {
        return (float)$this->scopeConfig->getValue(
            self::XML_PATH_MAX_SALE_QTY,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Get Notify Stock Qty
     *
     * @param null|string|bool|int|\Magento\Store\Model\Store $store
     * @return float
     */
    public function getNotifyStockQty($store = null)
    {
        return (float) $this->scopeConfig->getValue(
            self::XML_PATH_NOTIFY_STOCK_QTY,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Retrieve whether Quantity Increments is enabled
     *
     * @param null|string|bool|int|\Magento\Store\Model\Store $store
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getEnableQtyIncrements($store = null)
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_ENABLE_QTY_INCREMENTS,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Get Qty Increments
     *
     * @param null|string|bool|int|\Magento\Store\Model\Store $store
     * @return float
     */
    public function getQtyIncrements($store = null)
    {
        return (float)$this->scopeConfig->getValue(
            self::XML_PATH_QTY_INCREMENTS,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Retrieve backorders status
     *
     * @param null|string|bool|int|\Magento\Store\Model\Store $store
     * @return int
     */
    public function getBackorders($store = null)
    {
        return (int) $this->scopeConfig->getValue(
            self::XML_PATH_BACKORDERS,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Retrieve Manage Stock data wrapper
     *
     * @param null|string|bool|int|\Magento\Store\Model\Store $store
     * @return int
     */
    public function getManageStock($store = null)
    {
        return (int) $this->scopeConfig->isSetFlag(
            self::XML_PATH_MANAGE_STOCK,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Retrieve can Back in stock
     *
     * @param null|string|bool|int|\Magento\Store\Model\Store $store
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getCanBackInStock($store = null)
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_CAN_BACK_IN_STOCK,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Display out of stock products option
     *
     * @param null|string|bool|int|\Magento\Store\Model\Store $store
     * @return bool
     */
    public function isShowOutOfStock($store = null)
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_SHOW_OUT_OF_STOCK,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Check if credit memo items auto return option is enabled
     *
     * @param null|string|bool|int|\Magento\Store\Model\Store $store
     * @return bool
     */
    public function isAutoReturnEnabled($store = null)
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_ITEM_AUTO_RETURN,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Display product stock status. Shows if it is necessary to show product stock status in stock/out of stock.
     *
     * @param null|string|bool|int|\Magento\Store\Model\Store $store
     * @return bool
     */
    public function isDisplayProductStockStatus($store = null)
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_DISPLAY_PRODUCT_STOCK_STATUS,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Get Default Config Value
     *
     * @param string $field
     * @param null|string|bool|int|\Magento\Store\Model\Store $store
     * @return string|null
     */
    public function getDefaultConfigValue($field, $store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_ITEM . $field,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Get Stock Threshold Qty
     *
     * @param null|string|bool|int|\Magento\Store\Model\Store $store
     * @return string|null
     */
    public function getStockThresholdQty($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_STOCK_THRESHOLD_QTY,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Retrieve inventory item options (used in config)
     *
     * @return string[]
     */
    public function getConfigItemOptions()
    {
        return [
            'min_qty',
            'backorders',
            'min_sale_qty',
            'max_sale_qty',
            'notify_stock_qty',
            'manage_stock',
            'enable_qty_increments',
            'qty_increments',
            'is_decimal_divided'
        ];
    }
}
