<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order\Shipment;

/**
 * Class Package
 * @api
 * @since 2.1.2
 */
class Package implements \Magento\Sales\Api\Data\ShipmentPackageInterface
{
    /**
     * @var \Magento\Sales\Api\Data\ShipmentPackageExtensionInterface
     * @since 2.1.2
     */
    private $extensionAttributes;

    /**
     * {@inheritdoc}
     * @since 2.1.2
     */
    public function getExtensionAttributes()
    {
        return $this->extensionAttributes;
    }

    /**
     * {@inheritdoc}
     * @since 2.1.2
     */
    public function setExtensionAttributes(
        \Magento\Sales\Api\Data\ShipmentPackageExtensionInterface $extensionAttributes
    ) {
        $this->extensionAttributes = $extensionAttributes;
        return $this;
    }
}
