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
namespace Magento\Sales\Service\V1\Data;

use Magento\Framework\Service\Data\AbstractExtensibleObject as DataObject;

/**
 * Class Invoice
 */
class Invoice extends DataObject
{
    const ENTITY_ID = 'entity_id';
    const STORE_ID = 'store_id';
    const BASE_GRAND_TOTAL = 'base_grand_total';
    const SHIPPING_TAX_AMOUNT = 'shipping_tax_amount';
    const TAX_AMOUNT = 'tax_amount';
    const BASE_TAX_AMOUNT = 'base_tax_amount';
    const STORE_TO_ORDER_RATE = 'store_to_order_rate';
    const BASE_SHIPPING_TAX_AMOUNT = 'base_shipping_tax_amount';
    const BASE_DISCOUNT_AMOUNT = 'base_discount_amount';
    const BASE_TO_ORDER_RATE = 'base_to_order_rate';
    const GRAND_TOTAL = 'grand_total';
    const SHIPPING_AMOUNT = 'shipping_amount';
    const SUBTOTAL_INCL_TAX = 'subtotal_incl_tax';
    const BASE_SUBTOTAL_INCL_TAX = 'base_subtotal_incl_tax';
    const STORE_TO_BASE_RATE = 'store_to_base_rate';
    const BASE_SHIPPING_AMOUNT = 'base_shipping_amount';
    const TOTAL_QTY = 'total_qty';
    const BASE_TO_GLOBAL_RATE = 'base_to_global_rate';
    const SUBTOTAL = 'subtotal';
    const BASE_SUBTOTAL = 'base_subtotal';
    const DISCOUNT_AMOUNT = 'discount_amount';
    const BILLING_ADDRESS_ID = 'billing_address_id';
    const IS_USED_FOR_REFUND = 'is_used_for_refund';
    const ORDER_ID = 'order_id';
    const EMAIL_SENT = 'email_sent';
    const CAN_VOID_FLAG = 'can_void_flag';
    const STATE = 'state';
    const SHIPPING_ADDRESS_ID = 'shipping_address_id';
    const STORE_CURRENCY_CODE = 'store_currency_code';
    const TRANSACTION_ID = 'transaction_id';
    const ORDER_CURRENCY_CODE = 'order_currency_code';
    const BASE_CURRENCY_CODE = 'base_currency_code';
    const GLOBAL_CURRENCY_CODE = 'global_currency_code';
    const INCREMENT_ID = 'increment_id';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    const HIDDEN_TAX_AMOUNT = 'hidden_tax_amount';
    const BASE_HIDDEN_TAX_AMOUNT = 'base_hidden_tax_amount';
    const SHIPPING_HIDDEN_TAX_AMOUNT = 'shipping_hidden_tax_amount';
    const BASE_SHIPPING_HIDDEN_TAX_AMNT = 'base_shipping_hidden_tax_amnt';
    const SHIPPING_INCL_TAX = 'shipping_incl_tax';
    const BASE_SHIPPING_INCL_TAX = 'base_shipping_incl_tax';
    const BASE_TOTAL_REFUNDED = 'base_total_refunded';
    const DISCOUNT_DESCRIPTION = 'discount_description';
    const ITEMS = 'items';

    /**
     * Returns base_currency_code
     *
     * @return string
     */
    public function getBaseCurrencyCode()
    {
        return $this->_get(self::BASE_CURRENCY_CODE);
    }

    /**
     * Returns base_discount_amount
     *
     * @return float
     */
    public function getBaseDiscountAmount()
    {
        return $this->_get(self::BASE_DISCOUNT_AMOUNT);
    }

    /**
     * Returns base_grand_total
     *
     * @return float
     */
    public function getBaseGrandTotal()
    {
        return $this->_get(self::BASE_GRAND_TOTAL);
    }

