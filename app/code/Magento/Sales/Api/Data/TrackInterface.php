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
 * @since 2.2.0
 */
interface TrackInterface
{
    /**
     * Sets the track number for the shipment package.
     *
     * @param string $trackNumber
     * @return $this
     * @since 2.2.0
     */
    public function setTrackNumber($trackNumber);

    /**
     * Gets the track number for the shipment package.
     *
     * @return string Track number.
     * @since 2.2.0
     */
    public function getTrackNumber();

    /**
     * Sets the title for the shipment package.
     *
     * @param string $title
     * @return $this
     * @since 2.2.0
     */
    public function setTitle($title);

    /**
     * Gets the title for the shipment package.
     *
     * @return string Title.
     * @since 2.2.0
     */
    public function getTitle();

    /**
     * Sets the carrier code for the shipment package.
     *
     * @param string $code
     * @return $this
     * @since 2.2.0
     */
    public function setCarrierCode($code);

    /**
     * Gets the carrier code for the shipment package.
     *
     * @return string Carrier code.
     * @since 2.2.0
     */
    public function getCarrierCode();
}
