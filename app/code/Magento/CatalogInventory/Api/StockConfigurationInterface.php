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
namespace Magento\CatalogInventory\Api;

/**
 * Interface StockConfigurationInterface
 * @package Magento\CatalogInventory\Api
 * @api
 */
interface StockConfigurationInterface
{
    /**
     * Retrieve Default Website ID
     *
     * @return int
     */
    public function getDefaultWebsiteId();

    /**
     * @param int|null $filter
     * @return array
     */
    public function getIsQtyTypeIds($filter = null);

    /**
     * Check if Stock Management is applicable for the given Product Type
     *
     * @param int $productTypeId
     * @return bool
     */
    public function isQty($productTypeId);

    /**
     * Check if is possible subtract value from item qty
     *
     * @param null|string|bool|int|\Magento\Store\Model\Store $store
     * @return bool
     */
    public function canSubtractQty($store = null);

    /**
     * @param null|string|bool|int|\Magento\Store\Model\Store $store
     * @return float
     */
    public function getMinQty($store = null);

    /**
     * @param null|string|bool|int|\Magento\Store\Model\Store $store
     * @param int $customerGroupId
     * @return float
     */
    public function getMinSaleQty($store = null, $customerGroupId = null);

    /**
     * @param null|string|bool|int|\Magento\Store\Model\Store $store
     * @return float|null
     */
    public function getMaxSaleQty($store = null);

    /**
     * @param null|string|bool|int|\Magento\Store\Model\Store $store
     * @return float
     */
    public function getNotifyStockQty($store = null);

    /**
     * Retrieve whether Quantity Increments is enabled
     *
     * @param null|string|bool|int|\Magento\Store\Model\Store $store
     * @return bool
     */
    public function getEnableQtyIncrements($store = null);

    /**
     * @param null|string|bool|int|\Magento\Store\Model\Store $store
     * @return int
     */
    public function getQtyIncrements($store = null);

    /**
     * Retrieve backorders status
     *
     * @param null|string|bool|int|\Magento\Store\Model\Store $store
     * @return int
     */
    public function getBackorders($store = null);

    /**
     * Retrieve Manage Stock data wrapper
     *
     * @param null|string|bool|int|\Magento\Store\Model\Store $store
     * @return int
     */
    public function getManageStock($store = null);

    /**
     * Retrieve can Back in stock
     *
     * @param null|string|bool|int|\Magento\Store\Model\Store $store
     * @return bool
     */
    public function getCanBackInStock($store = null);

    /**
     * Display out of stock products option
     *
     * @param null|string|bool|int|\Magento\Store\Model\Store $store
     * @return bool
     */
    public function isShowOutOfStock($store = null);

    /**
     * Check if credit memo items auto return option is enabled
     *
     * @param null|string|bool|int|\Magento\Store\Model\Store $store
     * @return bool
     */
    public function isAutoReturnEnabled($store = null);

    /**
     * Get 'Display product stock status' option value
     * Shows if it is necessary to show product stock status ('in stock'/'out of stock')
     *
     * @param null|string|bool|int|\Magento\Store\Model\Store $store
     * @return bool
     */
    public function isDisplayProductStockStatus($store = null);

    /**
     * @param string $field
     * @param null|string|bool|int|\Magento\Store\Model\Store $store
     * @return string|null
     */
    public function getDefaultConfigValue($field, $store = null);

    /**
     * Retrieve inventory item options (used in config)
     *
     * @return string[]
     */
    public function getConfigItemOptions();
}
