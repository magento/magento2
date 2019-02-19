<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Api\Data;

/**
 * Input argument for shipment item creation
 *
 * Interface ShipmentItemCreationInterface
 *
 * @api
 * @since 100.1.2
 */
interface ShipmentItemCreationInterface extends
    LineItemInterface,
    \Magento\Framework\Api\ExtensibleDataInterface
{
    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Sales\Api\Data\ShipmentItemCreationExtensionInterface|null
     * @since 100.1.2
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Sales\Api\Data\ShipmentItemCreationExtensionInterface $extensionAttributes
     * @return $this
     * @since 100.1.2
     */
    public function setExtensionAttributes(
        \Magento\Sales\Api\Data\ShipmentItemCreationExtensionInterface $extensionAttributes
    );
}
