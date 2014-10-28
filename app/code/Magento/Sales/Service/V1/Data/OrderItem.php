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
 * Class OrderItem
 */
class OrderItem extends DataObject
{
    const ITEM_ID = 'item_id';
    const ORDER_ID = 'order_id';
    const PARENT_ITEM_ID = 'parent_item_id';
    const QUOTE_ITEM_ID = 'quote_item_id';
    const STORE_ID = 'store_id';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    const PRODUCT_ID = 'product_id';
    const PRODUCT_TYPE = 'product_type';
    const PRODUCT_OPTIONS = 'product_options';
    const WEIGHT = 'weight';
    const IS_VIRTUAL = 'is_virtual';
    const SKU = 'sku';
    const NAME = 'name';
    const DESCRIPTION = 'description';
    const APPLIED_RULE_IDS = 'applied_rule_ids';
    const ADDITIONAL_DATA = 'additional_data';
    const IS_QTY_DECIMAL = 'is_qty_decimal';
    const NO_DISCOUNT = 'no_discount';
    const QTY_BACKORDERED = 'qty_backordered';
    const QTY_CANCELED = 'qty_canceled';
    const QTY_INVOICED = 'qty_invoiced';
    const QTY_ORDERED = 'qty_ordered';
    const QTY_REFUNDED = 'qty_refunded';
    const QTY_SHIPPED = 'qty_shipped';
    const BASE_COST = 'base_cost';
    const PRICE = 'price';
    const BASE_PRICE = 'base_price';
    const ORIGINAL_PRICE = 'original_price';
    const BASE_ORIGINAL_PRICE = 'base_original_price';
    const TAX_PERCENT = 'tax_percent';
    const TAX_AMOUNT = 'tax_amount';
    const BASE_TAX_AMOUNT = 'base_tax_amount';
    const TAX_INVOICED = 'tax_invoiced';
    const BASE_TAX_INVOICED = 'base_tax_invoiced';
    const DISCOUNT_PERCENT = 'discount_percent';
    const DISCOUNT_AMOUNT = 'discount_amount';
    const BASE_DISCOUNT_AMOUNT = 'base_discount_amount';
    const DISCOUNT_INVOICED = 'discount_invoiced';
    const BASE_DISCOUNT_INVOICED = 'base_discount_invoiced';
    const AMOUNT_REFUNDED = 'amount_refunded';
    const BASE_AMOUNT_REFUNDED = 'base_amount_refunded';
    const ROW_TOTAL = 'row_total';
    const BASE_ROW_TOTAL = 'base_row_total';
    const ROW_INVOICED = 'row_invoiced';
    const BASE_ROW_INVOICED = 'base_row_invoiced';
    const ROW_WEIGHT = 'row_weight';
    const BASE_TAX_BEFORE_DISCOUNT = 'base_tax_before_discount';
    const TAX_BEFORE_DISCOUNT = 'tax_before_discount';
    const EXT_ORDER_ITEM_ID = 'ext_order_item_id';
    const LOCKED_DO_INVOICE = 'locked_do_invoice';
    const LOCKED_DO_SHIP = 'locked_do_ship';
    const PRICE_INCL_TAX = 'price_incl_tax';
    const BASE_PRICE_INCL_TAX = 'base_price_incl_tax';
    const ROW_TOTAL_INCL_TAX = 'row_total_incl_tax';
    const BASE_ROW_TOTAL_INCL_TAX = 'base_row_total_incl_tax';
    const HIDDEN_TAX_AMOUNT = 'hidden_tax_amount';
    const BASE_HIDDEN_TAX_AMOUNT = 'base_hidden_tax_amount';
    const HIDDEN_TAX_INVOICED = 'hidden_tax_invoiced';
    const BASE_HIDDEN_TAX_INVOICED = 'base_hidden_tax_invoiced';
    const HIDDEN_TAX_REFUNDED = 'hidden_tax_refunded';
    const BASE_HIDDEN_TAX_REFUNDED = 'base_hidden_tax_refunded';
    const IS_NOMINAL = 'is_nominal';
    const TAX_CANCELED = 'tax_canceled';
    const HIDDEN_TAX_CANCELED = 'hidden_tax_canceled';
    const TAX_REFUNDED = 'tax_refunded';
    const BASE_TAX_REFUNDED = 'base_tax_refunded';
    const DISCOUNT_REFUNDED = 'discount_refunded';
    const BASE_DISCOUNT_REFUNDED = 'base_discount_refunded';

    /**
     * int
     */
    const GW_ID = 'gw_id';

    /**
     * float
     */
    const GW_BASE_PRICE = 'gw_base_price';

