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
interface StockItemConfigurationInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    const BACKORDERS_NO = 0;
    const BACKORDERS_YES_NONOTIFY = 1;
    const BACKORDERS_YES_NOTIFY = 2;

    const IS_QTY_DECIMAL = 'is_qty_decimal';
    const SHOW_DEFAULT_NOTIFICATION_MESSAGE = 'show_default_notification_message';

    /*
     * Safety stock threshold, not to confuse with the one used to show the "Only X left" label on frontend
     */
    const USE_CONFIG_MIN_QTY = 'use_config_min_qty';
    const MIN_QTY = 'min_qty';

    /*
     * Threshold intended to show the "Only X left" label on frontend
     */
    const USE_CONFIG_STOCK_THRESHOLD_QTY = 'use_config_stock_threshold_qty';
    const STOCK_THRESHOLD_QTY = 'stock_threshold_qty';

    /*
     * Used to prevent to buy less than a certain qty of a product, not to confuse with the safety stock threshold
     */
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

    const LOW_STOCK_DATE = 'low_stock_date';
    const IS_DECIMAL_DIVIDED = 'is_decimal_divided';
    const STOCK_STATUS_CHANGED_AUTO = 'stock_status_changed_auto';

    /**
     * @return bool
     */
    public function isQtyDecimal(): bool;

    /**
     * @param bool $isQtyDecimal
     * @return void
     */
    public function setIsQtyDecimal(bool $isQtyDecimal): void;

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
     * @return void
     */
    public function setUseConfigMinQty(bool $useConfigMinQty): void;

    /**
     * @return float
     */
    public function getMinQty(): float;

    /**
     * @param float $minQty
     * @return void
     */
    public function setMinQty(float $minQty): void;

    /**
     * @return bool
     */
    public function isUseConfigMinSaleQty(): bool;

    /**
     * @param bool $useConfigMinSaleQty
     * @return void
     */
    public function setUseConfigMinSaleQty(bool $useConfigMinSaleQty): void;

    /**
     * @return float
     */
    public function getMinSaleQty(): float;

    /**
     * @param float $minSaleQty
     * @return void
     */
    public function setMinSaleQty(float $minSaleQty): void;

    /**
     * @return bool
     */
    public function isUseConfigMaxSaleQty(): bool;

    /**
     * @param bool $useConfigMaxSaleQty
     * @return void
     */
    public function setUseConfigMaxSaleQty(bool $useConfigMaxSaleQty): void;

    /**
     * @return float
     */
    public function getMaxSaleQty(): float;

    /**
     * @param float $maxSaleQty
     * @return void
     */
    public function setMaxSaleQty(float $maxSaleQty): void;

    /**
     * @return bool
     */
    public function isUseConfigBackorders(): bool;

    /**
     * @param bool $useConfigBackorders
     * @return void
     */
    public function setUseConfigBackorders(bool $useConfigBackorders): void;

    /**
     * Retrieve backorders status
     *
     * @return int
     */
    public function getBackorders(): int;

    /**
     * @param int $backOrders
     * @return void
     */
    public function setBackorders(int $backOrders): void;

    /**
     * @return bool
     */
    public function isUseConfigNotifyStockQty(): bool;

    /**
     * @param bool $useConfigNotifyStockQty
     * @return void
     */
    public function setUseConfigNotifyStockQty(bool $useConfigNotifyStockQty): void;

    /**
     * @return float
     */
    public function getNotifyStockQty(): float;

    /**
     * @param float $notifyStockQty
     * @return void
     */
    public function setNotifyStockQty(float $notifyStockQty): void;

    /**
     * @return bool
     */
    public function isUseConfigQtyIncrements(): bool;

    /**
     * @param bool $useConfigQtyIncrements
     * @return void
     */
    public function setUseConfigQtyIncrements(bool $useConfigQtyIncrements): void;

    /**
     * Retrieve Quantity Increments data wrapper
     *
     * @return float
     */
    public function getQtyIncrements(): float;

    /**
     * @param float $qtyIncrements
     * @return void
     */
    public function setQtyIncrements(float $qtyIncrements): void;

    /**
     * @return bool
     */
    public function isUseConfigEnableQtyInc(): bool;

    /**
     * @param bool $useConfigEnableQtyInc
     * @return void
     */
    public function setUseConfigEnableQtyInc(bool $useConfigEnableQtyInc): void;

    /**
     * @return bool
     */
    public function isEnableQtyIncrements(): bool;

    /**
     * @param $enableQtyIncrements
     * @return void
     */
    public function setEnableQtyIncrements(bool $enableQtyIncrements): void;

    /**
     * @return bool
     */
    public function isUseConfigManageStock(): bool;

    /**
     * @param bool $useConfigManageStock
     * @return void
     */
    public function setUseConfigManageStock(bool $useConfigManageStock): void;

    /**
     * @return bool
     */
    public function isManageStock(): bool;

    /**
     * @param bool $manageStock
     * @return void
     */
    public function setManageStock(bool $manageStock): void;

    /**
     * @return string
     */
    public function getLowStockDate(): string;

    /**
     * @param string $lowStockDate
     * @return void
     */
    public function setLowStockDate(string $lowStockDate): void;

    /**
     * @return bool
     */
    public function isDecimalDivided(): bool;

    /**
     * @param bool $isDecimalDivided
     * @return void
     */
    public function setIsDecimalDivided(bool $isDecimalDivided): void;

    /**
     * @return int
     */
    public function getStockStatusChangedAuto(): bool;

    /**
     * @param int $stockStatusChangedAuto
     * @return void
     */
    public function setStockStatusChangedAuto(int $stockStatusChangedAuto): void;

    /**
     * @return float
     */
    public function getStockThresholdQty(): float;

    /**
     * Retrieve existing extension attributes object
     *
     * @return \Magento\InventoryConfigurationApi\Api\Data\StockItemConfigurationExtensionInterface|null
     */
    public function getExtensionAttributes(): ?StockItemConfigurationExtensionInterface;

    /**
     * Set an extension attributes object
     *
     * @param \Magento\InventoryConfigurationApi\Api\Data\StockItemConfigurationExtensionInterface $extensionAttributes
     * @return void
     */
    public function setExtensionAttributes(
        \Magento\InventoryConfigurationApi\Api\Data\StockItemConfigurationExtensionInterface $extensionAttributes
    ): void;
}
