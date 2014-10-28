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
 * Stock item details data object
 *
 * @codeCoverageIgnore
 */
class StockItemDetails extends AbstractExtensibleObject
{
    /**#@+
     * Stock item object data keys
     */
    const QTY = 'qty';

    const MIN_QTY = 'min_qty';

    const IS_QTY_DECIMAL = 'is_qty_decimal';

    const BACKORDERS = 'backorders';

    const MIN_SALE_QTY = 'min_sale_qty';

    const MAX_SALE_QTY = 'max_sale_qty';

    const IS_IN_STOCK = 'is_in_stock';

    const LOW_STOCK_DATE = 'low_stock_date';

    const NOTIFY_STOCK_QTY = 'notify_stock_qty';

    const MANAGE_STOCK = 'manage_stock';

    const STOCK_STATUS_CHANGED_AUTO = 'stock_status_changed_auto';

    const QTY_INCREMENTS = 'qty_increments';

    const ENABLE_QTY_INCREMENTS = 'enable_qty_increments';

    const IS_DECIMAL_DIVIDED = 'is_decimal_divided';
    /**#@-*/

    /**
     * @return float|null
     */
    public function getQty()
    {
        return $this->_get(self::QTY);
    }

    /**
     * @return float|null
     */
    public function getMinQty()
    {
        return $this->_get(self::MIN_QTY);
    }

    /**
     * @return bool|null
     */
    public function getIsQtyDecimal()
    {
        return $this->_get(self::IS_QTY_DECIMAL);
    }

    /**
     * @return bool|null
     */
    public function isBackorders()
    {
        return $this->_get(self::BACKORDERS);
    }

    /**
     * @return float|null
     */
    public function getMinSaleQty()
    {
        return $this->_get(self::MIN_SALE_QTY);
    }

    /**
     * @return float|null
     */
    public function getMaxSaleQty()
    {
        return $this->_get(self::MAX_SALE_QTY);
    }

    /**
     * @return bool|null
     */
    public function getIsInStock()
    {
        return $this->_get(self::IS_IN_STOCK);
    }

    /**
     * @return string|null
     */
    public function getLowStockDate()
    {
        return $this->_get(self::LOW_STOCK_DATE);
    }

    /**
     * @return float|null
     */
    public function getNotifyStockQty()
    {
        return $this->_get(self::NOTIFY_STOCK_QTY);
    }

    /**
     * @return bool|null
     */
    public function isManageStock()
    {
        return $this->_get(self::MANAGE_STOCK);
    }

    /**
     * @return bool|null
     */
    public function isStockStatusChangedAuto()
    {
        return $this->_get(self::STOCK_STATUS_CHANGED_AUTO);
    }

    /**
     * @return float|null
     */
    public function getQtyIncrements()
    {
        return $this->_get(self::QTY_INCREMENTS);
    }

    /**
     * @return bool|null
     */
    public function isEnableQtyIncrements()
    {
        return $this->_get(self::ENABLE_QTY_INCREMENTS);
    }

    /**
     * @return bool|null
     */
    public function getIsDecimalDivided()
    {
        return $this->_get(self::IS_DECIMAL_DIVIDED);
    }
}
