<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Shipping\Model\Carrier;

interface CarrierInterface
{
    /**
     * Check if carrier has shipping tracking option available
     *
     * @return boolean
     * @api
     */
    public function isTrackingAvailable();

    /**
     * Get allowed shipping methods
     *
     * @return array
     * @api
     */
    public function getAllowedMethods();
}
