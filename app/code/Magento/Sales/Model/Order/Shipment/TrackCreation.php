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
     * @var string
     */
    private $trackNumber;

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

    //@codeCoverageIgnoreStart

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
        return $this;
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
        return $this;
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
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getExtensionAttributes()
    {
        return $this->extensionAttributes;
    }

    /**
     * {@inheritdoc}
     */
    public function setExtensionAttributes(
        \Magento\Sales\Api\Data\ShipmentTrackCreationExtensionInterface $extensionAttributes
    ) {
        $this->extensionAttributes = $extensionAttributes;
        return $this;
    }

    //@codeCoverageIgnoreEnd
}