    /**
     * float
     */
    const GW_PRICE = 'gw_price';

    /**
     * float
     */
    const GW_BASE_TAX_AMOUNT = 'gw_base_tax_amount';

    /**
     * float
     */
    const GW_TAX_AMOUNT = 'gw_tax_amount';

    /**
     * float
     */
    const GW_BASE_PRICE_INVOICED = 'gw_base_price_invoiced';

    /**
     * float
     */
    const GW_PRICE_INVOICED = 'gw_price_invoiced';

    /**
     * float
     */
    const GW_BASE_TAX_AMOUNT_INVOICED = 'gw_base_tax_amount_invoiced';

    /**
     * float
     */
    const GW_TAX_AMOUNT_INVOICED = 'gw_tax_amount_invoiced';

    /**
     * float
     */
    const GW_BASE_PRICE_REFUNDED = 'gw_base_price_refunded';

    /**
     * float
     */
    const GW_PRICE_REFUNDED = 'gw_price_refunded';

    /**
     * float
     */
    const GW_BASE_TAX_AMOUNT_REFUNDED = 'gw_base_tax_amount_refunded';

    /**
     * float
     */
    const GW_TAX_AMOUNT_REFUNDED = 'gw_tax_amount_refunded';

    /**
     * int
     */
    const FREE_SHIPPING = 'free_shipping';

    /**
     * float
     */
    const QTY_RETURNED = 'qty_returned';

    /**
     * int
     */
    const EVENT_ID = 'event_id';

    /**
     * float
     */
    const BASE_WEEE_TAX_APPLIED_AMOUNT = 'base_weee_tax_applied_amount';

    /**
     * float
     */
    const BASE_WEEE_TAX_APPLIED_ROW_AMNT = 'base_weee_tax_applied_row_amnt';

    /**
     * float
     */
    const WEEE_TAX_APPLIED_AMOUNT = 'weee_tax_applied_amount';

    /**
     * float
     */
    const WEEE_TAX_APPLIED_ROW_AMOUNT = 'weee_tax_applied_row_amount';

    /**
     * string
     */
    const WEEE_TAX_APPLIED = 'weee_tax_applied';

    /**
     * float
     */
    const WEEE_TAX_DISPOSITION = 'weee_tax_disposition';

    /**
     * float
     */
    const WEEE_TAX_ROW_DISPOSITION = 'weee_tax_row_disposition';

    /**
     * float
     */
    const BASE_WEEE_TAX_DISPOSITION = 'base_weee_tax_disposition';

    /**
     * float
     */
    const BASE_WEEE_TAX_ROW_DISPOSITION = 'base_weee_tax_row_disposition';

    /**
     * Returns additional_data
     *
     * @return string
     */
    public function getAdditionalData()
    {
        return $this->_get(self::ADDITIONAL_DATA);
    }

    /**
     * Returns amount_refunded
     *
     * @return float
     */
    public function getAmountRefunded()
    {
        return $this->_get(self::AMOUNT_REFUNDED);
    }

    /**
     * Returns applied_rule_ids
     *
     * @return string
     */
    public function getAppliedRuleIds()
    {
        return $this->_get(self::APPLIED_RULE_IDS);
    }

    /**
     * Returns base_amount_refunded
     *
     * @return float
     */
    public function getBaseAmountRefunded()
    {
        return $this->_get(self::BASE_AMOUNT_REFUNDED);
    }

