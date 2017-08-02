<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order\Shipment;

use Magento\Sales\Api\Data\ShipmentItemCreationInterface;

/**
 * Class ItemCreation
 * @since 2.1.2
 */
class ItemCreation implements ShipmentItemCreationInterface
{
    /**
     * @var int
     * @since 2.1.2
     */
    private $orderItemId;

    /**
     * @var float
     * @since 2.1.2
     */
    private $qty;

    /**
     * @var \Magento\Sales\Api\Data\ShipmentItemCreationExtensionInterface
     * @since 2.1.2
     */
    private $extensionAttributes;

    //@codeCoverageIgnoreStart

    /**
     * {@inheritdoc}
     * @since 2.1.2
     */
    public function getOrderItemId()
    {
        return $this->orderItemId;
    }

    /**
     * {@inheritdoc}
     * @since 2.1.2
     */
    public function setOrderItemId($orderItemId)
    {
        $this->orderItemId = $orderItemId;
    }

    /**
     * {@inheritdoc}
     * @since 2.1.2
     */
    public function getQty()
    {
        return $this->qty;
    }

    /**
     * {@inheritdoc}
     * @since 2.1.2
     */
    public function setQty($qty)
    {
        $this->qty = $qty;
    }

    /**
     * {@inheritdoc}
     *
     * @return \Magento\Sales\Api\Data\ShipmentItemCreationExtensionInterface|null
     * @since 2.1.2
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
     * @since 2.1.2
     */
    public function setExtensionAttributes(
        \Magento\Sales\Api\Data\ShipmentItemCreationExtensionInterface $extensionAttributes
    ) {
        $this->extensionAttributes = $extensionAttributes;
        return $this;
    }

    //@codeCoverageIgnoreEnd
}
