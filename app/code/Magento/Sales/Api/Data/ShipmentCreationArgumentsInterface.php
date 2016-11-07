<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Api\Data;

/**
 * Interface for creation arguments for Shipment.
 *
 * @api
 */
interface ShipmentCreationArgumentsInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**
     * Gets existing extension attributes.
     *
     * @return \Magento\Sales\Api\Data\ShipmentCreationArgumentsExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Sets extension attributes.
     *
     * @param \Magento\Sales\Api\Data\ShipmentCreationArgumentsExtensionInterface $extensionAttributes
     *
     * @return $this
     */
    public function setExtensionAttributes(
        \Magento\Sales\Api\Data\ShipmentCreationArgumentsExtensionInterface $extensionAttributes
    );
}
