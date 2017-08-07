<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Interface ShippingAssignmentInterface
 * @api
 * @since 2.0.3
 */
interface ShippingAssignmentInterface extends ExtensibleDataInterface
{
    /**#@+
     * Shipping assignment object data keys
     */
    const KEY_SHIPPING = 'shipping';

    const KEY_ITEMS = 'items';

    const KEY_STOCK_ID = 'stock_id';
    /**#@-*/

    /**
     * Gets shipping object
     *
     * @return \Magento\Sales\Api\Data\ShippingInterface
     * @since 2.0.3
     */
    public function getShipping();

    /**
     * Gets order items of shipping assignment
     *
     * @return \Magento\Sales\Api\Data\OrderItemInterface[]
     * @since 2.0.3
     */
    public function getItems();

    /**
     * Gets stock id
     *
     * @return int|null
     * @since 2.0.3
     */
    public function getStockId();

    /**
     * Sets shipping
     *
     * @param \Magento\Sales\Api\Data\ShippingInterface $shipping
     * @return $this
     * @since 2.0.3
     */
    public function setShipping(\Magento\Sales\Api\Data\ShippingInterface $shipping);

    /**
     * Sets order items to shipping assignment
     *
     * @param \Magento\Sales\Api\Data\OrderItemInterface[] $items
     * @return $this
     * @since 2.0.3
     */
    public function setItems(array $items);

    /**
     * Sets stock id
     *
     * @param int|null $stockId
     * @return $this
     * @since 2.0.3
     */
    public function setStockId($stockId = null);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Sales\Api\Data\ShippingAssignmentExtensionInterface|null
     * @since 2.0.3
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Sales\Api\Data\ShippingAssignmentExtensionInterface $extensionAttributes
     * @return $this
     * @since 2.0.3
     */
    public function setExtensionAttributes(
        \Magento\Sales\Api\Data\ShippingAssignmentExtensionInterface $extensionAttributes
    );
}
