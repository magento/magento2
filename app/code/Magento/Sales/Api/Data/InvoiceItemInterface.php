<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Api\Data;

/**
 * Invoice item interface.
 *
 * An invoice is a record of the receipt of payment for an order. An invoice item is a purchased item in an invoice.
 */
interface InvoiceItemInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case.
     */
    /*
     * Entity ID.
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
     * Discount amount.
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

    /**
     * Gets the additional data for the invoice item.
     *
     * @return string Additional data.
     */
    public function getAdditionalData();

    /**
     * Gets the base cost for the invoice item.
     *
     * @return float Base cost.
     */
    public function getBaseCost();

    /**
     * Gets the base discount amount for the invoice item.
     *
     * @return float Base discount amount.
     */
    public function getBaseDiscountAmount();

    /**
     * Gets the base hidden tax amount for the invoice item.
     *
     * @return float Base hidden tax amount.
     */
    public function getBaseHiddenTaxAmount();

    /**
     * Gets the base price for the invoice item.
     *
     * @return float Base price.
     */
    public function getBasePrice();

    /**
     * Gets the base price including tax for the invoice item.
     *
     * @return float Base price including tax.
     */
    public function getBasePriceInclTax();

    /**
     * Gets the base row total for the invoice item.
     *
     * @return float Base row total.
     */
    public function getBaseRowTotal();

    /**
     * Gets the base row total including tax for the invoice item.
     *
     * @return float Base row total including tax.
     */
    public function getBaseRowTotalInclTax();

    /**
     * Gets the base tax amount for the invoice item.
     *
     * @return float Base tax amount.
     */
    public function getBaseTaxAmount();

    /**
     * Gets the description for the invoice item.
     *
     * @return string Description.
     */
    public function getDescription();

    /**
     * Gets the discount amount for the invoice item.
     *
     * @return float Discount amount.
     */
    public function getDiscountAmount();

    /**
     * Gets the ID for the invoice item.
     *
     * @return int Invoice item ID.
     */
    public function getEntityId();

    /**
     * Gets the hidden tax amount for the invoice item.
     *
     * @return float Hidden tax amount.
     */
    public function getHiddenTaxAmount();

    /**
     * Gets the name for the invoice item.
     *
     * @return string Name.
     */
    public function getName();

    /**
     * Gets the order item ID for the invoice item.
     *
     * @return int Order item ID.
     */
    public function getOrderItemId();

    /**
     * Gets the parent ID for the invoice item.
     *
     * @return int Parent ID.
     */
    public function getParentId();

    /**
     * Gets the price for the invoice item.
     *
     * @return float Price.
     */
    public function getPrice();

    /**
     * Gets the price including tax for the invoice item.
     *
     * @return float Price including tax.
     */
    public function getPriceInclTax();

    /**
     * Gets the product ID for the invoice item.
     *
     * @return int Product ID.
     */
    public function getProductId();

    /**
     * Gets the quantity for the invoice item.
     *
     * @return float Quantity.
     */
    public function getQty();

    /**
     * Gets the row total for the invoice item.
     *
     * @return float Row total.
     */
    public function getRowTotal();

    /**
     * Gets the row total including tax for the invoice item.
     *
     * @return float Row total including tax.
     */
    public function getRowTotalInclTax();

    /**
     * Gets the SKU for the invoice item.
     *
     * @return string SKU.
     */
    public function getSku();

    /**
     * Gets the tax amount for the invoice item.
     *
     * @return float Tax amount.
     */
    public function getTaxAmount();
}
