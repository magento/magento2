<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryConfigurationApi\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Interface StockItemConfiguration
 * @api
 */
interface StockItemConfigurationInterface extends ExtensibleDataInterface
{
    const BACKORDERS_NO = 0;
    const BACKORDERS_YES_NONOTIFY = 1;
    const BACKORDERS_YES_NOTIFY = 2;

    const SKU = 'sku';
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

    /**
     * @return string
     */
    public function getSku(): string;

    /**
     * @param string $sku
     * @return $this
     */
    public function setSku(string $sku): StockItemConfigurationInterface;

    /**
     * Retrieve stock identifier
     *
     * @return int
     */
    public function getStockId(): int;

    /**
     * Set stock identifier
     *
     * @param int $stockId
     * @return $this
     */
    public function setStockId(int $stockId): StockItemConfigurationInterface;

    /**
     * @return float
     */
    public function getQty(): float;

    /**
     * @param float $qty
     * @return $this
     */
    public function setQty(float $qty): StockItemConfigurationInterface;


    /**
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getIsQtyDecimal(): bool;

    /**
     * @param bool $isQtyDecimal
     * @return $this
     */
    public function setIsQtyDecimal(bool $isQtyDecimal): StockItemConfigurationInterface;

    /**
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getShowDefaultNotificationMessage(): bool;

    /**
     * @param bool $showDefaultNotificationMessage
     * @return $this
     */
    public function setShowDefaultNotificationMessage(bool $showDefaultNotificationMessage): StockItemConfigurationInterface;

    /**
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getUseConfigMinQty(): bool;

    /**
     * @param bool $useConfigMinQty
     * @return $this
     */
    public function setUseConfigMinQty(bool $useConfigMinQty): StockItemConfigurationInterface;

    /**
     * Retrieve minimal quantity available for item status in stock
     *
     * @return float
     */
    public function getMinQty(): float;

    /**
     * Set minimal quantity available for item status in stock
     *
     * @param float $minQty
     * @return $this
     */
    public function setMinQty(float $minQty): StockItemConfigurationInterface;

    /**
     * @return bool
     */
    public function getUseConfigMinSaleQty(): bool;

    /**
     * @param bool $useConfigMinSaleQty
     * @return $this
     */
    public function setUseConfigMinSaleQty(bool $useConfigMinSaleQty): StockItemConfigurationInterface;

    /**
     * Retrieve Minimum Qty Allowed in Shopping Cart or NULL when there is no limitation
     *
     * @return float
     */
    public function getMinSaleQty(): float;

    /**
     * Set Minimum Qty Allowed in Shopping Cart or NULL when there is no limitation
     *
     * @param float $minSaleQty
     * @return $this
     */
    public function setMinSaleQty(float $minSaleQty): StockItemConfigurationInterface;

    /**
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getUseConfigMaxSaleQty(): bool;

    /**
     * @param bool $useConfigMaxSaleQty
     * @return $this
     */
    public function setUseConfigMaxSaleQty(bool $useConfigMaxSaleQty): StockItemConfigurationInterface;

    /**
     * Retrieve Maximum Qty Allowed in Shopping Cart data wrapper
     *
     * @return float
     */
    public function getMaxSaleQty(): float;

    /**
     * Set Maximum Qty Allowed in Shopping Cart data wrapper
     *
     * @param float $maxSaleQty
     * @return $this
     */
    public function setMaxSaleQty(float $maxSaleQty): StockItemConfigurationInterface;

    /**
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getUseConfigBackorders(): bool;

    /**
     * @param bool $useConfigBackorders
     * @return $this
     */
    public function setUseConfigBackorders(bool $useConfigBackorders): StockItemConfigurationInterface;

    /**
     * Retrieve backorders status
     *
     * @return int
     */
    public function getBackorders(): int;

    /**
     * Set backOrders status
     *
     * @param int $backOrders
     * @return $this
     */
    public function setBackorders(int $backOrders): StockItemConfigurationInterface;

