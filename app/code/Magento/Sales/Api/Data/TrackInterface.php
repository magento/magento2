<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Api\Data;

/**
 * Shipment Track Creation interface.
 *
 * @api
 */
interface TrackInterface
{
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
