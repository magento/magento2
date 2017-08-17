<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
