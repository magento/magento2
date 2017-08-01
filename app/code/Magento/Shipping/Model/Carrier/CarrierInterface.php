<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Shipping\Model\Carrier;

/**
 * Interface \Magento\Shipping\Model\Carrier\CarrierInterface
 *
 * @since 2.0.0
 */
interface CarrierInterface
{
    /**
     * Check if carrier has shipping tracking option available
     *
     * @return boolean
     * @api
     * @since 2.0.0
     */
    public function isTrackingAvailable();

    /**
     * Get allowed shipping methods
     *
     * @return array
     * @api
     * @since 2.0.0
     */
    public function getAllowedMethods();
}
