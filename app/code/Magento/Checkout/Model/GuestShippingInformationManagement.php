<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Model;

class GuestShippingInformationManagement implements \Magento\Checkout\Api\GuestShippingInformationManagementInterface
{
    /**
     * @var \Magento\Quote\Api\GuestShippingAddressManagementInterface
     */
    protected $shippingAddressManagement;

    /**
     * @var \Magento\Quote\Api\GuestShippingMethodManagementInterface
     */
    protected $shippingMethodManagement;

    /**
     * @param \Magento\Quote\Api\GuestShippingAddressManagementInterface $shippingAddressManagement
     * @param \Magento\Quote\Api\GuestShippingMethodManagementInterface $shippingMethodManagement
     */
    public function __construct(
        \Magento\Quote\Api\GuestShippingAddressManagementInterface $shippingAddressManagement,
        \Magento\Quote\Api\GuestShippingMethodManagementInterface $shippingMethodManagement
    ) {
        $this->shippingAddressManagement = $shippingAddressManagement;
        $this->shippingMethodManagement = $shippingMethodManagement;
    }

    /**
     * {@inheritDoc}
     */
    public function saveAddressInformation(
        $cartId,
        \Magento\Checkout\Api\Data\ShippingInformationInterface $addressInformation
    ) {
        $this->shippingAddressManagement->assign($cartId, $addressInformation->getShippingAddress());
        $this->shippingMethodManagement->set(
            $cartId,
            $addressInformation->getShippingCarrierCode(),
            $addressInformation->getShippingMethodCode()
        );

        //TODO: implement logic for return available payment methods
    }
}
