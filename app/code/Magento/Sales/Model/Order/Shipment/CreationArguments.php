<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Sales\Model\Order\Shipment;

/**
 * Creation arguments for Shipment.
 *
 * @api
 * @since 100.1.2
 */
class CreationArguments implements \Magento\Sales\Api\Data\ShipmentCreationArgumentsInterface
{
    /**
     * @var \Magento\Sales\Api\Data\ShipmentCreationArgumentsExtensionInterface
     */
    private $extensionAttributes;

    /**
     * {@inheritdoc}
     * @since 100.1.2
     */
    public function getExtensionAttributes()
    {
        return $this->extensionAttributes;
    }

    /**
     * {@inheritdoc}
     * @since 100.1.2
     */
    public function setExtensionAttributes(
        \Magento\Sales\Api\Data\ShipmentCreationArgumentsExtensionInterface $extensionAttributes
    ) {
        $this->extensionAttributes = $extensionAttributes;
        return $this;
    }
}
