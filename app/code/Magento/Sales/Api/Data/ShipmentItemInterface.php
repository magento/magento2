<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Api\Data;

/**
 * Shipment item interface.
 *
 * A shipment is a delivery package that contains products. A shipment document accompanies the shipment. This
 * document lists the products and their quantities in the delivery package. A product is an item in a shipment.
 */
interface ShipmentItemInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case
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
     * Row total.
     */
    const ROW_TOTAL = 'row_total';
    /*
     * Price.
     */
    const PRICE = 'price';
    /*
     * Weight.
     */
    const WEIGHT = 'weight';
    /*
     * Quantity.
     */
    const QTY = 'qty';
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
     * Name.
     */
    const NAME = 'name';
    /*
     * SKU.
     */
    const SKU = 'sku';

    /**
     * Gets the additional data for the shipment item.
     *
     * @return string Additional data.
     */
    public function getAdditionalData();

    /**
     * Gets the description for the shipment item.
     *
     * @return string Description.
     */
    public function getDescription();

    /**
     * Gets the ID for the shipment item.
     *
     * @return int Shipment item ID.
     */
    public function getEntityId();

    /**
     * Gets the name for the shipment item.
     *
     * @return string Name.
     */
    public function getName();

    /**
     * Gets the order item ID for the shipment item.
     *
     * @return int Order item ID.
     */
    public function getOrderItemId();

    /**
     * Gets the parent ID for the shipment item.
     *
     * @return int Parent ID.
     */
    public function getParentId();

    /**
     * Gets the price for the shipment item.
     *
     * @return float Price.
     */
    public function getPrice();

    /**
     * Gets the product ID for the shipment item.
     *
     * @return int Product ID.
     */
    public function getProductId();

    /**
     * Gets the quantity for the shipment item.
     *
     * @return float Quantity.
     */
    public function getQty();

    /**
     * Gets the row total for the shipment item.
     *
     * @return float Row total.
     */
    public function getRowTotal();

    /**
     * Gets the SKU for the shipment item.
     *
     * @return string SKU.
     */
    public function getSku();

    /**
     * Gets the weight for the shipment item.
     *
     * @return float Weight.
     */
    public function getWeight();
}
