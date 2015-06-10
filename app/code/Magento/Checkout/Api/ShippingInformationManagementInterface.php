<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Api;

interface ShippingInformationManagementInterface
{
    /**
     * @param \Magento\Checkout\Api\Data\ShippingInformationInterface $addressInformation
     * @return mixed
     */
    public function saveAddressInformation(
        \Magento\Checkout\Api\Data\ShippingInformationInterface $addressInformation
    );
}