    /**
     * Returns base_hidden_tax_amount
     *
     * @return float
     */
    public function getBaseHiddenTaxAmount()
    {
        return $this->_get(self::BASE_HIDDEN_TAX_AMOUNT);
    }

    /**
     * Returns base_shipping_amount
     *
     * @return float
     */
    public function getBaseShippingAmount()
    {
        return $this->_get(self::BASE_SHIPPING_AMOUNT);
    }

    /**
     * Returns base_shipping_hidden_tax_amnt
     *
     * @return float
     */
    public function getBaseShippingHiddenTaxAmnt()
    {
        return $this->_get(self::BASE_SHIPPING_HIDDEN_TAX_AMNT);
    }

    /**
     * Returns base_shipping_incl_tax
     *
     * @return float
     */
    public function getBaseShippingInclTax()
    {
        return $this->_get(self::BASE_SHIPPING_INCL_TAX);
    }

    /**
     * Returns base_shipping_tax_amount
     *
     * @return float
     */
    public function getBaseShippingTaxAmount()
    {
        return $this->_get(self::BASE_SHIPPING_TAX_AMOUNT);
    }

    /**
     * Returns base_subtotal
     *
     * @return float
     */
    public function getBaseSubtotal()
    {
        return $this->_get(self::BASE_SUBTOTAL);
    }

    /**
     * Returns base_subtotal_incl_tax
     *
     * @return float
     */
    public function getBaseSubtotalInclTax()
    {
        return $this->_get(self::BASE_SUBTOTAL_INCL_TAX);
    }

    /**
     * Returns base_tax_amount
     *
     * @return float
     */
    public function getBaseTaxAmount()
    {
        return $this->_get(self::BASE_TAX_AMOUNT);
    }

    /**
     * Returns base_total_refunded
     *
     * @return float
     */
    public function getBaseTotalRefunded()
    {
        return $this->_get(self::BASE_TOTAL_REFUNDED);
    }

    /**
     * Returns base_to_global_rate
     *
     * @return float
     */
    public function getBaseToGlobalRate()
    {
        return $this->_get(self::BASE_TO_GLOBAL_RATE);
    }

    /**
     * Returns base_to_order_rate
     *
     * @return float
     */
    public function getBaseToOrderRate()
    {
        return $this->_get(self::BASE_TO_ORDER_RATE);
    }

    /**
     * Returns billing_address_id
     *
     * @return int
     */
    public function getBillingAddressId()
    {
        return $this->_get(self::BILLING_ADDRESS_ID);
    }

    /**
     * Returns can_void_flag
     *
     * @return int
     */
    public function getCanVoidFlag()
    {
        return $this->_get(self::CAN_VOID_FLAG);
    }

    /**
     * Returns created_at
     *
     * @return string
     */
    public function getCreatedAt()
    {
        return $this->_get(self::CREATED_AT);
    }

    /**
     * Returns discount_amount
     *
     * @return float
     */
    public function getDiscountAmount()
    {
        return $this->_get(self::DISCOUNT_AMOUNT);
    }

    /**
     * Returns discount_description
     *
     * @return string
     */
    public function getDiscountDescription()
    {
        return $this->_get(self::DISCOUNT_DESCRIPTION);
    }

    /**
     * Returns email_sent
     *
     * @return int
     */
    public function getEmailSent()
    {
        return $this->_get(self::EMAIL_SENT);
    }

    /**
     * Returns entity_id
     *
     * @return int
     */
    public function getEntityId()
    {
        return $this->_get(self::ENTITY_ID);
    }

    /**
     * Returns global_currency_code
     *
     * @return string
     */
    public function getGlobalCurrencyCode()
    {
        return $this->_get(self::GLOBAL_CURRENCY_CODE);
    }

    /**
     * Returns grand_total
     *
     * @return float
     */
    public function getGrandTotal()
    {
        return $this->_get(self::GRAND_TOTAL);
    }

    /**
     * Returns hidden_tax_amount
     *
     * @return float
     */
    public function getHiddenTaxAmount()
    {
        return $this->_get(self::HIDDEN_TAX_AMOUNT);
    }