    /**
     * @return bool
     */
    public function getUseConfigNotifyStockQty(): bool;

    /**
     * @param bool $useConfigNotifyStockQty
     * @return $this
     */
    public function setUseConfigNotifyStockQty(bool $useConfigNotifyStockQty): StockItemConfigurationInterface;

    /**
     * Retrieve Notify for Quantity Below data wrapper
     *
     * @return float
     */
    public function getNotifyStockQty(): float;

    /**
     * Set Notify for Quantity Below data wrapper
     *
     * @param float $notifyStockQty
     * @return $this
     */
    public function setNotifyStockQty(float $notifyStockQty): StockItemConfigurationInterface;

    /**
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getUseConfigQtyIncrements(): bool;

    /**
     * @param bool $useConfigQtyIncrements
     * @return $this
     */
    public function setUseConfigQtyIncrements(bool $useConfigQtyIncrements): StockItemConfigurationInterface;

    /**
     * Retrieve Quantity Increments data wrapper
     *
     * @return float|false
     */
    public function getQtyIncrements(): float;

    /**
     * Set Quantity Increments data wrapper
     *
     * @param float $qtyIncrements
     * @return $this
     */
    public function setQtyIncrements(float $qtyIncrements): StockItemConfigurationInterface;

    /**
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getUseConfigEnableQtyInc(): bool;

    /**
     * @param bool $useConfigEnableQtyInc
     * @return $this
     */
    public function setUseConfigEnableQtyInc(bool $useConfigEnableQtyInc): StockItemConfigurationInterface;

    /**
     * Retrieve whether Quantity Increments is enabled
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getEnableQtyIncrements(): bool;

    /**
     * Set whether Quantity Increments is enabled
     *
     * @param bool $enableQtyIncrements
     * @return $this
     */
    public function setEnableQtyIncrements(bool $enableQtyIncrements): StockItemConfigurationInterface;

    /**
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getUseConfigManageStock(): bool;

    /**
     * @param bool $useConfigManageStock
     * @return $this
     */
    public function setUseConfigManageStock(bool $useConfigManageStock): StockItemConfigurationInterface;

    /**
     * Retrieve can Manage Stock
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getManageStock(): bool;

    /**
     * @param bool $manageStock
     * @return $this
     */
    public function setManageStock(bool $manageStock): StockItemConfigurationInterface;

    /**
     * Retrieve Stock Availability
     *
     * @return bool
     */
    public function getIsInStock(): bool;

    /**
     * Set Stock Availability
     *
     * @param bool $isInStock
     * @return $this
     */
    public function setIsInStock(bool $isInStock): StockItemConfigurationInterface;

    /**
     * @return string
     */
    public function getLowStockDate(): string;

    /**
     * @param string $lowStockDate
     * @return $this
     */
    public function setLowStockDate(string $lowStockDate): StockItemConfigurationInterface;

    /**
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getIsDecimalDivided(): bool;

    /**
     * @param bool $isDecimalDivided
     * @return $this
     */
    public function setIsDecimalDivided(bool $isDecimalDivided): StockItemConfigurationInterface;

    /**
     * @return int
     */
    public function getStockStatusChangedAuto(): int;

    /**
     * @param int $stockStatusChangedAuto
     * @return $this
     */
    public function setStockStatusChangedAuto(int $stockStatusChangedAuto): StockItemConfigurationInterface;

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\InventoryConfigurationApi\Api\Data\StockItemConfigurationExtensionInterface|null
     */
    public function getExtensionAttributes(): StockItemConfigurationExtensionInterface;

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\InventoryConfigurationApi\Api\Data\StockItemConfigurationExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Magento\InventoryConfigurationApi\Api\Data\StockItemConfigurationExtensionInterface $extensionAttributes
    ): StockItemConfigurationInterface;
}
