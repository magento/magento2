<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Interface StockItem
 * @api
 * @since 100.0.2
 *
 * @deprecated 100.3.0 Replaced with Multi Source Inventory
 * @link https://devdocs.magento.com/guides/v2.4/inventory/index.html
 * @link https://devdocs.magento.com/guides/v2.4/inventory/inventory-api-reference.html
 */
interface StockItemInterface extends ExtensibleDataInterface
{
    const BACKORDERS_NO = 0;

    const ITEM_ID = 'item_id';
    const PRODUCT_ID = 'product_id';
    const STOCK_ID = 'stock_id';
    const QTY = 'qty';
    const IS_QTY_DECIMAL = 'is_qty_decimal';
    const SHOW_DEFAULT_NOTIFICATION_MESSAGE = 'show_default_notification_message';

    const USE_CONFIG_MIN_QTY = 'use_config_min_qty';
    const MIN_QTY = 'min_qty';

    const USE_CONFIG_MIN_SALE_QTY = 'use_config_min_sale_qty';
    const MIN_SALE_QTY = 'min_sale_qty';

    const USE_CONFIG_MAX_SALE_QTY = 'use_config_max_sale_qty';
    const MAX_SALE_QTY = 'max_sale_qty';

    const USE_CONFIG_BACKORDERS = 'use_config_backorders';
    const BACKORDERS = 'backorders';

    const USE_CONFIG_NOTIFY_STOCK_QTY = 'use_config_notify_stock_qty';
    const NOTIFY_STOCK_QTY = 'notify_stock_qty';

    const USE_CONFIG_QTY_INCREMENTS = 'use_config_qty_increments';
    const QTY_INCREMENTS = 'qty_increments';

    const USE_CONFIG_ENABLE_QTY_INC = 'use_config_enable_qty_inc';
    const ENABLE_QTY_INCREMENTS = 'enable_qty_increments';

    const USE_CONFIG_MANAGE_STOCK = 'use_config_manage_stock';
    const MANAGE_STOCK = 'manage_stock';

    const IS_IN_STOCK = 'is_in_stock';
    const LOW_STOCK_DATE = 'low_stock_date';
    const IS_DECIMAL_DIVIDED = 'is_decimal_divided';
    const STOCK_STATUS_CHANGED_AUTO = 'stock_status_changed_auto';

    const STORE_ID = 'store_id';
    const CUSTOMER_GROUP_ID = 'customer_group_id';

    /**
     * @return int|null
     */
    public function getItemId();

    /**
     * @param int $itemId
     * @return $this
     */
    public function setItemId($itemId);

    /**
     * @return int|null
     */
    public function getProductId();

    /**
     * @param int $productId
     * @return $this
     */
    public function setProductId($productId);

    /**
     * Retrieve stock identifier
     *
     * @return int|null
     */
    public function getStockId();

    /**
     * Set stock identifier
     *
     * @param int $stockId
     * @return $this
     */
    public function setStockId($stockId);

    /**
     * @return float
     */
    public function getQty();

    /**
     * @param float $qty
     * @return $this
     */
    public function setQty($qty);

    /**
     * Retrieve Stock Availability
     *
     * @return bool|int
     */
    public function getIsInStock();

    /**
     * Set Stock Availability
     *
     * @param bool|int $isInStock
     * @return $this
     */
    public function setIsInStock($isInStock);

    /**
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getIsQtyDecimal();

    /**
     * @param bool $isQtyDecimal
     * @return $this
     */
    public function setIsQtyDecimal($isQtyDecimal);

    /**
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getShowDefaultNotificationMessage();

    /**
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getUseConfigMinQty();

    /**
     * @param bool $useConfigMinQty
     * @return $this
     */
    public function setUseConfigMinQty($useConfigMinQty);

    /**
     * Retrieve minimal quantity available for item status in stock
     *
     * @return float
     */
    public function getMinQty();

    /**
     * Set minimal quantity available for item status in stock
     *
     * @param float $minQty
     * @return $this
     */
    public function setMinQty($minQty);

    /**
     * @return int
     */
    public function getUseConfigMinSaleQty();