    /**
     * Returns increment_id
     *
     * @return string
     */
    public function getIncrementId()
    {
        return $this->_get(self::INCREMENT_ID);
    }

    /**
     * Returns is_used_for_refund
     *
     * @return int
     */
    public function getIsUsedForRefund()
    {
        return $this->_get(self::IS_USED_FOR_REFUND);
    }

    /**
     * Returns order_currency_code
     *
     * @return string
     */
    public function getOrderCurrencyCode()
    {
        return $this->_get(self::ORDER_CURRENCY_CODE);
    }

    /**
     * Returns order_id
     *
     * @return int
     */
    public function getOrderId()
    {
        return $this->_get(self::ORDER_ID);
    }

    /**
     * Returns shipping_address_id
     *
     * @return int
     */
    public function getShippingAddressId()
    {
        return $this->_get(self::SHIPPING_ADDRESS_ID);
    }

    /**
     * Returns shipping_amount
     *
     * @return float
     */
    public function getShippingAmount()
    {
        return $this->_get(self::SHIPPING_AMOUNT);
    }

    /**
     * Returns shipping_hidden_tax_amount
     *
     * @return float
     */
    public function getShippingHiddenTaxAmount()
    {
        return $this->_get(self::SHIPPING_HIDDEN_TAX_AMOUNT);
    }

    /**
     * Returns shipping_incl_tax
     *
     * @return float
     */
    public function getShippingInclTax()
    {
        return $this->_get(self::SHIPPING_INCL_TAX);
    }

    /**
     * Returns shipping_tax_amount
     *
     * @return float
     */
    public function getShippingTaxAmount()
    {
        return $this->_get(self::SHIPPING_TAX_AMOUNT);
    }

    /**
     * Returns state
     *
     * @return int
     */
    public function getState()
    {
        return $this->_get(self::STATE);
    }

    /**
     * Returns store_currency_code
     *
     * @return string
     */
    public function getStoreCurrencyCode()
    {
        return $this->_get(self::STORE_CURRENCY_CODE);
    }

    /**
     * Returns store_id
     *
     * @return int
     */
    public function getStoreId()
    {
        return $this->_get(self::STORE_ID);
    }

    /**
     * Returns store_to_base_rate
     *
     * @return float
     */
    public function getStoreToBaseRate()
    {
        return $this->_get(self::STORE_TO_BASE_RATE);
    }

    /**
     * Returns store_to_order_rate
     *
     * @return float
     */
    public function getStoreToOrderRate()
    {
        return $this->_get(self::STORE_TO_ORDER_RATE);
    }

    /**
     * Returns subtotal
     *
     * @return float
     */
    public function getSubtotal()
    {
        return $this->_get(self::SUBTOTAL);
    }

    /**
     * Returns subtotal_incl_tax
     *
     * @return float
     */
    public function getSubtotalInclTax()
    {
        return $this->_get(self::SUBTOTAL_INCL_TAX);
    }

    /**
     * Returns tax_amount
     *
     * @return float
     */
    public function getTaxAmount()
    {
        return $this->_get(self::TAX_AMOUNT);
    }

    /**
     * Returns total_qty
     *
     * @return float
     */
    public function getTotalQty()
    {
        return $this->_get(self::TOTAL_QTY);
    }

    /**
     * Returns transaction_id
     *
     * @return string
     */
    public function getTransactionId()
    {
        return $this->_get(self::TRANSACTION_ID);
    }

    /**
     * Returns updated_at
     *
     * @return string
     */
    public function getUpdatedAt()
    {
        return $this->_get(self::UPDATED_AT);
    }

    /**
     * Returns invoice items
     *
     * @return \Magento\Sales\Service\V1\Data\InvoiceItem[]
     */
    public function getItems()
    {
        return (array)$this->_get(self::ITEMS);
    }
}
