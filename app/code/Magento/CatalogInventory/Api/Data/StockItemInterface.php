<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Interface StockItem
 */
interface StockItemInterface extends ExtensibleDataInterface
{
    const BACKORDERS_NO = 0;

    const ITEM_ID = 'item_id';
    const PRODUCT_ID = 'product_id';
    const WEBSITE_ID = 'website_id';
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
    const NOTIFY_STOCK_QTY = 'use_config_notify_stock_qty';

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
     * @return int
     */
    public function getItemId();

    /**
     * @return int
     */
    public function getProductId();

    /**
     * Retrieve Website Id
     *
     * @return int
     */
    public function getWebsiteId();

    /**
     * Retrieve stock identifier
     *
     * @return int
     */
    public function getStockId();

    /**
     * @return float
     */
    public function getQty();

    /**
     * Retrieve Stock Availability
     *
     * @return bool|int
     */
    public function getIsInStock();

    /**
     * @return bool
     */
    public function getIsQtyDecimal();

    /**
     * @return bool
     */
    public function getShowDefaultNotificationMessage();

    /**
     * @return bool
     */
    public function getUseConfigMinQty();

    /**
     * Retrieve minimal quantity available for item status in stock
     *
     * @return float
     */
    public function getMinQty();

    /**
     * @return int
     */
    public function getUseConfigMinSaleQty();

    /**
     * Retrieve Minimum Qty Allowed in Shopping Cart or NULL when there is no limitation
     *
     * @return float
     */
    public function getMinSaleQty();

    /**
     * @return bool
     */
    public function getUseConfigMaxSaleQty();

    /**
     * Retrieve Maximum Qty Allowed in Shopping Cart data wrapper
     *
     * @return float
     */
    public function getMaxSaleQty();

    /**
     * @return bool
     */
    public function getUseConfigBackorders();

    /**
     * Retrieve backorders status
     *
     * @return int
     */
    public function getBackorders();

    /**
     * @return bool
     */
    public function getUseConfigNotifyStockQty();

    /**
     * Retrieve Notify for Quantity Below data wrapper
     *
     * @return float
     */
    public function getNotifyStockQty();

    /**
     * @return bool
     */
    public function getUseConfigQtyIncrements();

    /**
     * Retrieve Quantity Increments data wrapper
     *
     * @return float|false
     */
    public function getQtyIncrements();

    /**
     * @return bool
     */
    public function getUseConfigEnableQtyInc();

    /**
     * Retrieve whether Quantity Increments is enabled
     *
     * @return bool
     */
    public function getEnableQtyIncrements();

    /**
     * @return bool
     */
    public function getUseConfigManageStock();

    /**
     * Retrieve can Manage Stock
     *
     * @return bool
     */
    public function getManageStock();

    /**
     * @return string
     */
    public function getLowStockDate();

    /**
     * @return bool
     */
    public function getIsDecimalDivided();

    /**
     * @return int
     */
    public function getStockStatusChangedAuto();
}
