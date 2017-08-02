<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order\Shipment;

use Magento\Sales\Api\Data\ShipmentItemCreationInterface;

/**
 * Class ItemCreation
 * @since 2.2.0
 */
class ItemCreation implements ShipmentItemCreationInterface
{
    /**
     * @var int
     * @since 2.2.0
     */
    private $orderItemId;

    /**
     * @var float
     * @since 2.2.0
     */
    private $qty;

    /**
     * @var \Magento\Sales\Api\Data\ShipmentItemCreationExtensionInterface
     * @since 2.2.0
     */
    private $extensionAttributes;

    //@codeCoverageIgnoreStart

    /**
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function getOrderItemId()
    {
        return $this->orderItemId;
    }

    /**
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function setOrderItemId($orderItemId)
    {
        $this->orderItemId = $orderItemId;
    }

    /**
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function getQty()
    {
        return $this->qty;
    }

    /**
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function setQty($qty)
    {
        $this->qty = $qty;
    }

    /**
     * {@inheritdoc}
     *
     * @return \Magento\Sales\Api\Data\ShipmentItemCreationExtensionInterface|null
     * @since 2.2.0
     */
    public function getExtensionAttributes()
    {
        return $this->extensionAttributes;
    }

    /**
     * {@inheritdoc}
     *
     * @param \Magento\Sales\Api\Data\ShipmentItemCreationExtensionInterface $extensionAttributes
     * @return $this
     * @since 2.2.0
     */
    public function setExtensionAttributes(
        \Magento\Sales\Api\Data\ShipmentItemCreationExtensionInterface $extensionAttributes
    ) {
        $this->extensionAttributes = $extensionAttributes;
        return $this;
    }

    //@codeCoverageIgnoreEnd
}