    /**
     * Returns base_cost
     *
     * @return float
     */
    public function getBaseCost()
    {
        return $this->_get(self::BASE_COST);
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
     * Returns base_discount_invoiced
     *
     * @return float
     */
    public function getBaseDiscountInvoiced()
    {
        return $this->_get(self::BASE_DISCOUNT_INVOICED);
    }

    /**
     * Returns base_discount_refunded
     *
     * @return float
     */
    public function getBaseDiscountRefunded()
    {
        return $this->_get(self::BASE_DISCOUNT_REFUNDED);
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
     * Returns base_hidden_tax_invoiced
     *
     * @return float
     */
    public function getBaseHiddenTaxInvoiced()
    {
        return $this->_get(self::BASE_HIDDEN_TAX_INVOICED);
    }

    /**
     * Returns base_hidden_tax_refunded
     *
     * @return float
     */
    public function getBaseHiddenTaxRefunded()
    {
        return $this->_get(self::BASE_HIDDEN_TAX_REFUNDED);
    }

    /**
     * Returns base_original_price
     *
     * @return float
     */
    public function getBaseOriginalPrice()
    {
        return $this->_get(self::BASE_ORIGINAL_PRICE);
    }

    /**
     * Returns base_price
     *
     * @return float
     */
    public function getBasePrice()
    {
        return $this->_get(self::BASE_PRICE);
    }

    /**
     * Returns base_price_incl_tax
     *
     * @return float
     */
    public function getBasePriceInclTax()
    {
        return $this->_get(self::BASE_PRICE_INCL_TAX);
    }

    /**
     * Returns base_row_invoiced
     *
     * @return float
     */
    public function getBaseRowInvoiced()
    {
        return $this->_get(self::BASE_ROW_INVOICED);
    }

    /**
     * Returns base_row_total
     *
     * @return float
     */
    public function getBaseRowTotal()
    {
        return $this->_get(self::BASE_ROW_TOTAL);
    }

    /**
     * Returns base_row_total_incl_tax
     *
     * @return float
     */
    public function getBaseRowTotalInclTax()
    {
        return $this->_get(self::BASE_ROW_TOTAL_INCL_TAX);
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
     * Returns base_tax_before_discount
     *
     * @return float
     */
    public function getBaseTaxBeforeDiscount()
    {
        return $this->_get(self::BASE_TAX_BEFORE_DISCOUNT);
    }

    /**
     * Returns base_tax_invoiced
     *
     * @return float
     */
    public function getBaseTaxInvoiced()
    {
        return $this->_get(self::BASE_TAX_INVOICED);
    }

    /**
     * Returns base_tax_refunded
     *
     * @return float
     */
    public function getBaseTaxRefunded()
    {
        return $this->_get(self::BASE_TAX_REFUNDED);
    }

    /**
     * Returns base_weee_tax_applied_amount
     *
     * @return float
     */
    public function getBaseWeeeTaxAppliedAmount()
    {
        return $this->_get(self::BASE_WEEE_TAX_APPLIED_AMOUNT);
    }

    /**
     * Returns base_weee_tax_applied_row_amnt
     *
     * @return float
     */
    public function getBaseWeeeTaxAppliedRowAmnt()
    {
        return $this->_get(self::BASE_WEEE_TAX_APPLIED_ROW_AMNT);
    }

    /**
     * Returns base_weee_tax_disposition
     *
     * @return float
     */
    public function getBaseWeeeTaxDisposition()
    {
        return $this->_get(self::BASE_WEEE_TAX_DISPOSITION);
    }

    /**
     * Returns base_weee_tax_row_disposition
     *
     * @return float
     */
    public function getBaseWeeeTaxRowDisposition()
    {
        return $this->_get(self::BASE_WEEE_TAX_ROW_DISPOSITION);
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
     * Returns description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->_get(self::DESCRIPTION);
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
     * Returns discount_invoiced
     *
     * @return float
     */
    public function getDiscountInvoiced()
    {
        return $this->_get(self::DISCOUNT_INVOICED);
    }

    /**
     * Returns discount_percent
     *
     * @return float
     */
    public function getDiscountPercent()
    {
        return $this->_get(self::DISCOUNT_PERCENT);
    }

    /**
     * Returns discount_refunded
     *
     * @return float
     */
    public function getDiscountRefunded()
    {
        return $this->_get(self::DISCOUNT_REFUNDED);
    }

    /**
     * Returns event_id
     *
     * @return int
     */
    public function getEventId()
    {
        return $this->_get(self::EVENT_ID);
    }

    /**
     * Returns ext_order_item_id
     *
     * @return string
     */
    public function getExtOrderItemId()
    {
        return $this->_get(self::EXT_ORDER_ITEM_ID);
    }

    /**
     * Returns free_shipping
     *
     * @return int
     */
    public function getFreeShipping()
    {
        return $this->_get(self::FREE_SHIPPING);
    }

    /**
     * Returns gw_base_price
     *
     * @return float
     */
    public function getGwBasePrice()
    {
        return $this->_get(self::GW_BASE_PRICE);
    }

    /**
     * Returns gw_base_price_invoiced
     *
     * @return float
     */
    public function getGwBasePriceInvoiced()
    {
        return $this->_get(self::GW_BASE_PRICE_INVOICED);
    }

    /**
     * Returns gw_base_price_refunded
     *
     * @return float
     */
    public function getGwBasePriceRefunded()
    {
        return $this->_get(self::GW_BASE_PRICE_REFUNDED);
    }

    /**
     * Returns gw_base_tax_amount
     *
     * @return float
     */
    public function getGwBaseTaxAmount()
    {
        return $this->_get(self::GW_BASE_TAX_AMOUNT);
    }

    /**
     * Returns gw_base_tax_amount_invoiced
     *
     * @return float
     */
    public function getGwBaseTaxAmountInvoiced()
    {
        return $this->_get(self::GW_BASE_TAX_AMOUNT_INVOICED);
    }

    /**
     * Returns gw_base_tax_amount_refunded
     *
     * @return float
     */
    public function getGwBaseTaxAmountRefunded()
    {
        return $this->_get(self::GW_BASE_TAX_AMOUNT_REFUNDED);
    }

    /**
     * Returns gw_id
     *
     * @return int
     */
    public function getGwId()
    {
        return $this->_get(self::GW_ID);
    }

    /**
     * Returns gw_price
     *
     * @return float
     */
    public function getGwPrice()
    {
        return $this->_get(self::GW_PRICE);
    }

    /**
     * Returns gw_price_invoiced
     *
     * @return float
     */
    public function getGwPriceInvoiced()
    {
        return $this->_get(self::GW_PRICE_INVOICED);
    }

    /**
     * Returns gw_price_refunded
     *
     * @return float
     */
    public function getGwPriceRefunded()
    {
        return $this->_get(self::GW_PRICE_REFUNDED);
    }

    /**
     * Returns gw_tax_amount
     *
     * @return float
     */
    public function getGwTaxAmount()
    {
        return $this->_get(self::GW_TAX_AMOUNT);
    }

    /**
     * Returns gw_tax_amount_invoiced
     *
     * @return float
     */
    public function getGwTaxAmountInvoiced()
    {
        return $this->_get(self::GW_TAX_AMOUNT_INVOICED);
    }

    /**
     * Returns gw_tax_amount_refunded
     *
     * @return float
     */
    public function getGwTaxAmountRefunded()
    {
        return $this->_get(self::GW_TAX_AMOUNT_REFUNDED);
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
     * Returns hidden_tax_canceled
     *
     * @return float
     */
    public function getHiddenTaxCanceled()
    {
        return $this->_get(self::HIDDEN_TAX_CANCELED);
    }

    /**
     * Returns hidden_tax_invoiced
     *
     * @return float
     */
    public function getHiddenTaxInvoiced()
    {
        return $this->_get(self::HIDDEN_TAX_INVOICED);
    }

    /**
     * Returns hidden_tax_refunded
     *
     * @return float
     */
    public function getHiddenTaxRefunded()
    {
        return $this->_get(self::HIDDEN_TAX_REFUNDED);
    }

    /**
     * Returns is_nominal
     *
     * @return int
     */
    public function getIsNominal()
    {
        return $this->_get(self::IS_NOMINAL);
    }

    /**
     * Returns is_qty_decimal
     *
     * @return int
     */
    public function getIsQtyDecimal()
    {
        return $this->_get(self::IS_QTY_DECIMAL);
    }

    /**
     * Returns is_virtual
     *
     * @return int
     */
    public function getIsVirtual()
    {
        return $this->_get(self::IS_VIRTUAL);
    }

    /**
     * Returns item_id
     *
     * @return int
     */
    public function getItemId()
    {
        return $this->_get(self::ITEM_ID);
    }

    /**
     * Returns locked_do_invoice
     *
     * @return int
     */
    public function getLockedDoInvoice()
    {
        return $this->_get(self::LOCKED_DO_INVOICE);
    }

    /**
     * Returns locked_do_ship
     *
     * @return int
     */
    public function getLockedDoShip()
    {
        return $this->_get(self::LOCKED_DO_SHIP);
    }

    /**
     * Returns name
     *
     * @return string
     */
    public function getName()
    {
        return $this->_get(self::NAME);
    }

    /**
     * Returns no_discount
     *
     * @return int
     */
    public function getNoDiscount()
    {
        return $this->_get(self::NO_DISCOUNT);
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
     * Returns original_price
     *
     * @return float
     */
    public function getOriginalPrice()
    {
        return $this->_get(self::ORIGINAL_PRICE);
    }

    /**
     * Returns parent_item_id
     *
     * @return int
     */
    public function getParentItemId()
    {
        return $this->_get(self::PARENT_ITEM_ID);
    }

    /**
     * Returns price
     *
     * @return float
     */
    public function getPrice()
    {
        return $this->_get(self::PRICE);
    }

    /**
     * Returns price_incl_tax
     *
     * @return float
     */
    public function getPriceInclTax()
    {
        return $this->_get(self::PRICE_INCL_TAX);
    }

    /**
     * Returns product_id
     *
     * @return int
     */
    public function getProductId()
    {
        return $this->_get(self::PRODUCT_ID);
    }

    /**
     * Returns product_options
     *
     * @return string
     */
    public function getProductOptions()
    {
        return $this->_get(self::PRODUCT_OPTIONS);
    }

    /**
     * Returns product_type
     *
     * @return string
     */
    public function getProductType()
    {
        return $this->_get(self::PRODUCT_TYPE);
    }

    /**
     * Returns qty_backordered
     *
     * @return float
     */
    public function getQtyBackordered()
    {
        return $this->_get(self::QTY_BACKORDERED);
    }

    /**
     * Returns qty_canceled
     *
     * @return float
     */
    public function getQtyCanceled()
    {
        return $this->_get(self::QTY_CANCELED);
    }

    /**
     * Returns qty_invoiced
     *
     * @return float
     */
    public function getQtyInvoiced()
    {
        return $this->_get(self::QTY_INVOICED);
    }

    /**
     * Returns qty_ordered
     *
     * @return float
     */
    public function getQtyOrdered()
    {
        return $this->_get(self::QTY_ORDERED);
    }

    /**
     * Returns qty_refunded
     *
     * @return float
     */
    public function getQtyRefunded()
    {
        return $this->_get(self::QTY_REFUNDED);
    }

    /**
     * Returns qty_returned
     *
     * @return float
     */
    public function getQtyReturned()
    {
        return $this->_get(self::QTY_RETURNED);
    }

    /**
     * Returns qty_shipped
     *
     * @return float
     */
    public function getQtyShipped()
    {
        return $this->_get(self::QTY_SHIPPED);
    }

    /**
     * Returns quote_item_id
     *
     * @return int
     */
    public function getQuoteItemId()
    {
        return $this->_get(self::QUOTE_ITEM_ID);
    }

    /**
     * Returns row_invoiced
     *
     * @return float
     */
    public function getRowInvoiced()
    {
        return $this->_get(self::ROW_INVOICED);
    }

    /**
     * Returns row_total
     *
     * @return float
     */
    public function getRowTotal()
    {
        return $this->_get(self::ROW_TOTAL);
    }

    /**
     * Returns row_total_incl_tax
     *
     * @return float
     */
    public function getRowTotalInclTax()
    {
        return $this->_get(self::ROW_TOTAL_INCL_TAX);
    }

    /**
     * Returns row_weight
     *
     * @return float
     */
    public function getRowWeight()
    {
        return $this->_get(self::ROW_WEIGHT);
    }

    /**
     * Returns sku
     *
     * @return string
     */
    public function getSku()
    {
        return $this->_get(self::SKU);
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
     * Returns tax_amount
     *
     * @return float
     */
    public function getTaxAmount()
    {
        return $this->_get(self::TAX_AMOUNT);
    }

    /**
     * Returns tax_before_discount
     *
     * @return float
     */
    public function getTaxBeforeDiscount()
    {
        return $this->_get(self::TAX_BEFORE_DISCOUNT);
    }

    /**
     * Returns tax_canceled
     *
     * @return float
     */
    public function getTaxCanceled()
    {
        return $this->_get(self::TAX_CANCELED);
    }

    /**
     * Returns tax_invoiced
     *
     * @return float
     */
    public function getTaxInvoiced()
    {
        return $this->_get(self::TAX_INVOICED);
    }

    /**
     * Returns tax_percent
     *
     * @return float
     */
    public function getTaxPercent()
    {
        return $this->_get(self::TAX_PERCENT);
    }

    /**
     * Returns tax_refunded
     *
     * @return float
     */
    public function getTaxRefunded()
    {
        return $this->_get(self::TAX_REFUNDED);
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
     * Returns weee_tax_applied
     *
     * @return string
     */
    public function getWeeeTaxApplied()
    {
        return $this->_get(self::WEEE_TAX_APPLIED);
    }

    /**
     * Returns weee_tax_applied_amount
     *
     * @return float
     */
    public function getWeeeTaxAppliedAmount()
    {
        return $this->_get(self::WEEE_TAX_APPLIED_AMOUNT);
    }

    /**
     * Returns weee_tax_applied_row_amount
     *
     * @return float
     */
    public function getWeeeTaxAppliedRowAmount()
    {
        return $this->_get(self::WEEE_TAX_APPLIED_ROW_AMOUNT);
    }

    /**
     * Returns weee_tax_disposition
     *
     * @return float
     */
    public function getWeeeTaxDisposition()
    {
        return $this->_get(self::WEEE_TAX_DISPOSITION);
    }

    /**
     * Returns weee_tax_row_disposition
     *
     * @return float
     */
    public function getWeeeTaxRowDisposition()
    {
        return $this->_get(self::WEEE_TAX_ROW_DISPOSITION);
    }

    /**
     * Returns weight
     *
     * @return float
     */
    public function getWeight()
    {
        return $this->_get(self::WEIGHT);
    }
}
