<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Api\Data;

/**
 * Shipment Track URL Creation interface.
 *
 * @api
 */
interface TrackUrlInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case.
     */
    /*
     * Track URL.
     */
    const TRACK_URL = 'track_url';
    /**
     * Sets the track URL for the shipment package.
     *
     * @param string $trackUrl
     * @return $this
     */
    public function setTrackUrl($trackUrl);

    /**
     * Gets the track URL for the shipment package.
     *
     * @return string Track URL.
     */
    public function getTrackUrl();
}
