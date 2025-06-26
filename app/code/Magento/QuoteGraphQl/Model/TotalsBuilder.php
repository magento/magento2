<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model;

use Magento\Checkout\Api\Data\TotalsInformationInterface;
use Magento\Checkout\Api\Data\TotalsInformationInterfaceFactory;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Model\Quote\AddressFactory;

class TotalsBuilder
{
    /**
     * TotalsBuilder Constructor
     *
     * @param AddressFactory $addressFactory
     * @param TotalsInformationInterfaceFactory $totalsInformationFactory
     */
    public function __construct(
        private readonly AddressFactory $addressFactory,
        private readonly TotalsInformationInterfaceFactory $totalsInformationFactory
    ) {
    }

    /**
     * Build totals information with address data
     *
     * @param array $addressData
     * @param array $shippingMethod
     * @return TotalsInformationInterface
     */
    public function execute(array $addressData, array $shippingMethod): TotalsInformationInterface
    {
        $address = $this->addressFactory->create();
        $region = $addressData['region'] ?? [];

        $address->setCountryId($addressData['country_code']);
        $address->setRegionId($region[AddressInterface::KEY_REGION_ID] ?? null);
        $address->setRegion($region[AddressInterface::KEY_REGION] ?? null);
        $address->setPostcode($addressData[AddressInterface::KEY_POSTCODE] ?? null);
        $address->setRegionCode($region[AddressInterface::KEY_REGION_CODE] ?? null);

        $data = [TotalsInformationInterface::ADDRESS => $address];

        if (isset($shippingMethod['carrier_code'], $shippingMethod['method_code'])) {
            $data[TotalsInformationInterface::SHIPPING_METHOD_CODE] = $shippingMethod['method_code'];
            $data[TotalsInformationInterface::SHIPPING_CARRIER_CODE] = $shippingMethod['carrier_code'];
        }

        return $this->totalsInformationFactory->create(['data' => $data]);
    }
}
