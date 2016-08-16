<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Api\Data;

/**
 * Shipment Track Creation interface.
 *
 * @api
 */
interface TrackInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**
     * Sets the weight for the shipment package.
     *
     * @param float $weight
     * @return $this
     */
    public function setWeight($weight);

    /**
     * Gets the weight for the shipment package.
     *
     * @return float Weight.
     */
    public function getWeight();

    /**
     * Sets the quantity for the shipment package.
     *
     * @param float $qty
     * @return $this
     */
    public function setQty($qty);

    /**
     * Gets the quantity for the shipment package.
     *
     * @return float Quantity.
     */
    public function getQty();

    /**
     * Sets the track number for the shipment package.
     *
     * @param string $trackNumber
     * @return $this
     */
    public function setTrackNumber($trackNumber);

    /**
     * Gets the track number for the shipment package.
     *
     * @return string Track number.
     */
    public function getTrackNumber();

    /**
     * Sets the description for the shipment package.
     *
     * @param string $description
     * @return $this
     */
    public function setDescription($description);

    /**
     * Gets the description for the shipment package.
     *
     * @return string Description.
     */
    public function getDescription();

    /**
     * Sets the title for the shipment package.
     *
     * @param string $title
     * @return $this
     */
    public function setTitle($title);

    /**
     * Gets the title for the shipment package.
     *
     * @return string Title.
     */
    public function getTitle();

    /**
     * Sets the carrier code for the shipment package.
     *
     * @param string $code
     * @return $this
     */
    public function setCarrierCode($code);

    /**
     * Gets the carrier code for the shipment package.
     *
     * @return string Carrier code.
     */
    public function getCarrierCode();
}
