<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Sales\Model\Order\Shipment;

/**
 * Class PackageCreation
 * @api
 * @since 100.1.2
 */
class PackageCreation implements \Magento\Sales\Api\Data\ShipmentPackageCreationInterface
{
    /**
     * @var \Magento\Sales\Api\Data\ShipmentPackageCreationExtensionInterface
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
        \Magento\Sales\Api\Data\ShipmentPackageCreationExtensionInterface $extensionAttributes
    ) {
        $this->extensionAttributes = $extensionAttributes;
        return $this;
    }
}
