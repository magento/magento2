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

    const SKU = 'sku';
    const STOCK_ID = 'stock_id';
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
     * @return bool
     */
    public function isShowDefaultNotificationMessage(): bool;

    /**
     * @return bool
     */
    public function isUseConfigMinQty(): bool;

    /**
     * @return float
     */
    public function getMinQty(): float;

    /**
     * @return bool
     */
    public function isUseConfigMinSaleQty(): bool;

    /**
     * @return float
     */
    public function getMinSaleQty(): float;

    /**
     * @return bool
     */
    public function isUseConfigMaxSaleQty(): bool;

    /**
     * @return float
     */
    public function getMaxSaleQty(): float;

    /**
     * @return bool
     */
    public function isUseConfigBackorders(): bool;

    /**
     * Retrieve backorders status
     *
     * @return int
     */
    public function getBackorders(): int;

    /**
     * @return bool
     */
    public function isUseConfigNotifyStockQty(): bool;

    /**
     * @return float
     */
    public function getNotifyStockQty(): float;

    /**
     * @return bool
     */
    public function isUseConfigQtyIncrements(): bool;

    /**
     * Retrieve Quantity Increments data wrapper
     *
     * @return float
     */
    public function getQtyIncrements(): float;

    /**
     * @return bool
     */
    public function isUseConfigEnableQtyInc(): bool;

    /**
     * @return bool
     */
    public function isEnableQtyIncrements(): bool;

    /**
     * @return bool
     */
    public function isUseConfigManageStock(): bool;

    /**
     * @return bool
     */
    public function isManageStock(): bool;

    /**
     * @return string
     */
    public function getLowStockDate(): string;

    /**
     * @return bool
     */
    public function isDecimalDivided(): bool;

    /**
     * @return int
     */
    public function getStockStatusChangedAuto(): int;
}
