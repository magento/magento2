<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Sales\Api\Data;

/**
 * Interface ShipmentItemInterface
 */
interface ShipmentItemInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const ENTITY_ID = 'entity_id';
    const PARENT_ID = 'parent_id';
    const ROW_TOTAL = 'row_total';
    const PRICE = 'price';
    const WEIGHT = 'weight';
    const QTY = 'qty';
    const PRODUCT_ID = 'product_id';
    const ORDER_ITEM_ID = 'order_item_id';
    const ADDITIONAL_DATA = 'additional_data';
    const DESCRIPTION = 'description';
    const NAME = 'name';
    const SKU = 'sku';

    /**
     * Returns additional_data
     *
     * @return string
     */
    public function getAdditionalData();

    /**
     * Returns description
     *
     * @return string
     */
    public function getDescription();

    /**
     * Returns entity_id
     *
     * @return int
     */
    public function getEntityId();

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
     * Returns sku
     *
     * @return string
     */
    public function getSku();

    /**
     * Returns weight
     *
     * @return float
     */
    public function getWeight();
}
