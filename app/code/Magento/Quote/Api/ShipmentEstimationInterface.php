<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Api;

use Magento\Quote\Api\Data\AddressInterface;

/**
 * Interface ShipmentManagementInterface
 * @api
 * @since 100.0.7
 */
interface ShipmentEstimationInterface
{
    /**
     * Estimate shipping by address and return list of available shipping methods
     *
     * @param mixed $cartId
     * @param AddressInterface $address
     * @return \Magento\Quote\Api\Data\ShippingMethodInterface[] An array of shipping methods
     * @throws \Magento\Framework\Exception\InputException The specified input is not valid.
     * @since 100.0.7
     */
    public function estimateByExtendedAddress($cartId, AddressInterface $address);
}
