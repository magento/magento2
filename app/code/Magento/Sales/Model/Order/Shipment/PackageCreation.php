<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order\Shipment;

/**
 * Class PackageCreation
 * @api
 * @since 2.2.0
 */
class PackageCreation implements \Magento\Sales\Api\Data\ShipmentPackageCreationInterface
{
    /**
     * @var \Magento\Sales\Api\Data\ShipmentPackageCreationExtensionInterface
     * @since 2.2.0
     */
    private $extensionAttributes;

    /**
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function getExtensionAttributes()
    {
        return $this->extensionAttributes;
    }

    /**
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function setExtensionAttributes(
        \Magento\Sales\Api\Data\ShipmentPackageCreationExtensionInterface $extensionAttributes
    ) {
        $this->extensionAttributes = $extensionAttributes;
        return $this;
    }
}
