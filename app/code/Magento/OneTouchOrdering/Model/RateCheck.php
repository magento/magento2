<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\OneTouchOrdering\Model;

use Magento\Customer\Model\Address;
use Magento\Quote\Model\Quote\Address\RateCollectorInterfaceFactory;
use Magento\Quote\Model\Quote\Address\RateRequestFactory;
use Magento\Store\Model\StoreManagerInterface;

class RateCheck
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
    private $rateCollector;

    /**
     * RateCheck constructor.
     * @param RateRequestFactory $rateRequestFactory
     * @param RateCollectorInterfaceFactory $rateCollector
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        RateRequestFactory $rateRequestFactory,
        RateCollectorInterfaceFactory $rateCollector,
        StoreManagerInterface $storeManager
    ) {
        $this->rateRequestFactory = $rateRequestFactory;
        $this->storeManager = $storeManager;
        $this->rateCollector = $rateCollector;
    }

    /**
     * @param Address $address
     * @return array
     */
    public function getRatesForCustomerAddress(Address $address)
    {
        /** @var $request \Magento\Quote\Model\Quote\Address\RateRequest */
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

        $result = $this->rateCollector->create()->collectRates($request)->getResult();

        $shippingRates = [];

        if ($result) {
            $shippingRates = $result->getAllRates();
        }

        return $shippingRates;
    }
}
