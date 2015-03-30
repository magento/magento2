<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Model;

class AddressDetailsManagement implements \Magento\Quote\Api\AddressDetailsManagementInterface
{
    /**
     * @var \Magento\Quote\Api\BillingAddressManagementInterface
     */
    protected $billingAddressManagement;

    /**
     * @var \Magento\Quote\Api\ShippingAddressManagementInterface
     */
    protected $shippingAddressManagement;

    /**
     * @var \Magento\Quote\Api\PaymentMethodManagementInterface
     */
    protected $paymentMethodManagement;

    /**
     * @var \Magento\Quote\Api\ShippingMethodManagementInterface
     */
    protected $shippingMethodManagement;

    /**
     * @var AddressDetailsFactory
     */
    protected $addressDetailsFactory;

    /**
     * @param \Magento\Quote\Api\BillingAddressManagementInterface $billingAddressManagement
     * @param \Magento\Quote\Api\ShippingAddressManagementInterface $shippingAddressManagement
     * @param \Magento\Quote\Api\PaymentMethodManagementInterface $paymentMethodManagement
     * @param \Magento\Quote\Api\ShippingMethodManagementInterface $shippingMethodManagement
     * @param AddressDetailsFactory $addressDetailsFactory
     */
    public function __construct(
        \Magento\Quote\Api\BillingAddressManagementInterface $billingAddressManagement,
        \Magento\Quote\Api\ShippingAddressManagementInterface $shippingAddressManagement,
        \Magento\Quote\Api\PaymentMethodManagementInterface $paymentMethodManagement,
        \Magento\Quote\Api\ShippingMethodManagementInterface $shippingMethodManagement,
        \Magento\Quote\Model\AddressDetailsFactory $addressDetailsFactory
    ) {
        $this->billingAddressManagement = $billingAddressManagement;
        $this->shippingAddressManagement = $shippingAddressManagement;
        $this->paymentMethodManagement = $paymentMethodManagement;
        $this->shippingMethodManagement = $shippingMethodManagement;
        $this->addressDetailsFactory = $addressDetailsFactory;
    }

    /**
     * @{inheritdoc}
     */
    public function saveAddresses(
        $cartId,
        \Magento\Quote\Api\Data\AddressInterface $billingAddress,
        \Magento\Quote\Api\Data\AddressInterface $shippingAddress
    ) {
        $this->billingAddressManagement->assign($cartId, $billingAddress);
        $this->shippingAddressManagement->assign($cartId, $shippingAddress);

        $addressDetails = $this->addressDetailsFactory->create();
        $addressDetails->setShippingMethods($this->shippingMethodManagement->getList($cartId));
        $addressDetails->setPaymentMethods($this->paymentMethodManagement->getList($cartId));
        return $addressDetails;
    }
}
