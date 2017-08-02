<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Api;

/**
 * Interface StockConfigurationInterface
 * @api
 * @since 2.0.0
 */
interface StockConfigurationInterface
{
    /**
     * Retrieve Default Scope ID
     *
     * @return int
     * @since 2.0.0
     */
    public function getDefaultScopeId();

    /**
     * @param int $filter
     * @return int[]
     * @since 2.0.0
     */
    public function getIsQtyTypeIds($filter = null);

    /**
     * Check if Stock Management is applicable for the given Product Type
     *
     * @param int $productTypeId
     * @return bool
     * @since 2.0.0
     */
    public function isQty($productTypeId);

    /**
     * Check if is possible subtract value from item qty
     *
     * @param int $storeId
     * @return bool
     * @since 2.0.0
     */
    public function canSubtractQty($storeId = null);

    /**
     * @param int $storeId
     * @return float
     * @since 2.0.0
     */
    public function getMinQty($storeId = null);

    /**
     * @param int $storeId
     * @param int $customerGroupId
     * @return float
     * @since 2.0.0
     */
    public function getMinSaleQty($storeId = null, $customerGroupId = null);

    /**
     * @param int $storeId
     * @return float
     * @since 2.0.0
     */
    public function getMaxSaleQty($storeId = null);

    /**
     * @param int $storeId
     * @return float
     * @since 2.0.0
     */
    public function getNotifyStockQty($storeId = null);

    /**
     * Retrieve whether Quantity Increments is enabled
     *
     * @param int $storeId
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     * @since 2.0.0
     */
    public function getEnableQtyIncrements($storeId = null);

    /**
     * @param int $storeId
     * @return int
     * @since 2.0.0
     */
    public function getQtyIncrements($store = null);

    /**
     * Retrieve backorders status
     *
     * @param int $storeId
     * @return int
     * @since 2.0.0
     */
    public function getBackorders($storeId = null);

    /**
     * Retrieve Manage Stock data wrapper
     *
     * @param int $storeId
     * @return int
     * @since 2.0.0
     */
    public function getManageStock($storeId = null);

    /**
     * Retrieve can Back in stock
     *
     * @param int $storeId
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     * @since 2.0.0
     */
    public function getCanBackInStock($storeId = null);

    /**
     * Display out of stock products option
     *
     * @param int $storeId
     * @return bool
     * @since 2.0.0
     */
    public function isShowOutOfStock($storeId = null);

    /**
     * Check if credit memo items auto return option is enabled
     *
     * @param int $storeId
     * @return bool
     * @since 2.0.0
     */
    public function isAutoReturnEnabled($storeId = null);

    /**
     * Get 'Display product stock status' option value
     * Shows if it is necessary to show product stock status ('in stock'/'out of stock')
     *
     * @param int $storeId
     * @return bool
     * @since 2.0.0
     */
    public function isDisplayProductStockStatus($storeId = null);

    /**
     * @param string $field
     * @param int $storeId
     * @return string
     * @since 2.0.0
     */
    public function getDefaultConfigValue($field, $storeId = null);

    /**
     * Retrieve inventory item options (used in config)
     *
     * @return string[]
     * @since 2.0.0
     */
    public function getConfigItemOptions();
}
