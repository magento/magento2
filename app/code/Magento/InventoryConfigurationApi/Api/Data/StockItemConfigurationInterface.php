<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryConfigurationApi\Api\Data;

/**
 * @api
 */
interface StockItemConfigurationInterface
{
    const BACKORDERS_NO = 0;
    const BACKORDERS_YES_NONOTIFY = 1;
    const BACKORDERS_YES_NOTIFY = 2;

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
     * @return bool
     */
    public function isQtyDecimal(): bool;

    /**
     * @param bool $isQtyDecimal
     * @return StockItemConfigurationInterface
     */
    public function setIsQtyDecimal(bool $isQtyDecimal): StockItemConfigurationInterface;

    /**
     * @return bool
     */
    public function isShowDefaultNotificationMessage(): bool;

    /**
     * @return bool
     */
    public function isUseConfigMinQty(): bool;

    /**
     * @param bool $useConfigMinQty
     * @return StockItemConfigurationInterface
     */
    public function setUseConfigMinQty(bool $useConfigMinQty): StockItemConfigurationInterface;

    /**
     * @return float
     */
    public function getMinQty(): float;

    /**
     * @param float $minQty
     * @return StockItemConfigurationInterface
     */
    public function setMinQty(float $minQty): StockItemConfigurationInterface;

    /**
     * @return bool
     */
    public function isUseConfigMinSaleQty(): bool;

    /**
     * @param bool $useConfigMinSaleQty
     * @return StockItemConfigurationInterface
     */
    public function setUseConfigMinSaleQty(bool $useConfigMinSaleQty): StockItemConfigurationInterface;

    /**
     * @return float
     */
    public function getMinSaleQty(): float;

    /**
     * @param float $minSaleQty
     * @return StockItemConfigurationInterface
     */
    public function setMinSaleQty(float $minSaleQty): StockItemConfigurationInterface;

    /**
     * @return bool
     */
    public function isUseConfigMaxSaleQty(): bool;

    /**
     * @param bool $useConfigMaxSaleQty
     * @return StockItemConfigurationInterface
     */
    public function setUseConfigMaxSaleQty(bool $useConfigMaxSaleQty): StockItemConfigurationInterface;

    /**
     * @return float
     */
    public function getMaxSaleQty(): float;

    /**
     * @param float $maxSaleQty
     * @return StockItemConfigurationInterface
     */
    public function setMaxSaleQty(float $maxSaleQty): StockItemConfigurationInterface;

    /**
     * @return bool
     */
    public function isUseConfigBackorders(): bool;

    /**
     * @param bool $useConfigBackorders
     * @return StockItemConfigurationInterface
     */
    public function setUseConfigBackorders(bool $useConfigBackorders): StockItemConfigurationInterface;

    /**
     * Retrieve backorders status
     *
     * @return int
     */
    public function getBackorders(): int;

    /**
     * @param int $backOrders
     * @return StockItemConfigurationInterface
     */
    public function setBackorders(int $backOrders): StockItemConfigurationInterface;

    /**
     * @return bool
     */
    public function isUseConfigNotifyStockQty(): bool;

    /**
     * @param bool $useConfigNotifyStockQty
     * @return StockItemConfigurationInterface
     */
    public function setUseConfigNotifyStockQty(bool $useConfigNotifyStockQty): StockItemConfigurationInterface;

    /**
     * @return float
     */
    public function getNotifyStockQty(): float;

    /**
     * @param float $notifyStockQty
     * @return StockItemConfigurationInterface
     */
    public function setNotifyStockQty(float $notifyStockQty): StockItemConfigurationInterface;

    /**
     * @return bool
     */
    public function isUseConfigQtyIncrements(): bool;

    /**
     * @param bool $useConfigQtyIncrements
     * @return StockItemConfigurationInterface
     */
    public function setUseConfigQtyIncrements(bool $useConfigQtyIncrements): StockItemConfigurationInterface;

    /**
     * Retrieve Quantity Increments data wrapper
     *
     * @return float
     */
    public function getQtyIncrements(): float;

    /**
     * @param float $qtyIncrements
     * @return StockItemConfigurationInterface
     */
    public function setQtyIncrements(float $qtyIncrements): StockItemConfigurationInterface;

    /**
     * @return bool
     */
    public function isUseConfigEnableQtyInc(): bool;

    /**
     * @param bool $useConfigEnableQtyInc
     * @return StockItemConfigurationInterface
     */
    public function setUseConfigEnableQtyInc(bool $useConfigEnableQtyInc): StockItemConfigurationInterface;

    /**
     * @return bool
     */
    public function isEnableQtyIncrements(): bool;

    /**
     * @param $enableQtyIncrements
     * @return StockItemConfigurationInterface
     */
    public function setEnableQtyIncrements(bool $enableQtyIncrements): StockItemConfigurationInterface;

    /**
     * @return bool
     */
    public function isUseConfigManageStock(): bool;

    /**
     * @param bool $useConfigManageStock
     * @return StockItemConfigurationInterface
     */
    public function setUseConfigManageStock(bool $useConfigManageStock): StockItemConfigurationInterface;

    /**
     * @return bool
     */
    public function isManageStock(): bool;

    /**
     * @param bool $manageStock
     * @return StockItemConfigurationInterface
     */
    public function setManageStock(bool $manageStock): StockItemConfigurationInterface;

    /**
     * @return string
     */
    public function getLowStockDate(): string;

    /**
     * @param string $lowStockDate
     * @return StockItemConfigurationInterface
     */
    public function setLowStockDate(string $lowStockDate): StockItemConfigurationInterface;

    /**
     * @return bool
     */
    public function isDecimalDivided(): bool;

    /**
     * @param bool $isDecimalDivided
     * @return StockItemConfigurationInterface
     */
    public function setIsDecimalDivided(bool $isDecimalDivided): StockItemConfigurationInterface;

    /**
     * @return int
     */
    public function getStockStatusChangedAuto(): bool;

    /**
     * @param int $stockStatusChangedAuto
     * @return StockItemConfigurationInterface
     */
    public function setStockStatusChangedAuto(int $stockStatusChangedAuto): StockItemConfigurationInterface;
}
