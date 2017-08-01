<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order\Shipment;

use Magento\Sales\Api\Data\ShipmentTrackCreationInterface;

/**
 * Class TrackCreation
 * @since 2.2.0
 */
class TrackCreation implements ShipmentTrackCreationInterface
{
    /**
     * @var string
     * @since 2.2.0
     */
    private $trackNumber;

    /**
     * @var string
     * @since 2.2.0
     */
    private $title;

    /**
     * @var string
     * @since 2.2.0
     */
    private $carrierCode;

    /**
     * @var \Magento\Sales\Api\Data\ShipmentTrackCreationExtensionInterface
     * @since 2.2.0
     */
    private $extensionAttributes;

    //@codeCoverageIgnoreStart

    /**
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function getTrackNumber()
    {
        return $this->trackNumber;
    }

    /**
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function setTrackNumber($trackNumber)
    {
        $this->trackNumber = $trackNumber;
        return $this;
    }

    /**
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function getCarrierCode()
    {
        return $this->carrierCode;
    }

    /**
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function setCarrierCode($carrierCode)
    {
        $this->carrierCode = $carrierCode;
        return $this;
    }

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
        \Magento\Sales\Api\Data\ShipmentTrackCreationExtensionInterface $extensionAttributes
    ) {
        $this->extensionAttributes = $extensionAttributes;
        return $this;
    }

    //@codeCoverageIgnoreEnd
}
