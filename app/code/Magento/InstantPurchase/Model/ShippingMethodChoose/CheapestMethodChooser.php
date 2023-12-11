<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\InstantPurchase\Model\ShippingMethodChoose;

use Magento\Customer\Model\Address;
use Magento\Quote\Api\Data\ShippingMethodInterfaceFactory;

/**
 * Creates special shipping method to choose cheapest shipping method after quote creation.
 */
class CheapestMethodChooser implements ShippingMethodChooserInterface
{
    /**
     * @var ShippingMethodInterfaceFactory
     */
    private $shippingMethodFactory;

    /**
     * @var CarrierFinder
     */
    private $carrierFinder;

    /**
     * CheapestMethodChooser constructor.
     * @param ShippingMethodInterfaceFactory $shippingMethodFactory
     * @param CarrierFinder $carrierFinder
     */
    public function __construct(
        ShippingMethodInterfaceFactory $shippingMethodFactory,
        CarrierFinder $carrierFinder
    ) {
        $this->shippingMethodFactory = $shippingMethodFactory;
        $this->carrierFinder = $carrierFinder;
    }

    /**
     * @inheritdoc
     */
    public function choose(Address $address)
    {
        $shippingMethod = $this->shippingMethodFactory->create()
            ->setCarrierCode(DeferredShippingMethodChooserInterface::CARRIER)
            ->setMethodCode(CheapestMethodDeferredChooser::METHOD_CODE)
            ->setMethodTitle(__('Cheapest price'))
            ->setAvailable($this->areShippingMethodsAvailable($address));
        return $shippingMethod;
    }

    /**
     * Checks if any shipping method available.
     *
     * @param Address $address
     * @return bool
     */
    private function areShippingMethodsAvailable(Address $address): bool
    {
        $carriersForAddress = $this->carrierFinder->getCarriersForCustomerAddress($address);
        return !empty($carriersForAddress);
    }
}
