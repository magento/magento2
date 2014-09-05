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

namespace Magento\Bundle\Service\V1\Product\Link\Data;

/**
 * Bundle ProductLink Service Data Object
 *
 * @codeCoverageIgnore
 */
class ProductLink extends \Magento\Framework\Service\Data\AbstractExtensibleObject
{
    /**
     * Constants for Data Object keys
     */
    const SKU = 'product_sku';
    const POSITION = 'position';
    const IS_DEFAULT = 'default';
    const PRICE_TYPE = 'slection_price_type';
    const PRICE_VALUE = 'slection_price_value';
    const QUANTITY = 'selection_qty';
    const CAN_CHANGE_QUANTITY = 'selection_can_change_qty';

    /**
     * Get product sku
     *
     * @return string
     */
    public function getSku()
    {
        return $this->_get(self::SKU);
    }

    /**
     * Get product position
     *
     * @return int
     */
    public function getPosition()
    {
        return $this->_get(self::POSITION);
    }

    /**
     * @return boolean
     */
    public function isDefault()
    {
        return $this->_get(self::IS_DEFAULT);
    }

    /**
     * Get price type
     *
     * @return int
     */
    public function getPriceType()
    {
        return $this->_get(self::PRICE_TYPE);
    }

    /**
     * Get price value
     *
     * @return float
     */
    public function getPriceValue()
    {
        return $this->_get(self::PRICE_VALUE);
    }

    /**
     * Get quantity
     *
     * @return int
     */
    public function getQuantity()
    {
        return $this->_get(self::QUANTITY);
    }

    /**
     * Get whether quantity could be changed
     *
     * @return int
     */
    public function getCanChangeQuantity()
    {
        return $this->_get(self::CAN_CHANGE_QUANTITY);
    }
}
