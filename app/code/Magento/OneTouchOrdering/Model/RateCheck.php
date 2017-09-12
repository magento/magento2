<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\OneTouchOrdering\Model;

class RateCheck
{
    /**
     * @var \Magento\Quote\Model\Quote\Address\RateRequestFactory
     */
    private $rateRequestFactory;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var \Magento\Quote\Model\Quote\Address\RateCollectorInterfaceFactory
     */
    private $rateCollector;

    /**
     * RateCheck constructor.
     * @param \Magento\Quote\Model\Quote\Address\RateRequestFactory $rateRequestFactory
     * @param \Magento\Quote\Model\Quote\Address\RateCollectorInterfaceFactory $rateCollector
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Quote\Model\Quote\Address\RateRequestFactory $rateRequestFactory,
        \Magento\Quote\Model\Quote\Address\RateCollectorInterfaceFactory $rateCollector,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->rateRequestFactory = $rateRequestFactory;
        $this->storeManager = $storeManager;
        $this->rateCollector = $rateCollector;
    }

    /**
     * @param \Magento\Customer\Model\Address $address
     * @return array
     */
    public function getRatesForCustomerAddress(\Magento\Customer\Model\Address $address)
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