    /**
     * @param int $useConfigMinSaleQty
     * @return $this
     */
    public function setUseConfigMinSaleQty($useConfigMinSaleQty);

    /**
     * Retrieve Minimum Qty Allowed in Shopping Cart or NULL when there is no limitation
     *
     * @return float
     */
    public function getMinSaleQty();

    /**
     * Set Minimum Qty Allowed in Shopping Cart or NULL when there is no limitation
     *
     * @param float $minSaleQty
     * @return $this
     */
    public function setMinSaleQty($minSaleQty);

    /**
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getUseConfigMaxSaleQty();

    /**
     * @param bool $useConfigMaxSaleQty
     * @return $this
     */
    public function setUseConfigMaxSaleQty($useConfigMaxSaleQty);

    /**
     * Retrieve Maximum Qty Allowed in Shopping Cart data wrapper
     *
     * @return float
     */
    public function getMaxSaleQty();

    /**
     * Set Maximum Qty Allowed in Shopping Cart data wrapper
     *
     * @param float $maxSaleQty
     * @return $this
     */
    public function setMaxSaleQty($maxSaleQty);

    /**
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getUseConfigBackorders();

    /**
     * @param bool $useConfigBackorders
     * @return $this
     */
    public function setUseConfigBackorders($useConfigBackorders);

    /**
     * Retrieve backorders status
     *
     * @return int
     */
    public function getBackorders();

    /**
     * Set backOrders status
     *
     * @param int $backOrders
     * @return $this
     */
    public function setBackorders($backOrders);

    /**
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getUseConfigNotifyStockQty();

    /**
     * @param bool $useConfigNotifyStockQty
     * @return $this
     */
    public function setUseConfigNotifyStockQty($useConfigNotifyStockQty);

    /**
     * Retrieve Notify for Quantity Below data wrapper
     *
     * @return float
     */
    public function getNotifyStockQty();

    /**
     * Set Notify for Quantity Below data wrapper
     *
     * @param float $notifyStockQty
     * @return $this
     */
    public function setNotifyStockQty($notifyStockQty);

    /**
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getUseConfigQtyIncrements();

    /**
     * @param bool $useConfigQtyIncrements
     * @return $this
     */
    public function setUseConfigQtyIncrements($useConfigQtyIncrements);

    /**
     * Retrieve Quantity Increments data wrapper
     *
     * @return float|false
     */
    public function getQtyIncrements();

    /**
     * Set Quantity Increments data wrapper
     *
     * @param float $qtyIncrements
     * @return $this
     */
    public function setQtyIncrements($qtyIncrements);

    /**
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getUseConfigEnableQtyInc();

    /**
     * @param bool $useConfigEnableQtyInc
     * @return $this
     */
    public function setUseConfigEnableQtyInc($useConfigEnableQtyInc);

    /**
     * Retrieve whether Quantity Increments is enabled
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getEnableQtyIncrements();

    /**
     * Set whether Quantity Increments is enabled
     *
     * @param bool $enableQtyIncrements
     * @return $this
     */
    public function setEnableQtyIncrements($enableQtyIncrements);

    /**
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getUseConfigManageStock();

    /**
     * @param bool $useConfigManageStock
     * @return $this
     */
    public function setUseConfigManageStock($useConfigManageStock);

    /**
     * Retrieve can Manage Stock
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getManageStock();

    /**
     * @param bool $manageStock
     * @return $this
     */
    public function setManageStock($manageStock);

    /**
     * @return string
     */
    public function getLowStockDate();

    /**
     * @param string $lowStockDate
     * @return $this
     */
    public function setLowStockDate($lowStockDate);

    /**
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getIsDecimalDivided();

    /**
     * @param bool $isDecimalDivided
     * @return $this
     */
    public function setIsDecimalDivided($isDecimalDivided);

    /**
     * @return int
     */
    public function getStockStatusChangedAuto();

    /**
     * @param int $stockStatusChangedAuto
     * @return $this
     */
    public function setStockStatusChangedAuto($stockStatusChangedAuto);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\CatalogInventory\Api\Data\StockItemExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\CatalogInventory\Api\Data\StockItemExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Magento\CatalogInventory\Api\Data\StockItemExtensionInterface $extensionAttributes
    );
}
