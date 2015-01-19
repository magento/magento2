<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Api\Data;

/**
 * Credit memo item interface.
 *
 * After a customer places and pays for an order and an invoice has been issued, the merchant can create a credit memo
 * to refund all or part of the amount paid for any returned or undelivered items. The memo restores funds to the
 * customer account so that the customer can make future purchases. A credit memo item is an invoiced item for which
 * a merchant creates a credit memo.
 */
interface CreditmemoItemInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case.
     */
    /*
     * Credit memo item ID.
     */
    const ENTITY_ID = 'entity_id';
    /*
     * Parent ID.
     */
    const PARENT_ID = 'parent_id';
    /*
     * Base price.
     */
    const BASE_PRICE = 'base_price';
    /*
     * Tax amount.
     */
    const TAX_AMOUNT = 'tax_amount';
    /*
     * Base row total.
     */
    const BASE_ROW_TOTAL = 'base_row_total';
    /*
     * Base row total.
     */
    const DISCOUNT_AMOUNT = 'discount_amount';
    /*
     * Row total.
     */
    const ROW_TOTAL = 'row_total';
    /*
     * Base discount amount.
     */
    const BASE_DISCOUNT_AMOUNT = 'base_discount_amount';
    /*
     * Price including tax.
     */
    const PRICE_INCL_TAX = 'price_incl_tax';
    /*
     * Base tax amount.
     */
    const BASE_TAX_AMOUNT = 'base_tax_amount';
    /*
     * Base price including tax.
     */
    const BASE_PRICE_INCL_TAX = 'base_price_incl_tax';
    /*
     * Quantity.
     */
    const QTY = 'qty';
    /*
     * Base cost.
     */
    const BASE_COST = 'base_cost';
    /*
     * Price.
     */
    const PRICE = 'price';
    /*
     * Base row total including tax.
     */
    const BASE_ROW_TOTAL_INCL_TAX = 'base_row_total_incl_tax';
    /*
     * Row total including tax.
     */
    const ROW_TOTAL_INCL_TAX = 'row_total_incl_tax';
    /*
     * Product ID.
     */
    const PRODUCT_ID = 'product_id';
    /*
     * Order item ID.
     */
    const ORDER_ITEM_ID = 'order_item_id';
    /*
     * Additional data.
     */
    const ADDITIONAL_DATA = 'additional_data';
    /*
     * Description.
     */
    const DESCRIPTION = 'description';
    /*
     * SKU.
     */
    const SKU = 'sku';
    /*
     * Name.
     */
    const NAME = 'name';
    /*
     * Hidden tax amount.
     */
    const HIDDEN_TAX_AMOUNT = 'hidden_tax_amount';
    /*
     * Base hidden tax amount.
     */
    const BASE_HIDDEN_TAX_AMOUNT = 'base_hidden_tax_amount';
    /*
     * WEEE tax disposition.
     */
    const WEEE_TAX_DISPOSITION = 'weee_tax_disposition';
    /*
     * WEEE tax row disposition.
     */
    const WEEE_TAX_ROW_DISPOSITION = 'weee_tax_row_disposition';
    /*
     * Base WEEE tax disposition.
     */
    const BASE_WEEE_TAX_DISPOSITION = 'base_weee_tax_disposition';
    /*
     * Base WEEE tax row disposition.
     */
    const BASE_WEEE_TAX_ROW_DISPOSITION = 'base_weee_tax_row_disposition';
    /*
     * WEEE tax applied.
     */
    const WEEE_TAX_APPLIED = 'weee_tax_applied';
    /*
     * Base WEEE tax applied amount.
     */
    const BASE_WEEE_TAX_APPLIED_AMOUNT = 'base_weee_tax_applied_amount';
    /*
     * Base WEEE tax applied row amount.
     */
    const BASE_WEEE_TAX_APPLIED_ROW_AMNT = 'base_weee_tax_applied_row_amnt';
    /*
     * WEEE tax applied amount.
     */
    const WEEE_TAX_APPLIED_AMOUNT = 'weee_tax_applied_amount';
    /*
     * WEEE tax applied row amount.
     */
    const WEEE_TAX_APPLIED_ROW_AMOUNT = 'weee_tax_applied_row_amount';

    /**
     * Gets the additional data for a credit memo item.
     *
     * @return string Additional data.
     */
    public function getAdditionalData();

    /**
     * Gets the base cost for a credit memo item.
     *
     * @return float
     */
    public function getBaseCost();

    /**
     * Gets the base discount amount for a credit memo item.
     *
     * @return float
     */
    public function getBaseDiscountAmount();

    /**
     * Gets the base hidden tax amount for a credit memo item.
     *
     * @return float
     */
    public function getBaseHiddenTaxAmount();

    /**
     * Gets the base price for a credit memo item.
     *
     * @return float
     */
    public function getBasePrice();

    /**
     * Gets the base price including tax for a credit memo item.
     *
     * @return float Base price including tax.
     */
    public function getBasePriceInclTax();

    /**
     * Gets the base row total for a credit memo item.
     *
     * @return float Base row total.
     */
    public function getBaseRowTotal();

    /**
     * Gets the base row total including tax for a credit memo item.
     *
     * @return float Base row total including tax.
     */
    public function getBaseRowTotalInclTax();

    /**
     * Gets the base tax amount for a credit memo item.
     *
     * @return float Base tax amount.
     */
    public function getBaseTaxAmount();

    /**
     * Gets the base WEEE tax applied amount for a credit memo item.
     *
     * @return float Base WEEE tax applied amount.
     */
    public function getBaseWeeeTaxAppliedAmount();

    /**
     * Gets the base WEEE tax applied row amount for a credit memo item.
     *
     * @return float Base WEEE tax applied row amount.
     */
    public function getBaseWeeeTaxAppliedRowAmnt();

    /**
     * Gets the base WEEE tax disposition for a credit memo item.
     *
     * @return float Base WEEE tax disposition.
     */
    public function getBaseWeeeTaxDisposition();

    /**
     * Gets the base WEEE tax row disposition for a credit memo item.
     *
     * @return float Base WEEE tax row disposition.
     */
    public function getBaseWeeeTaxRowDisposition();

    /**
     * Gets the description for a credit memo item.
     *
     * @return string Description.
     */
    public function getDescription();

    /**
     * Gets the discount amount for a credit memo item.
     *
     * @return float Discount amount.
     */
    public function getDiscountAmount();

    /**
     * Gets the ID for a credit memo item.
     *
     * @return int Credit memo item ID.
     */
    public function getEntityId();

    /**
     * Gets the hidden tax amount for a credit memo item.
     *
     * @return float Hidden tax amount.
     */
    public function getHiddenTaxAmount();

    /**
     * Gets the name for a credit memo item.
     *
     * @return string Name.
     */
    public function getName();

    /**
     * Gets the order item ID for a credit memo item.
     *
     * @return int Order item ID.
     */
    public function getOrderItemId();

    /**
     * Gets the parent ID for a credit memo item.
     *
     * @return int Parent ID.
     */
    public function getParentId();

    /**
     * Gets the price for a credit memo item.
     *
     * @return float Price.
     */
    public function getPrice();

    /**
     * Gets the price including tax for a credit memo item.
     *
     * @return float Price including tax.
     */
    public function getPriceInclTax();

    /**
     * Gets the product ID for a credit memo item.
     *
     * @return int Product ID.
     */
    public function getProductId();

    /**
     * Gets the quantity for a credit memo item.
     *
     * @return float Quantity.
     */
    public function getQty();

    /**
     * Gets the row total for a credit memo item.
     *
     * @return float Row total.
     */
    public function getRowTotal();

    /**
     * Gets the row total including tax for a credit memo item.
     *
     * @return float Row total including tax.
     */
    public function getRowTotalInclTax();

    /**
     * Gets the SKU for a credit memo item.
     *
     * @return string SKU.
     */
    public function getSku();

    /**
     * Gets the tax amount for a credit memo item.
     *
     * @return float Tax amount.
     */
    public function getTaxAmount();

    /**
     * Gets the WEEE tax applied for a credit memo item.
     *
     * @return string WEEE tax applied.
     */
    public function getWeeeTaxApplied();

    /**
     * Gets the WEEE tax applied amount for a credit memo item.
     *
     * @return float WEEE tax applied amount.
     */
    public function getWeeeTaxAppliedAmount();

    /**
     * Gets the WEEE tax applied row amount for a credit memo item.
     *
     * @return float WEEE tax applied row amount.
     */
    public function getWeeeTaxAppliedRowAmount();

    /**
     * Gets the WEEE tax disposition for a credit memo item.
     *
     * @return float WEEE tax disposition.
     */
    public function getWeeeTaxDisposition();

    /**
     * Gets the WEEE tax row disposition for a credit memo item.
     *
     * @return float WEEE tax row disposition.
     */
    public function getWeeeTaxRowDisposition();
}
