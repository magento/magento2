<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Sales\Api\Data;

/**
 * Interface CreditmemoItemInterface
 */
interface CreditmemoItemInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const ENTITY_ID = 'entity_id';
    const PARENT_ID = 'parent_id';
    const BASE_PRICE = 'base_price';
    const TAX_AMOUNT = 'tax_amount';
    const BASE_ROW_TOTAL = 'base_row_total';
    const DISCOUNT_AMOUNT = 'discount_amount';
    const ROW_TOTAL = 'row_total';
    const BASE_DISCOUNT_AMOUNT = 'base_discount_amount';
    const PRICE_INCL_TAX = 'price_incl_tax';
    const BASE_TAX_AMOUNT = 'base_tax_amount';
    const BASE_PRICE_INCL_TAX = 'base_price_incl_tax';
    const QTY = 'qty';
    const BASE_COST = 'base_cost';
    const PRICE = 'price';
    const BASE_ROW_TOTAL_INCL_TAX = 'base_row_total_incl_tax';
    const ROW_TOTAL_INCL_TAX = 'row_total_incl_tax';
    const PRODUCT_ID = 'product_id';
    const ORDER_ITEM_ID = 'order_item_id';
    const ADDITIONAL_DATA = 'additional_data';
    const DESCRIPTION = 'description';
    const SKU = 'sku';
    const NAME = 'name';
    const HIDDEN_TAX_AMOUNT = 'hidden_tax_amount';
    const BASE_HIDDEN_TAX_AMOUNT = 'base_hidden_tax_amount';
    const WEEE_TAX_DISPOSITION = 'weee_tax_disposition';
    const WEEE_TAX_ROW_DISPOSITION = 'weee_tax_row_disposition';
    const BASE_WEEE_TAX_DISPOSITION = 'base_weee_tax_disposition';
    const BASE_WEEE_TAX_ROW_DISPOSITION = 'base_weee_tax_row_disposition';
    const WEEE_TAX_APPLIED = 'weee_tax_applied';
    const BASE_WEEE_TAX_APPLIED_AMOUNT = 'base_weee_tax_applied_amount';
    const BASE_WEEE_TAX_APPLIED_ROW_AMNT = 'base_weee_tax_applied_row_amnt';
    const WEEE_TAX_APPLIED_AMOUNT = 'weee_tax_applied_amount';
    const WEEE_TAX_APPLIED_ROW_AMOUNT = 'weee_tax_applied_row_amount';

    /**
     * Returns additional_data
     *
     * @return string
     */
    public function getAdditionalData();

    /**
     * Returns base_cost
     *
     * @return float
     */
    public function getBaseCost();

    /**
     * Returns base_discount_amount
     *
     * @return float
     */
    public function getBaseDiscountAmount();

    /**
     * Returns base_hidden_tax_amount
     *
     * @return float
     */
    public function getBaseHiddenTaxAmount();

    /**
     * Returns base_price
     *
     * @return float
     */
    public function getBasePrice();

    /**
     * Returns base_price_incl_tax
     *
     * @return float
     */
    public function getBasePriceInclTax();

    /**
     * Returns base_row_total
     *
     * @return float
     */
    public function getBaseRowTotal();

    /**
     * Returns base_row_total_incl_tax
     *
     * @return float
     */
    public function getBaseRowTotalInclTax();

    /**
     * Returns base_tax_amount
     *
     * @return float
     */
    public function getBaseTaxAmount();

    /**
     * Returns base_weee_tax_applied_amount
     *
     * @return float
     */
    public function getBaseWeeeTaxAppliedAmount();

    /**
     * Returns base_weee_tax_applied_row_amnt
     *
     * @return float
     */
    public function getBaseWeeeTaxAppliedRowAmnt();

    /**
     * Returns base_weee_tax_disposition
     *
     * @return float
     */
    public function getBaseWeeeTaxDisposition();

    /**
     * Returns base_weee_tax_row_disposition
     *
     * @return float
     */
    public function getBaseWeeeTaxRowDisposition();

    /**
     * Returns description
     *
     * @return string
     */
    public function getDescription();

    /**
     * Returns discount_amount
     *
     * @return float
     */
    public function getDiscountAmount();

    /**
     * Returns entity_id
     *
     * @return int
     */
    public function getEntityId();

    /**
     * Returns hidden_tax_amount
     *
     * @return float
     */
    public function getHiddenTaxAmount();

    /**
     * Returns name
     *
     * @return string
     */
    public function getName();

    /**
     * Returns order_item_id
     *
     * @return int
     */
    public function getOrderItemId();

    /**
     * Returns parent_id
     *
     * @return int
     */
    public function getParentId();

    /**
     * Returns price
     *
     * @return float
     */
    public function getPrice();

    /**
     * Returns price_incl_tax
     *
     * @return float
     */
    public function getPriceInclTax();

    /**
     * Returns product_id
     *
     * @return int
     */
    public function getProductId();

    /**
     * Returns qty
     *
     * @return float
     */
    public function getQty();

    /**
     * Returns row_total
     *
     * @return float
     */
    public function getRowTotal();

    /**
     * Returns row_total_incl_tax
     *
     * @return float
     */
    public function getRowTotalInclTax();

    /**
     * Returns sku
     *
     * @return string
     */
    public function getSku();

    /**
     * Returns tax_amount
     *
     * @return float
     */
    public function getTaxAmount();

    /**
     * Returns weee_tax_applied
     *
     * @return string
     */
    public function getWeeeTaxApplied();

    /**
     * Returns weee_tax_applied_amount
     *
     * @return float
     */
    public function getWeeeTaxAppliedAmount();

    /**
     * Returns weee_tax_applied_row_amount
     *
     * @return float
     */
    public function getWeeeTaxAppliedRowAmount();

    /**
     * Returns weee_tax_disposition
     *
     * @return float
     */
    public function getWeeeTaxDisposition();

    /**
     * Returns weee_tax_row_disposition
     *
     * @return float
     */
    public function getWeeeTaxRowDisposition();
}
