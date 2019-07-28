<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\InstantPurchase\Model\ShippingMethodChoose;

use Magento\Customer\Model\Address;
use Magento\Framework\DataObject;
use Magento\Shipping\Model\Config as CarriersConfig;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Collect shipping rates for customer address without packaging estimation.
 */
class CarrierFinder
{
    /**
     * @var CarriersConfig
     */
    private $carriersConfig;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * CarrierFinder constructor.
     * @param CarriersConfig $carriersConfig
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        CarriersConfig $carriersConfig,
        StoreManagerInterface $storeManager
    ) {
        $this->carriersConfig = $carriersConfig;
        $this->storeManager = $storeManager;
    }

    /**
     * Finds carriers delivering to customer address
     *
     * @param Address $address
     * @return array
     */
    public function getCarriersForCustomerAddress(Address $address): array
    {
        $request = new DataObject([
            'dest_country_id' => $address->getCountryId()
        ]);

        $carriers = [];
        foreach ($this->carriersConfig->getActiveCarriers($this->storeManager->getStore()->getId()) as $carrier) {
            $checked = $carrier->checkAvailableShipCountries($request);
            if (false !== $checked && null === $checked->getErrorMessage() && !empty($checked->getAllowedMethods())) {
                $carriers[] = $checked;
            }
        }

        return $carriers;
    }
}
