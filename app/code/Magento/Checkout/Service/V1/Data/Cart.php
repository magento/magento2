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
namespace Magento\Checkout\Service\V1\Data;

/**
 * Cart data object
 *
 * @codeCoverageIgnore
 */
class Cart extends \Magento\Framework\Service\Data\AbstractExtensibleObject
{
    const ID = 'id';

    const STORE_ID = 'store_id';

    const CREATED_AT = 'created_at';

    const UPDATED_AT = 'updated_at';

    const CONVERTED_AT = 'converted_at';

    const IS_ACTIVE = 'is_active';

    const IS_VIRTUAL = 'is_virtual';

    const ITEMS = 'items';

    const ITEMS_COUNT = 'items_count';

    const ITEMS_QUANTITY = 'items_qty';

    const CUSTOMER = 'customer';

    const CHECKOUT_METHOD = 'checkout_method';

    const SHIPPING_ADDRESS = 'shipping_address';

    const BILLING_ADDRESS = 'shipping_address';

    const TOTALS = 'totals';

    const RESERVED_ORDER_ID = 'reserved_order_id';

    const ORIG_ORDER_ID = 'orig_order_id';

    const CURRENCY = 'currency';

    /**
     * Cart/Quote id
     *
     * @return int
     */
    public function getId()
    {
        return $this->_get(self::ID);
    }

    /**
     * Store id
     *
     * @return int
     */
    public function getStoreId()
    {
        return $this->_get(self::STORE_ID);
    }

    /**
     * Creation date and time
     *
     * @return string
     */
    public function getCreatedAt()
    {
        return $this->_get(self::CREATED_AT);
    }

    /**
     * Last update date and time
     *
     * @return string
     */
    public function getUpdatedAt()
    {
        return $this->_get(self::UPDATED_AT);
    }

    /**
     * Get conversion date and time
     *
     * @return string|null
     */
    public function getConvertedAt()
    {
        return $this->_get(self::CONVERTED_AT);
    }

    /**
     * Get active status flag
     *
     * @return bool|null
     */
    public function getIsActive()
    {
        $value = $this->_get(self::IS_ACTIVE);
        if (!is_null($value)) {
            $value = (bool)$value;
        }

        return $value;
    }

    /**
     * Get virtual flag(cart contains virtual products)
     *
     * @return bool|null
     */
    public function getIsVirtual()
    {
        $value = $this->_get(self::IS_VIRTUAL);
        if (!is_null($value)) {
            $value = (bool)$value;
        }

        return $value;
    }

    /**
     * Get cart items
     *
     * @return \Magento\Checkout\Service\V1\Data\Cart\Item[]|null
     */
    public function getItems()
    {
        return $this->_get(self::ITEMS);
    }

    /**
     * Amount of different items/products in cart
     *
     * @return int|null
     */
    public function getItemsCount()
    {
        return $this->_get(self::ITEMS_COUNT);
    }

    /**
     * Get quantity of all items/products in cart
     *
     * @return float|null
     */
    public function getItemsQty()
    {
        return $this->_get(self::ITEMS_QUANTITY);
    }

    /**
     * Get customer data
     *
     * @return \Magento\Checkout\Service\V1\Data\Cart\Customer
     */
    public function getCustomer()
    {
        return $this->_get(self::CUSTOMER);
    }

    /**
     * Get checkout method
     *
     * @return string|null
     */
    public function getCheckoutMethod()
    {
        return $this->_get(self::CHECKOUT_METHOD);
    }

    /**
     * @return \Magento\Checkout\Service\V1\Data\Cart\Address|null
     */
    public function getShippingAddress()
    {
        return $this->_get(self::SHIPPING_ADDRESS);
    }

    /**
     * @return \Magento\Checkout\Service\V1\Data\Cart\Address|null
     */
    public function getBillingAddress()
    {
        return $this->_get(self::BILLING_ADDRESS);
    }

    /**
     * @return \Magento\Checkout\Service\V1\Data\Cart\Totals|null
     */
    public function getTotals()
    {
        return $this->_get(self::TOTALS);
    }

    /**
     * Get reserved order id
     *
     * @return string|null
     */
    public function getReservedOrderId()
    {
        return $this->_get(self::RESERVED_ORDER_ID);
    }

    /**
     * Get original order id
     *
     * @return string|null
     */
    public function getOrigOrderId()
    {
        return $this->_get(self::ORIG_ORDER_ID);
    }

    /**
     * Get currency information
     *
     * @return \Magento\Checkout\Service\V1\Data\Cart\Currency|null
     */
    public function getCurrency()
    {
        return $this->_get(self::CURRENCY);
    }
}
