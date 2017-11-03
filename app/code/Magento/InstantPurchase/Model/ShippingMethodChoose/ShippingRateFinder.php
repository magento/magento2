<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\InstantPurchase\Model\ShippingMethodChoose;

use Magento\Customer\Model\Address;
use Magento\Quote\Model\Quote\Address\RateCollectorInterfaceFactory;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Quote\Model\Quote\Address\RateRequestFactory;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Collect shipping rates for customer address without packaging estiamtion.
 */
class ShippingRateFinder
{
    /**
     * @var RateRequestFactory
     */
    private $rateRequestFactory;
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var RateCollectorInterfaceFactory
     */
    private $rateCollectorFactory;

    /**
     * RateCheck constructor.
     * @param RateRequestFactory $rateRequestFactory
     * @param RateCollectorInterfaceFactory $rateCollectorFactory
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        RateRequestFactory $rateRequestFactory,
        RateCollectorInterfaceFactory $rateCollectorFactory,
        StoreManagerInterface $storeManager
    ) {
        $this->rateRequestFactory = $rateRequestFactory;
        $this->storeManager = $storeManager;
        $this->rateCollectorFactory = $rateCollectorFactory;
    }

    /**
     * Finds shipping rates for an address.
     *
     * @param Address $address
     * @return array
     */
    public function getRatesForCustomerAddress(Address $address): array
    {
        /** @var $request RateRequest */
        $request = $this->rateRequestFactory->create();
        $request->setDestCountryId($address->getCountryId());
        $request->setDestRegionId($address->getRegionId());
        $request->setDestRegionCode($address->getRegionCode());
        $request->setDestStreet($address->getStreetFull());
        $request->setDestCity($address->getCity());
        $request->setDestPostcode($address->getPostcode());
        $request->setStoreId($this->storeManager->getStore()->getId());
        $request->setWebsiteId($this->storeManager->getWebsite()->getId());
        $request->setBaseCurrency($this->storeManager->getStore()->getBaseCurrency());
        $request->setPackageCurrency($this->storeManager->getStore()->getCurrentCurrency());
        // Because of wrong compare operator in \Magento\OfflineShipping\Model\Carrier\Tablerate on line: 167
        $request->setPackageQty(-1);

        $result = $this->rateCollectorFactory->create()->collectRates($request)->getResult();

        $shippingRates = [];

        if ($result) {
            $shippingRates = $result->getAllRates();
        }

        return $shippingRates;
    }
}
