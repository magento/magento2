<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Model\Order\Shipment;

use Magento\Sales\Api\Data\ShipmentTrackCreationInterface;

/**
 * Class TrackCreation
 */
class TrackCreation implements ShipmentTrackCreationInterface
{
    /**
     * @var float
     */
    private $weight;

    /**
     * @var float
     */
    private $qty;

    /**
     * @var int
     */
    private $orderId;

    /**
     * @var string
     */
    private $trackNumber;

    /**
     * @var string
     */
    private $description;

    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $carrierCode;

    /**
     * @var \Magento\Sales\Api\Data\ShipmentTrackCreationExtensionInterface
     */
    private $extensionAttributes;

    /**
     * {@inheritdoc}
     */
    public function getWeight()
    {
        return $this->weight;
    }

    /**
     * {@inheritdoc}
     */
    public function setWeight($weight)
    {
        $this->weight = $weight;
    }

    /**
     * {@inheritdoc}
     */
    public function getQty()
    {
        return $this->qty;
    }

    /**
     * {@inheritdoc}
     */
    public function setQty($qty)
    {
        $this->qty = $qty;
    }

    /**
     * {@inheritdoc}
     */
    public function getOrderId()
    {
        return $this->orderId;
    }

    /**
     * {@inheritdoc}
     */
    public function setOrderId($orderId)
    {
        $this->orderId = $orderId;
    }

    /**
     * {@inheritdoc}
     */
    public function getTrackNumber()
    {
        return $this->trackNumber;
    }

    /**
     * {@inheritdoc}
     */
    public function setTrackNumber($trackNumber)
    {
        $this->trackNumber = $trackNumber;
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * {@inheritdoc}
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * {@inheritdoc}
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * {@inheritdoc}
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * {@inheritdoc}
     */
    public function getCarrierCode()
    {
        return $this->carrierCode;
    }

    /**
     * {@inheritdoc}
     */
    public function setCarrierCode($carrierCode)
    {
        $this->carrierCode = $carrierCode;
    }

    /**
     * {@inheritdoc}
     *
     * @return \Magento\Sales\Api\Data\ShipmentTrackCreationExtensionInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->extensionAttributes;
    }

    /**
     * {@inheritdoc}
     *
     * @param \Magento\Sales\Api\Data\ShipmentTrackCreationExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(\Magento\Sales\Api\Data\ShipmentTrackCreationExtensionInterface $extensionAttributes)
    {
        $this->extensionAttributes = $extensionAttributes;
        return $this;
    }
}
