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
namespace Magento\CatalogInventory\Service\V1\Data;

use Magento\Framework\Service\Data\AbstractExtensibleObject;

/**
 * Stock item data object
 *
 * @codeCoverageIgnore
 */
class StockItem extends AbstractExtensibleObject
{
    /**#@+
     * Stock item object data keys
     */
    const ITEM_ID = 'item_id';

    const PRODUCT_ID = 'product_id';

    const STOCK_ID = 'stock_id';

    const QTY = 'qty';

    const MIN_QTY = 'min_qty';

    const USE_CONFIG_MIN_QTY = 'use_config_min_qty';

    const IS_QTY_DECIMAL = 'is_qty_decimal';

    const BACKORDERS = 'backorders';

    const USE_CONFIG_BACKORDERS = 'use_config_backorders';

    const MIN_SALE_QTY = 'min_sale_qty';

    const USE_CONFIG_MIN_SALE_QTY = 'use_config_min_sale_qty';

    const MAX_SALE_QTY = 'max_sale_qty';

    const USE_CONFIG_MAX_SALE_QTY = 'use_config_max_sale_qty';

    const IS_IN_STOCK = 'is_in_stock';

    const LOW_STOCK_DATE = 'low_stock_date';

    const NOTIFY_STOCK_QTY = 'notify_stock_qty';

    const USE_CONFIG_NOTIFY_STOCK_QTY = 'use_config_notify_stock_qty';

    const MANAGE_STOCK = 'manage_stock';

    const USE_CONFIG_MANAGE_STOCK = 'use_config_manage_stock';

    const STOCK_STATUS_CHANGED_AUTO = 'stock_status_changed_auto';

    const USE_CONFIG_QTY_INCREMENTS = 'use_config_qty_increments';

    const QTY_INCREMENTS = 'qty_increments';

    const USE_CONFIG_ENABLE_QTY_INC = 'use_config_enable_qty_inc';

    const ENABLE_QTY_INCREMENTS = 'enable_qty_increments';

    const IS_DECIMAL_DIVIDED = 'is_decimal_divided';
    /**#@-*/

    /**
     * @return int
     */
    public function getItemId()
    {
        return $this->_get(self::ITEM_ID);
    }

    /**
     * @return int
     */
    public function getProductId()
    {
        return $this->_get(self::PRODUCT_ID);
    }

    /**
     * @return int
     */
    public function getStockId()
    {
        return $this->_get(self::STOCK_ID);
    }

    /**
     * @return float
     */
    public function getQty()
    {
        return $this->_get(self::QTY);
    }

    /**
     * @return float
     */
    public function getMinQty()
    {
        return $this->_get(self::MIN_QTY);
    }

    /**
     * @return bool
     */
    public function isUseConfigMinQty()
    {
        return $this->_get(self::USE_CONFIG_MIN_QTY);
    }

    /**
     * @return bool
     */
    public function getIsQtyDecimal()
    {
        return $this->_get(self::IS_QTY_DECIMAL);
    }

    /**
     * @return bool
     */
    public function isBackorders()
    {
        return $this->_get(self::BACKORDERS);
    }

    /**
     * @return bool
     */
    public function isUseConfigBackorders()
    {
        return $this->_get(self::USE_CONFIG_BACKORDERS);
    }

    /**
     * @return float
     */
    public function getMinSaleQty()
    {
        return $this->_get(self::MIN_SALE_QTY);
    }

    /**
     * @return bool
     */
    public function isUseConfigMinSaleQty()
    {
        return $this->_get(self::USE_CONFIG_MIN_SALE_QTY);
    }

    /**
     * @return float
     */
    public function getMaxSaleQty()
    {
        return $this->_get(self::MAX_SALE_QTY);
    }

    /**
     * @return bool
     */
    public function isUseConfigMaxSaleQty()
    {
        return $this->_get(self::USE_CONFIG_MAX_SALE_QTY);
    }

    /**
     * @return bool
     */
    public function getIsInStock()
    {
        return $this->_get(self::IS_IN_STOCK);
    }

    /**
     * @return string
     */
    public function getLowStockDate()
    {
        return $this->_get(self::LOW_STOCK_DATE);
    }

    /**
     * @return float
     */
    public function getNotifyStockQty()
    {
        return $this->_get(self::NOTIFY_STOCK_QTY);
    }

    /**
     * @return bool
     */
    public function isUseConfigNotifyStockQty()
    {
        return $this->_get(self::USE_CONFIG_NOTIFY_STOCK_QTY);
    }

    /**
     * @return bool
     */
    public function isManageStock()
    {
        return $this->_get(self::MANAGE_STOCK);
    }

    /**
     * @return bool
     */
    public function isUseConfigManageStock()
    {
        return $this->_get(self::USE_CONFIG_MANAGE_STOCK);
    }

    /**
     * @return bool
     */
    public function isStockStatusChangedAuto()
    {
        return $this->_get(self::STOCK_STATUS_CHANGED_AUTO);
    }

    /**
     * @return bool
     */
    public function isUseConfigQtyIncrements()
    {
        return $this->_get(self::USE_CONFIG_QTY_INCREMENTS);
    }

    /**
     * @return float
     */
    public function getQtyIncrements()
    {
        return $this->_get(self::QTY_INCREMENTS);
    }

    /**
     * @return bool
     */
    public function isUseConfigEnableQtyInc()
    {
        return $this->_get(self::USE_CONFIG_ENABLE_QTY_INC);
    }

    /**
     * @return bool
     */
    public function isEnableQtyIncrements()
    {
        return $this->_get(self::ENABLE_QTY_INCREMENTS);
    }

    /**
     * @return bool
     */
    public function getIsDecimalDivided()
    {
        return $this->_get(self::IS_DECIMAL_DIVIDED);
    }
}
