<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\InstantPurchase\Model\ShippingMethodChoose;

use Magento\Customer\Model\Address;
use Magento\Quote\Api\Data\ShippingMethodInterface;
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
     * @var ShippingRateFinder
     */
    private $shippingRateFinder;

    /**
     * CheapestMethodChooser constructor.
     * @param ShippingMethodInterfaceFactory $shippingMethodFactory
     * @param ShippingRateFinder $shippingRateFinder
     */
    public function __construct(
        ShippingMethodInterfaceFactory $shippingMethodFactory,
        ShippingRateFinder $shippingRateFinder
    ) {
        $this->shippingMethodFactory = $shippingMethodFactory;
        $this->shippingRateFinder = $shippingRateFinder;
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
        $shippingRatesForAddress = $this->shippingRateFinder->getRatesForCustomerAddress($address);
        return !empty($shippingRatesForAddress);
    }
}
